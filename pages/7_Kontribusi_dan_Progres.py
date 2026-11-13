import pandas as pd
import streamlit as st

st.title("Kontribusi Mata Kuliah dan Progres")

items = {
    "AHT (pendukung kuat)": ["Ekstraksi tabel dari PDF", "catch_clean.csv", "Grafik tren dan musiman", "Uji tren + komposisi"],
    "Meteorologi (inti)": ["Unduh ERA5/CMEMS/NOAA", "climate_monthly.csv", "Klimatologi dan anomali", "Korelasi lag + narasi mekanisme"],
    "AI (inti)": ["Dataset master", "Baseline + model", "Validasi temporal + ablasi", "Aplikasi ter-deploy"],
    "DPI (pendukung)": ["Parameter FishBase (dengan sumber)", "Kurva von Bertalanffy", "Indikator ukuran jika data ada"],
    "Konservasi (inti)": ["Status IUCN", "Matriks risiko", "Rekomendasi bertaut temuan"],
    "Administrasi": ["Bimbingan 1 (bukti)", "Bimbingan 2 (bukti)", "Poster", "Video 3-5 menit",
                     "Dokumen teknis", "Draf proposal PKM", "Rencana lomba 2026"],
}
done = total = 0
for group, tasks in items.items():
    st.subheader(group)
    for t in tasks:
        total += 1
        done += st.checkbox(t, key=f"{group}_{t}")
st.progress(done / total, text=f"{done}/{total} selesai")
st.caption("Centang hanya disimpan selama sesi browser. Catat progres final di dokumen teknis.")

st.subheader("Matriks bukti")
st.dataframe(pd.DataFrame({
    "Mata kuliah": ["AHT", "Meteorologi", "AI", "DPI", "Konservasi"],
    "Peran": ["Pendukung kuat", "Inti", "Inti", "Pendukung", "Inti"],
    "Halaman bukti": ["Hasil Tangkapan", "Iklim dan Laut", "Model dan Prediksi", "Spesies dan Pengelolaan",
                      "Spesies dan Pengelolaan, Kalender Musim"],
}), use_container_width=True, hide_index=True)
