import numpy as np
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
import streamlit as st

from utils import CLIMATE, LABELS, MONTHS, PORT, STUDY_BOX, TARGET, banner, get_master

st.title("Iklim dan Laut (Meteorologi)")
d, dummy = get_master()
banner(dummy)
st.caption("Sub-AOI Prigi [111.00°–113.00° BT, -10.45°–-8.00° LS] (50×41 = 2.050 grid centers, 59.550,43 km² equal-area) merupakan jendela analisis oseanografi regional, bukan batas resmi WPPNRI 573 atau koordinat tangkap GPS.")

t1, t2, t3, t4, t5 = st.tabs(["Klimatologi", "Anomali", "Korelasi lag", "Komposit IOD", "Area studi"])

with t1:
    v = st.selectbox("Variabel", CLIMATE, format_func=LABELS.get, key="clim_v")
    g = d.groupby(d.year_month.dt.month)[v]
    cl = pd.DataFrame({"bulan": MONTHS, "rata": g.mean().values, "sd": g.std().values})
    fig = go.Figure()
    fig.add_scatter(x=cl.bulan, y=cl.rata, mode="lines+markers", name="Rata-rata",
                    error_y=dict(type="data", array=cl.sd))
    fig.update_layout(yaxis_title=LABELS[v], title="Klimatologi bulanan (batang = 1 SD)")
    st.plotly_chart(fig, use_container_width=True)

with t2:
    v = st.selectbox("Variabel", CLIMATE, format_func=LABELS.get, key="anom_v")
    clim = d.groupby(d.year_month.dt.month)[v].transform("mean")
    an = d[v] - clim
    colors = np.where(an >= 0, "indianred", "steelblue")
    st.plotly_chart(go.Figure(go.Bar(x=d.year_month, y=an, marker_color=colors)).update_layout(
        yaxis_title=f"Anomali {LABELS[v]}", title="Anomali terhadap klimatologi bulanan"),
        use_container_width=True)

with t3:
    maxlag = st.slider("Lag maksimum (bulan)", 1, 12, 6)
    rows = {LABELS[k]: [d[TARGET].corr(d[k].shift(L)) for L in range(maxlag + 1)] for k in CLIMATE}
    cor = pd.DataFrame(rows, index=[f"lag {L}" for L in range(maxlag + 1)]).T
    st.plotly_chart(px.imshow(cor, zmin=-1, zmax=1, color_continuous_scale="RdBu_r", text_auto=".2f",
                              aspect="auto"), use_container_width=True)
    st.caption("Banyak uji sekaligus, jadi beberapa korelasi bisa muncul karena kebetulan. Korelasi bukan sebab-akibat.")

with t4:
    thr = st.slider("Ambang |DMI tahunan| (unit indeks)", 0.0, 1.0, 0.2, 0.05)
    ann = d.groupby(d.year_month.dt.year)["dmi"].mean()
    cls = pd.Series(np.where(ann > thr, "IOD positif", np.where(ann < -thr, "IOD negatif", "Netral")), index=ann.index)
    dd = d.assign(kelas=d.year_month.dt.year.map(cls), bulan=d.year_month.dt.month)
    comp = dd.groupby(["kelas", "bulan"])[TARGET].mean().reset_index()
    comp["bulan"] = comp.bulan.map(lambda m: MONTHS[m - 1])
    st.plotly_chart(px.line(comp, x="bulan", y=TARGET, color="kelas", markers=True,
                            category_orders={"bulan": MONTHS}), use_container_width=True)
    st.write("Jumlah tahun per kelas:", cls.value_counts().to_dict())
    st.caption("Klasifikasi ini sederhana (rata-rata tahunan, ambang bebas), bukan definisi resmi IOD. "
               "Jumlah tahun per kelas kecil, jadi hanya eksploratif.")

with t5:
    la, lo = STUDY_BOX["lat"], STUDY_BOX["lon"]
    fig = go.Figure()
    fig.add_trace(go.Scattermapbox(lat=[la[0], la[0], la[1], la[1], la[0]],
                                   lon=[lo[0], lo[1], lo[1], lo[0], lo[0]], mode="lines",
                                   name="Sub-AOI Prigi [111.00-113.00 BT, -10.45--8.00 LS]"))
    fig.add_trace(go.Scattermapbox(lat=[PORT[0]], lon=[PORT[1]], mode="markers+text",
                                   text=["PPN Prigi (-8.2818 LS, 111.7314 BT)"], marker=dict(size=12),
                                   name="Pelabuhan"))
    fig.update_layout(mapbox=dict(style="open-street-map", center=dict(lat=PORT[0], lon=PORT[1]), zoom=7),
                      margin=dict(l=0, r=0, t=0, b=0), height=500)
    st.plotly_chart(fig, use_container_width=True)
    st.caption("Koordinat perkiraan. Cek dan sesuaikan di utils.py (PORT, STUDY_BOX).")
