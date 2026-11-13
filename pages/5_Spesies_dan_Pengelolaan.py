import numpy as np
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
import streamlit as st

from utils import DATA, TARGET, banner, get_master, mann_kendall, species_in

st.title("Spesies dan Pengelolaan (DPI dan Konservasi)")
d, dummy = get_master()
banner(dummy)
sp = species_in(d)

st.subheader("1. Parameter biologi")
p = DATA / "species_traits.csv"
if p.exists():
    traits = pd.read_csv(p)
else:
    st.warning("Nilai di bawah adalah PLACEHOLDER DUMMY (bukan data FishBase). Ganti dengan nilai nyata dan "
               "isi kolom sumber dan tanggal akses.")
    traits = pd.DataFrame({"species_key": sp, "Linf_cm": 80.0, "K_per_tahun": 0.6, "t0": -0.1, "Lm_cm": 45.0,
                           "vulnerability_1_5": 3, "iucn_status": "isi", "source": "DUMMY - isi dari FishBase/IUCN"})
traits = st.data_editor(traits, use_container_width=True, hide_index=True, key="traits")

st.subheader("2. Kurva pertumbuhan von Bertalanffy")
pick = st.selectbox("Spesies", traits.species_key)
r = traits[traits.species_key == pick].iloc[0]
t = np.linspace(0, 8, 100)
L = r.Linf_cm * (1 - np.exp(-r.K_per_tahun * (t - r.t0)))
fig = go.Figure()
fig.add_scatter(x=t, y=L, name="Panjang")
fig.add_hline(y=r.Lm_cm, line_dash="dash", annotation_text="Lm")
fig.update_layout(xaxis_title="Umur (tahun)", yaxis_title="Panjang (cm)")
st.plotly_chart(fig, use_container_width=True)
if r.Lm_cm < r.Linf_cm:
    tm = r.t0 - np.log(1 - r.Lm_cm / r.Linf_cm) / r.K_per_tahun
    st.metric("Umur saat Lm (tahun)", f"{tm:.2f}")
st.caption("Kurva hanya ilustrasi parameter yang Anda isi. Tidak mengestimasi status stok.")

st.subheader("3. Matriks risiko (kerentanan x tekanan)")
rows = []
tot = d[sp].sum().sum()
for s in sp:
    ann = d.groupby(d.year_month.dt.year)[s].sum().values
    sen = mann_kendall(ann)["sen"] if len(ann) >= 4 else 0.0
    v = traits.loc[traits.species_key == s, "vulnerability_1_5"]
    rows.append({"spesies": s, "kerentanan": float(v.iloc[0]) if len(v) else 3.0,
                 "tekanan (% total)": d[s].sum() / tot * 100, "tren (kg/th)": sen})
rk = pd.DataFrame(rows)
rk["ukuran"] = rk["tren (kg/th)"].abs() + 1
st.plotly_chart(px.scatter(rk, x="kerentanan", y="tekanan (% total)", size="ukuran", color="tren (kg/th)",
                           text="spesies", color_continuous_scale="RdBu"), use_container_width=True)
st.dataframe(rk.drop(columns="ukuran").round(2), use_container_width=True, hide_index=True)
st.caption("Tekanan di sini = porsi dalam total pendaratan, proksi kasar. Kerentanan diisi dari FishBase/IUCN "
           "atau penilaian pakar. Warna menunjukkan arah tren (Sen) per spesies.")

st.subheader("4. Rekomendasi (bahan diskusi)")
st.markdown("""
- Kaitkan setiap rekomendasi dengan temuan spesifik di halaman lain.
- Sesuaikan dengan regulasi KKP terkini (verifikasi sendiri).
- Tandai yang tidak dapat disimpulkan (mis. status stok tanpa data upaya dan panjang).
""")


st.subheader("5. Penapisan Batimetri Sukarela Tangkapan Sampingan Hiu (CITES 2026-03-05)")
st.info("Nama Resmi Lapisan Spasial: **Candidate Bathymetric Screening Polygons for Voluntary Shark Bycatch Review** (123 poligon kandidat di lereng benua 150–1200 m / tubir 200–1000 m, luas 3.653,23 km²). Bukan peta distribusi hiu, bukan hotspot terverifikasi, dan bukan zona larangan melaut.")
byc_path = DATA / "prigi_elasmobranch_bycatch_records.csv"
if byc_path.exists():
    byc_df = pd.read_csv(byc_path)
    st.dataframe(byc_df.head(12), use_container_width=True, hide_index=True)
