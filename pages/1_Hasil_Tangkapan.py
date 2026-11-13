import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
import streamlit as st

from utils import MONTHS, TARGET, banner, decompose, get_master, mann_kendall, species_in

st.title("Hasil Tangkapan (AHT)")
d, dummy = get_master()
banner(dummy)
sp = species_in(d)

y0, y1 = int(d.year_month.dt.year.min()), int(d.year_month.dt.year.max())
yr = st.sidebar.slider("Rentang tahun", y0, y1, (y0, y1))
sub = d[d.year_month.dt.year.between(*yr)]

t1, t2, t3, t4 = st.tabs(["Tren dan uji tren", "Dekomposisi musiman", "Heatmap tahun x bulan",
                          "Komposisi dan CPUE"])

with t1:
    st.plotly_chart(px.line(sub, x="year_month", y=TARGET, labels={TARGET: "kg", "year_month": ""}),
                    use_container_width=True)
    ann = sub.groupby(sub.year_month.dt.year)[TARGET].sum()
    if len(ann) >= 4:
        mk = mann_kendall(ann.values)
        a, b, c = st.columns(3)
        a.metric("Kemiringan Sen (kg/tahun)", f"{mk['sen']:,.0f}")
        b.metric("Z Mann-Kendall", f"{mk['Z']:.2f}")
        c.metric("p-value", f"{mk['p']:.3f}")
        st.caption("Uji pada total tahunan. Tahun terakhir bisa belum lengkap. Tanpa koreksi ties.")
    else:
        st.info("Minimal 4 tahun untuk uji tren.")

with t2:
    s = d.set_index("year_month")[TARGET]
    tr_, se_, re_ = decompose(s)
    fig = go.Figure()
    fig.add_scatter(x=s.index, y=s, name="Asli", opacity=.4)
    fig.add_scatter(x=tr_.index, y=tr_, name="Tren (rata-rata bergerak 12 bln)")
    st.plotly_chart(fig, use_container_width=True)
    idx = se_.groupby(se_.index.month).first()
    st.plotly_chart(px.bar(x=MONTHS, y=idx.values, labels={"x": "Bulan", "y": "Indeks musim"},
                           title="Indeks musim (1 = rata-rata)"), use_container_width=True)
    st.plotly_chart(px.line(x=re_.index, y=re_.values, labels={"x": "", "y": "Residu (rasio)"},
                            title="Residu"), use_container_width=True)

with t3:
    piv = sub.pivot_table(index=sub.year_month.dt.year, columns=sub.year_month.dt.month,
                          values=TARGET, aggfunc="sum")
    piv.columns = [MONTHS[i - 1] for i in piv.columns]
    st.plotly_chart(px.imshow(piv, color_continuous_scale="YlGnBu", aspect="auto",
                              labels={"x": "Bulan", "y": "Tahun", "color": "kg"}),
                    use_container_width=True)

with t4:
    if sp:
        comp = sub[sp].sum().reset_index()
        comp.columns = ["jenis", "kg"]
        a, b = st.columns(2)
        a.plotly_chart(px.pie(comp, names="jenis", values="kg", title="Komposisi"),
                       use_container_width=True)
        stack = sub.melt(id_vars="year_month", value_vars=sp, var_name="jenis", value_name="kg")
        b.plotly_chart(px.area(stack, x="year_month", y="kg", color="jenis", title="Komposisi bulanan"),
                       use_container_width=True)
    if "cpue_proxy" in d.columns:
        st.plotly_chart(px.line(sub, x="year_month", y="cpue_proxy",
                                labels={"cpue_proxy": "kg/trip"}, title="CPUE proksi (produksi / trip)"),
                        use_container_width=True)
        st.caption("CPUE proksi kasar. Upaya (trip) belum menyatakan standarisasi alat tangkap.")
    else:
        st.info("Kolom cpue_proxy / effort_trips tidak tersedia.")
