import numpy as np
import plotly.express as px
import plotly.graph_objects as go
import streamlit as st

from utils import (CLIMATE, FEATURES, LABELS, TARGET, ablation, banner, get_master, rolling_cv,
                   train_models)

st.title("Model dan Prediksi (AI)")
d, dummy = get_master()
banner(dummy)
tr = train_models(d)
models, met, preds, train, test = tr["models"], tr["metrics"], tr["preds"], tr["train"], tr["test"]

st.markdown(f"Validasi **temporal**: latih sebelum {tr['cutoff']:%b %Y}, uji sesudahnya "
            f"({len(test)} bulan). Semua model dibandingkan dengan baseline musiman. "
            "Tidak ada pengacakan antar-baris.")

t1, t2, t3, t4, t5 = st.tabs(["Performa", "Validasi berlapis", "Interpretasi", "Simulasi", "Unduh"])

with t1:
    st.dataframe(met.round(1), use_container_width=True, hide_index=True)
    names = [c for c in preds.columns if c not in ("year_month",)]
    pick = st.multiselect("Tampilkan", names, default=["aktual", "Random Forest", "Baseline (seasonal naive)"])
    fig = go.Figure()
    for n in pick:
        fig.add_scatter(x=preds.year_month, y=preds[n], name=n,
                        line=dict(dash="dot") if "Baseline" in n else None)
    st.plotly_chart(fig, use_container_width=True)

with t2:
    st.subheader("Rolling-origin (latih meluas, uji per tahun)")
    cv = rolling_cv(d)
    if len(cv):
        st.dataframe(cv.round(1), use_container_width=True, hide_index=True)
        st.plotly_chart(px.bar(cv.melt("Tahun uji", var_name="Model", value_name="MAE"), x="Tahun uji",
                               y="MAE", color="Model", barmode="group"), use_container_width=True)
    else:
        st.info("Data belum cukup untuk rolling-origin.")
    st.subheader("Ablasi fitur (Random Forest)")
    ab = ablation(d)
    st.dataframe(ab.round(1), use_container_width=True, hide_index=True)
    st.plotly_chart(px.bar(ab, x="Skenario", y="MAE uji"), use_container_width=True)
    st.caption("Jika 'Tanpa iklim' sama atau lebih baik, variabel iklim belum menambah daya prediksi. "
               "Itu temuan sah dan harus dilaporkan jujur.")

with t3:
    pi = tr["perm"].sort_values()
    st.plotly_chart(go.Figure(go.Bar(x=pi.values, y=[LABELS.get(i, i) for i in pi.index], orientation="h")
                              ).update_layout(title="Permutation importance (Random Forest, data uji)",
                                              xaxis_title="Kenaikan MAE saat diacak"),
                    use_container_width=True)
    name = st.selectbox("Model untuk residu", [n for n in models], index=1)
    res = preds["aktual"] - preds[name]
    a, b = st.columns(2)
    a.plotly_chart(px.line(x=preds.year_month, y=res, labels={"x": "", "y": "Residu (kg)"},
                           title="Residu"), use_container_width=True)
    b.plotly_chart(px.histogram(res, nbins=15, title="Distribusi residu"), use_container_width=True)
    st.caption("Importance menunjukkan asosiasi dalam model, bukan sebab-akibat.")

with t4:
    st.warning("Simulasi ini hanya menunjukkan respons model terhadap input. Bukan prakiraan resmi. "
               "Model dilatih pada sampel kecil, jadi interpretasikan dengan hati-hati.")
    preset = st.selectbox("Skenario cepat", ["Normal (rata-rata)", "IOD positif kuat (P90 DMI)",
                                             "Perairan hangat dan angin lemah", "Gelombang tinggi"])
    base = {f: float(d[f].mean()) for f in CLIMATE}
    if preset.startswith("IOD"):
        base["dmi"] = float(d.dmi.quantile(.9))
    elif preset.startswith("Perairan"):
        base["sst_mean"], base["wind_speed"] = float(d.sst_mean.quantile(.9)), float(d.wind_speed.quantile(.1))
    elif preset.startswith("Gelombang"):
        base["hs_mean"] = float(d.hs_mean.quantile(.9))
    month = st.selectbox("Bulan", range(1, 13), index=7)
    cols = st.columns(3)
    vals = {}
    for i, f in enumerate(CLIMATE):
        lo, hi = float(d[f].min()), float(d[f].max())
        vals[f] = cols[i % 3].slider(LABELS[f], lo, hi, min(max(base[f], lo), hi), key=f"{preset}_{f}")
    seas = train.groupby(train.year_month.dt.month)[TARGET].mean()
    x = {**vals, "month_sin": np.sin(2 * np.pi * month / 12), "month_cos": np.cos(2 * np.pi * month / 12),
         "lag1_prod": float(seas.get(month - 1 if month > 1 else 12, seas.mean())),
         "lag12_prod": float(seas.get(month, seas.mean()))}
    X = np.array([[x[f] for f in FEATURES]])
    rf = models["Random Forest"]
    pt = np.array([t.predict(X)[0] for t in rf.estimators_])
    p10, p50, p90 = np.percentile(pt, [10, 50, 90])
    a, b, c, e = st.columns(4)
    a.metric("Median RF (kg)", f"{p50:,.0f}")
    b.metric("P10", f"{p10:,.0f}")
    c.metric("P90", f"{p90:,.0f}")
    e.metric("Rata-rata historis bulan itu", f"{seas.get(month, seas.mean()):,.0f}")
    others = {n: float(m.predict(X)[0]) for n, m in models.items()}
    st.plotly_chart(px.bar(x=list(others), y=list(others.values()), labels={"x": "Model", "y": "kg"},
                           title="Perbandingan antar-model untuk skenario ini"), use_container_width=True)
    st.caption("Rentang P10-P90 = sebaran prediksi antar pohon Random Forest, BUKAN interval kepercayaan "
               "statistik formal. Nilai lag diisi rata-rata historis latih bulan terkait.")

with t5:
    st.download_button("Unduh prediksi periode uji (CSV)", preds.to_csv(index=False).encode(),
                       "prediksi_uji.csv", "text/csv")
    st.download_button("Unduh metrik (CSV)", met.to_csv(index=False).encode(), "metrik.csv", "text/csv")
