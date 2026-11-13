# GEE OCEANOGRAPHIC PARAMETER METHODOLOGY & PHYSICAL EQUATIONS
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** GEE-METH-01  
**Status:** PEER-REVIEWED SCIENTIFIC STANDARDS  

---

## 1. SUHU PERMUKAAN LAUT (SST) & DETEKSI FRONT TERMAL
- **Sumber Data:** NOAA OISST v2.1 (AVHRR + In-situ Blended, Daily, 0.25°).
- **Agregasi Bulanan:**
  $$\overline{T} = \frac{1}{N} \sum_{i=1}^N T_i \quad (^\circ\text{C})$$
- **Deteksi Front Termal (Metode Sobel Fisik):**
  Untuk menghindari distorsi gradien per derajat yang mengabaikan konvergensi meridian, jarak spasial dihitung dalam kilometer metrik:
  $$\Delta x = R_E \cos(\phi) \Delta\lambda, \quad \Delta y = R_E \Delta\phi$$
  $$\frac{\partial T}{\partial x} = \frac{\text{Sobel}_x(T)}{\Delta x_{\text{km}}}, \quad \frac{\partial T}{\partial y} = \frac{\text{Sobel}_y(T)}{\Delta y_{\text{km}}}$$
  $$|\nabla T| = \sqrt{\left(\frac{\partial T}{\partial x}\right)^2 + \left(\frac{\partial T}{\partial y}\right)^2} \quad (^\circ\text{C/km})$$
  Front termal kuat ditandai ketika $|\nabla T| \ge 0.05^\circ\text{C/km}$.

---

## 2. DINAMIKA ANGIN & PEMOMPAAN EKMAN (WU 1982 FORMULATION)
- **Sumber Data:** NOAA CFSv2 Harmonized (6-Hourly Analysis, 0.205°).
- **Kecepatan Angin Permukaan (10m):**
  $$W = \sqrt{U_{10}^2 + V_{10}^2}$$
- **Koefisien Hambatan Permukaan Laut (Wu 1982):**
  $$C_d = \text{clip}((0.8 + 0.065 \cdot W) \times 10^{-3}, 1.0 \times 10^{-3}, 2.5 \times 10^{-3})$$
- **Tegangan Angin Surface:**
  $$\tau_x = \rho_{\text{air}} C_d W U_{10}, \quad \tau_y = \rho_{\text{air}} C_d W V_{10}, \quad |\boldsymbol{\tau}| = \rho_{\text{air}} C_d W^2$$
  dengan densitas udara $\rho_{\text{air}} = 1.22\text{ kg/m}^3$.
- **Kecepatan Pemompaan Ekman Vertikal ($w_E$):**
  $$w_E = \frac{\text{curl}(\boldsymbol{\tau})}{\rho_w f} - \frac{\beta \tau_x}{\rho_w f^2}$$
  dengan:
  - $\rho_w = 1025.0\text{ kg/m}^3$ (densitas air laut standar).
  - Parameter Coriolis: $f = 2\Omega \sin(\phi)$ (bernilai negatif di Belahan Bumi Selatan / WPP 573).
  - Parameter Rossby Beta: $\beta = \frac{2\Omega \cos(\phi)}{R_E}$.
  - Konvensi Tanda: Kecepatan vertikal $w_E > 0$ menunjukkan arus ke atas (*upward vertical velocity / upwelling*).
  - Konversi Satuan:
    $$w_E\ (\text{m/day}) = w_E\ (\text{m/s}) \times 86400$$
    (Contoh verifikasi unit test UT-06: $1.755 \times 10^{-5}\text{ m/s} = 1.51632\text{ m/day}$).

---

## 3. MARJIN STABILITAS NUMERIK DI DEKAT EKUATOR
Di daerah tropis mendekati khatulistiwa, $|f| \rightarrow 0$, sehingga suku $1/f$ dan $1/f^2$ berisiko singular.
Pada domain studi WPPNRI 573:
- Lintang paling utara adalah $\phi_{\max} = -6.80^\circ\text{S}$.
- Nilai Coriolis minimum:
  $$|f_{\min}| = 2 (7.2921 \times 10^{-5}) \sin(6.80^\circ) = 1.7268 \times 10^{-5}\text{ s}^{-1}$$
- Nilai ini berada **jauh di atas batas singularitas ekuator ($|\phi| < 2^\circ$ atau $|f| < 5 \times 10^{-6}\text{ s}^{-1}$)**, sehingga formulasi pemompaan Ekman pada seluruh sel WPPNRI 573 terbukti secara fisik dan numerik stabil tanpa memerlukan pemotongan buatan (*arbitrary clamping*).
