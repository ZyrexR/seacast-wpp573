import pandas as pd
import streamlit as st

from utils import MONTHS, TARGET, banner, get_master

st.title("Kalender Musim (indikatif)")
d, dummy = get_master()
banner(dummy)

agg = {"prod": (TARGET, "mean"), "hs": ("hs_mean", "mean")}
if "bad_weather_days" in d.columns:
    agg["bw"] = ("bad_weather_days", "mean")
mon = d.groupby(d.year_month.dt.month).agg(**agg)
mon["indeks"] = mon["prod"] / mon["prod"].mean()

c = st.columns(3)
hi = c[0].slider("Ambang musim puncak (indeks >)", 1.0, 1.5, 1.1, 0.05)
lo = c[1].slider("Ambang musim sepi (indeks <)", 0.5, 1.0, 0.9, 0.05)
thr = c[2].slider("Ambang gelombang waspada (m)", float(d.hs_mean.min()), float(d.hs_mean.max()),
                  float(d.hs_mean.quantile(.75)), 0.05)

tab = pd.DataFrame({
    "Bulan": MONTHS,
    "Indeks produksi": mon["indeks"].round(2).values,
    "Status": ["🟢 Puncak" if i > hi else "🔴 Sepi" if i < lo else "🟡 Sedang" for i in mon["indeks"]],
    "Rata-rata Hs (m)": mon["hs"].round(2).values,
    "Cuaca laut": ["⚠️ Waspada" if h > thr else "✅ Relatif tenang" for h in mon["hs"]],
})
if "bw" in mon:
    tab["Hari cuaca buruk (rata-rata)"] = mon["bw"].round(1).values
st.dataframe(tab, use_container_width=True, hide_index=True)
st.caption("Ambang ditentukan pengguna, bukan standar resmi. Indikator ini berbasis rata-rata historis, "
           "BUKAN peringatan dini. Untuk keselamatan, ikuti prakiraan resmi BMKG.")
