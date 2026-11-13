# OCEANOGRAPHIC INDICATOR DERIVATION METHODS & MATHEMATICAL SPECIFICATIONS
**Project:** SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi, WPPNRI 573  
**Phase:** 0.7 — Scientific Review of Oceanographic Indicators  
**Domain:** WPPNRI 573 (102.58°–126.50° BT, 6.80°–13.94° LS)  
**Physics Reference:** Physical Oceanography of the Eastern Indian Ocean (Tomczak & Godfrey, 2003; Sprintall et al., 2009)  

---

## 1. THERMAL FRONT DETECTION (DETEKSI FRONT TERMAL)

### 1.1 Formulasi Gradien Spasial pada Koordinat Sferis
Dalam domain geografis, jarak fisik per derajat bujur bervariasi terhadap lintang $\phi$:
$$dx = R \cos(\phi) \cdot d\lambda$$
$$dy = R \cdot d\phi$$
di mana $R = 6.371.000\text{ m}$ (jari-jari bumi rata-rata), $d\lambda$ adalah selisih bujur dalam radian, dan $d\phi$ adalah selisih lintang dalam radian.

Gradien spasial suhu permukaan laut ($SST$) dihitung menggunakan kernel konvolusi Sobel berbobot sferis:
$$\nabla SST = \left( \frac{\partial SST}{\partial x}, \frac{\partial SST}{\partial y} \right)$$
$$|\nabla SST| = \sqrt{ \left(\frac{\partial SST}{\partial x}\right)^2 + \left(\frac{\partial SST}{\partial y}\right)^2 } \times 1.000 \quad [^\circ\text{C}/\text{km}]$$

### 1.2 Ambang Batas Deteksi Front Pelagis
Berdasarkan literatur oseanografi perikanan tropis (Belkin & O'Reilly, 2009; Zainuddin et al., 2008):
- **Front Lemah:** $|\nabla SST| < 0.03^\circ\text{C}/\text{km}$ ($< 0.3^\circ\text{C} / 10\text{ km}$) — Latar belakang massa air homogen.
- **Front Sedang (Front Aktif):** $0.03 \le |\nabla SST| < 0.05^\circ\text{C}/\text{km}$ — Indikator pertemuan massa air pesisir dengan Samudera Hindia.
- **Front Kuat (Thermal Front Signifikan):** $|\nabla SST| \ge 0.05^\circ\text{C}/\text{km}$ ($\ge 0.5^\circ\text{C} / 10\text{ km}$) — Batas upwelling intensif di selatan Jawa-Bali, zona retensi agregasi cakalang (*Katsuwonus pelamis*).

---

## 2. WIND STRESS & EKMAN UPWELLING DYNAMICS

### 2.1 Gaya Gesek Angin Permukaan (Surface Wind Stress $\vec{\tau}$)
Dihitung dari angin 10 meter CFSV2 ($\vec{W} = (u, v)$):
$$\vec{\tau} = \rho_a C_d |\vec{W}| \vec{W}$$
di mana $\rho_a = 1.225\text{ kg/m}^3$ dan koefisien seret $C_d$ menggunakan formulasi Wu (1982):
$$C_d = \begin{cases} (0.8 + 0.065 |\vec{W}|) \times 10^{-3}, & |\vec{W}| < 11\text{ m/s} \\ (0.49 + 0.065 |\vec{W}|) \times 10^{-3}, & |\vec{W}| \ge 11\text{ m/s} \end{cases}$$

### 2.2 Parameter Coriolis & Perilaku di WPPNRI 573
Parameter Coriolis didefinisikan sebagai:
$$f = 2\Omega \sin(\phi)$$
di mana $\Omega = 7.2921 \times 10^{-5}\text{ rad/s}$.

**Catatan Ilmiah Khusus Domain WPPNRI 573:**
- Batas utara WPPNRI 573 adalah $\phi = -6.802^\circ\text{ LS} \implies f = -1.728 \times 10^{-5}\text{ s}^{-1}$.
- Batas selatan WPPNRI 573 adalah $\phi = -13.938^\circ\text{ LS} \implies f = -3.513 \times 10^{-5}\text{ s}^{-1}$.
- **Kesimpulan Validitas:** Seluruh domain WPPNRI 573 berada di Belahan Bumi Selatan dengan $|f| > 1.7 \times 10^{-5}\text{ s}^{-1}$. Tidak ada titik ekuator ($f = 0$) di dalam domain, sehingga perhitungan Ekman transport **tidak mengalami singularitas matematis**, dan tidak memerlukan *damping* ekuatorial artifisial.

### 2.3 Transpor Massa Ekman & Kecepatan Pemompaan Ekman (Ekman Pumping $W_E$)
$$M_{Ex} = \frac{\tau_y}{f}, \quad M_{Ey} = -\frac{\tau_x}{f}$$
Kecepatan pemompaan vertikal Ekman (*Ekman pumping velocity*):
$$W_E = \frac{1}{\rho_w f} \left( \frac{\partial \tau_y}{\partial x} - \frac{\partial \tau_x}{\partial y} \right) + \frac{\beta \tau_x}{\rho_w f^2}$$
di mana $\rho_w = 1.025\text{ kg/m}^3$ dan $\beta = \frac{2\Omega \cos(\phi)}{R}$.

**Konvensi Tanda Belahan Bumi Selatan (BBS):**
Karena $f < 0$ di BBS, *wind stress curl* siklonik ($\text{curl}(\vec{\tau}) < 0$) menghasilkan **$W_E > 0$ (Upwelling vertikal ke atas)**. Selama monsun tenggara (Juni–Oktober), angin timuran menyusuri pantai selatan Jawa memicu transpor Ekman lepas pantai ($M_{Ey} < 0$), menghasilkan *coastal upwelling* kuat di selatan Prigi.

---

## 3. INDIKATOR ARUS LAUT & KINETIKA EDDY (HYCOM)

### 3.1 Eddy Kinetic Energy (EKE)
$$\text{EKE} = \frac{1}{2} \left( (u - \bar{u})^2 + (v - \bar{v})^2 \right)$$
EKE tinggi ($> 0.05\text{ m}^2/\text{s}^2$) mengindikasikan adanya pusaran mesoskala (*mesoscale eddies*), baik siklonik (pusat dingin/nutrisi tinggi) maupun antisiklonik (zona konvergensi batas).

---

## 4. INDIKATOR BATIMETRI & KEMIRINGAN LERENG (ETOPO1)

Kedalaman riil laut:
$$Z_{depth} = -\text{bedrock} \quad (\text{meter})$$
Kemiringan lereng dasar laut (*slope* $\theta$):
$$\theta = \arctan\left( \sqrt{ \left(\frac{\partial Z}{\partial x}\right)^2 + \left(\frac{\partial Z}{\partial y}\right)^2 } \right) \times \frac{180^\circ}{\pi}$$
Zona lereng curam ($\theta > 2^\circ$) pada paparan neritik luar (kedalaman 100–500m) di selatan Prigi merupakan jalur migrasi utama madidihang (*Thunnus albacares*) dan legacy_species_removed (*Current TCT scope exclusion*).
