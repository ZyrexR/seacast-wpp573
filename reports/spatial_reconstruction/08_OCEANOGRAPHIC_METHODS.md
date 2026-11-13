# 08 — PHYSICAL OCEANOGRAPHIC METHODS & MATHEMATICAL FORMULATIONS
## SEACAST PRIGI — Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) 573

- **Document ID:** `OCN-METH-08`
- **Tujuan:** Dokumentasi persamaan matematis, konvensi tanda, dan satuan fisik prediktor oseanografi.

---

## 1. Gradien Front Termal Metrik Bola (Spherical Metric Sobel Gradient)

$$ \Delta x = \Delta\lambda \times 111.320 \times \cos(\phi) \quad [\text{km}] $$
$$ \Delta y = \Delta\phi \times 110.574 \quad [\text{km}] $$
$$ \|\nabla \text{SST}\| = \sqrt{ \left(\frac{\partial T}{\partial x}\right)^2 + \left(\frac{\partial T}{\partial y}\right)^2 } \quad [^\circ\text{C/km}] $$

Operator ini menggantikan pembagian konstan arbitrary `/4.63` pada skrip lama dan memberikan nilai intensitas front termal yang akurat secara metrik di seluruh lintang WPPNRI 573.

---

## 2. Dinamika Transpor Ekman & Redaman Lintang Rendah

Tegangan angin permukaan ($\tau$):
$$ \tau_x = \rho_a C_d u_{10} \sqrt{u_{10}^2 + v_{10}^2} \quad [\text{Pa}] $$
dengan $\rho_a = 1.22\text{ kg/m}^3$ dan $C_d = 1.3 \times 10^{-3}$.

Parameter Coriolis teredam:
$$ f_{\text{damped}}(\phi) = 2\Omega \sin(\max(|\phi|, 2.0^\circ) \times \text{sgn}(\phi)) $$
dengan $\Omega = 7.2921 \times 10^{-5}\text{ rad/s}$.

Transpor Ekman Meridional:
$$ M_{ey} = -\frac{\tau_x}{\rho_w f_{\text{damped}}} \quad [\text{m}^2/\text{s}] $$
dengan $\rho_w = 1025.0\text{ kg/m}^3$.
Di Belahan Bumi Selatan ($\phi < 0$), angin Monsun Tenggara dari timur ($u_{10} < 0$) menghasilkan $\tau_x < 0$, dan dengan $f < 0$, nilai $M_{ey} < 0$ menunjukkan transpor massa air ke arah selatan (lepas pantai) yang memicu *coastal upwelling*.
