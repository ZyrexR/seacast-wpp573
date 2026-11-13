# PHYSICAL OCEANOGRAPHIC FORMULA SPECIFICATION
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** PHYS-SPEC-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Physical Oceanographer  
**Date:** 2026-11-02  

---

## 1. FORMULASI RESMI & DEFINISI SIMBOL

### 1.1 Gradien Suhu Permukaan Laut (SST Gradient)
$$\|\nabla T\| = \sqrt{\left(\frac{\partial T}{\partial x}\right)^2 + \left(\frac{\partial T}{\partial y}\right)^2} \quad [^\circ\text{C/km}]$$
- $dx = R_E \cos(\phi) d\lambda \frac{\pi}{180^\circ}$ [m]
- $dy = R_E d\phi \frac{\pi}{180^\circ}$ [m]
- $R_E = 6.371.000\text{ m}$ (Jari-jari bola bumi).

### 1.2 Tegangan Angin Permukaan (Wind Stress)
$$\boldsymbol{\tau} = \rho_a C_d \|\mathbf{U}_{10}\| \mathbf{U}_{10} \quad [\text{N/m}^2]$$
- $\rho_a = 1,225\text{ kg/m}^3$ (Densitas udara permukaan).
- $C_d(U) = (0,80 + 0,065 U) \times 10^{-3}$ untuk $3 \le U \le 25\text{ m/s}$ (Large & Pond / Wu).

### 1.3 Dinamika Ekman Belahan Bumi Selatan
$$f = 2 \Omega \sin(\phi) < 0 \quad [\text{s}^{-1}]$$
$$\mathbf{M}_E = \frac{\boldsymbol{\tau} \times \hat{\mathbf{k}}}{\rho_w f} \quad [\text{m}^2/\text{s}]$$
$$w_E = \frac{1}{\rho_w f} (\nabla \times \boldsymbol{\tau}) + \frac{\beta \tau_x}{\rho_w f^2} \quad [\text{m/s}]$$
- $\rho_w = 1.025\text{ kg/m}^3$ (Densitas air laut rata-rata).
- $\beta = \frac{2 \Omega \cos(\phi)}{R_E}$ [$\text{m}^{-1}\text{s}^{-1}$].
- **Konvensi Tanda Belahan Bumi Selatan:** Transpor membelok 90° ke KIRI arah angin. Curl negatif menghasilkan $w_E > 0$ (Upwelling vertikal).

### 1.4 Komposit Rata-Rata Logaritmik Klorofil-a
$$\text{Chl}_{\text{monthly}} = 10^{\frac{1}{N} \sum_{i=1}^N \log_{10}(\text{Chl}_i)} \quad [\text{mg/m}^3]$$

### 1.5 Kemiringan Batimetri (Bathymetric Slope)
$$\theta = \arctan(\|\nabla z\|) \cdot \frac{180^\circ}{\pi} \quad [^\circ]$$
- Kedalaman $D = -z \ge 0\text{ m}$.
- Ambang batas paparan benua (*continental shelf break*): $D \le 200\text{ m}$ (Neritik) vs $D > 200\text{ m}$ (Oseanik).
