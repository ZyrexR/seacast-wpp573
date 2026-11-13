import numpy as np
import streamlit as st

from utils import (CLIMATE, LABELS, MONTHS, TARGET, banner, get_master, mann_kendall,
                   train_models)

st.set_page_config(page_title="Pelagis Besar PPN Prigi", page_icon="🐟", layout="wide")
d, dummy = get_master()
banner(dummy)

st.title("🐟 Dasbor Ikan Pelagis Besar PPN Prigi")
st.caption("Proyek ASIK VI 2026 | Analisis tangkapan, meteorologi laut, model prediksi, dan konservasi")
st.markdown("Memahami pola musim pendaratan, faktor meteorologi dan oseanografi yang berasosiasi, "
            "serta prakiraan indikatif beserta ketidakpastiannya. Buka halaman lain lewat menu kiri.")

tr = train_models(d)
met = tr["metrics"]
best = met.iloc[1:].sort_values("MAE").iloc[0]
gain = (1 - best["MAE"] / met.iloc[0]["MAE"]) * 100
annual = d.groupby(d.year_month.dt.year)[TARGET].sum()
mk = mann_kendall(annual.values)
peak = d.groupby(d.year_month.dt.month)[TARGET].mean().idxmax()

c = st.columns(4)
c[0].metric("Periode data", f"{d.year_month.min():%Y} - {d.year_month.max():%Y}")
c[1].metric("Jumlah bulan", len(d))
c[2].metric("Model terbaik (MAE uji)", best["Model"])
c[3].metric("Selisih MAE vs baseline", f"{gain:+.1f}%")

st.subheader("Temuan otomatis")
best_lag = max(((v, L, d[TARGET].corr(d[v].shift(L))) for v in CLIMATE for L in range(7)),
               key=lambda t: abs(np.nan_to_num(t[2])))
arah = "naik" if mk["sen"] > 0 else "turun"
st.markdown(f"""
- Bulan dengan rata-rata pendaratan tertinggi: **{MONTHS[peak - 1]}**.
- Tren tahunan (Mann-Kendall): kecenderungan **{arah}** (Sen {mk['sen']:,.0f} kg/tahun, p = {mk['p']:.3f}). {'Signifikan pada 5%.' if mk['p'] < .05 else 'Tidak signifikan pada 5%.'}
- Asosiasi klimatik terkuat: **{LABELS[best_lag[0]]}** pada lag **{best_lag[1]} bulan** (r = {best_lag[2]:.2f}). Korelasi bukan sebab-akibat.
- {'Model mengungguli baseline musiman pada periode uji.' if gain > 0 else 'Model TIDAK mengungguli baseline musiman. Laporkan apa adanya.'}
""")
st.info("Prediksi bersifat indikatif berdasarkan data historis. Bukan peringatan cuaca resmi (rujuk BMKG).")
