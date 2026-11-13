import pandas as pd
import streamlit as st

from utils import REQUIRED, banner, get_master

st.title("Data dan Keterbatasan")
d, dummy = get_master()
banner(dummy)

st.subheader("Unggah data nyata")
st.caption("CSV harus memuat kolom: " + ", ".join(REQUIRED) +
           ". Opsional: cakalang_kg, tuna_kg, tongkol_kg, tenggiri_kg, effort_trips, cpue_proxy, bad_weather_days.")
up = st.file_uploader("master.csv", type="csv")
if up is not None:
    df = pd.read_csv(up)
    if "year_month" in df.columns:
        df["year_month"] = pd.to_datetime(df["year_month"])
    else:
        df = None
    if df is None:
        st.error("Kolom year_month tidak ditemukan.")
    else:
        miss = [c for c in REQUIRED if c not in df.columns]
        if miss:
            st.error(f"Kolom wajib hilang: {miss}")
        else:
            st.session_state["uploaded_df"] = df
            st.success("Data diterima. Muat ulang halaman lain untuk melihat hasilnya.")
            st.rerun()
if "uploaded_df" in st.session_state and st.button("Kembali ke data bawaan/dummy"):
    del st.session_state["uploaded_df"]
    st.rerun()

st.subheader("Laporan kualitas data")
full = pd.date_range(d.year_month.min(), d.year_month.max(), freq="MS")
missing = full.difference(d.year_month)
a, b, c = st.columns(3)
a.metric("Jumlah baris", len(d))
b.metric("Bulan hilang", len(missing))
c.metric("Baris duplikat tanggal", int(d.year_month.duplicated().sum()))
if len(missing):
    st.write("Bulan hilang:", [f"{m:%Y-%m}" for m in missing][:24])
nulls = d.isna().sum()
st.dataframe(nulls[nulls > 0].rename("jumlah NaN").to_frame(), use_container_width=True)
st.caption("NaN pada lag di 12 bulan pertama normal.")

with st.expander("Jelajahi data"):
    st.dataframe(d, use_container_width=True)
    st.download_button("Unduh dataset master (CSV)", d.to_csv(index=False).encode(), "master.csv", "text/csv")

st.subheader("Sumber data (isi dan verifikasi)")
st.markdown("""
| Data | Sumber | Tanggal akses |
|---|---|---|
| Statistik pendaratan | Laporan Statistik PPN Prigi 2015-2025 (portal repositori KKP) | |
| Angin, tekanan, hujan | ERA5 (Copernicus CDS) | |
| SST, klorofil, SLA, gelombang | Copernicus Marine | |
| ENSO, IOD | NOAA (ONI, DMI) | |
| Parameter biologi, status | FishBase, IUCN Red List | |
""")

st.subheader("Keterbatasan")
st.markdown("""
- Ukuran sampel bulanan kecil, rentan overfitting.
- Kotak ekstraksi iklim adalah asumsi, bukan lokasi tangkap.
- Statistik pelabuhan merekam pendaratan, bukan stok atau upaya penuh.
- Korelasi dan importance bukan bukti sebab-akibat.
""")
