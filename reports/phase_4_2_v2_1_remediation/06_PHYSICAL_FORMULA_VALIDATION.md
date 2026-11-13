# SEACAST PRIGI — PHYSICAL OCEANOGRAPHIC FORMULA VALIDATION
## PHASE 4.2 GEE PRODUCTION SCRIPT v2.1
### Mathematical Derivations, Physical Constants, and Unit Conversions

**Document ID:** `SEACAST-PHASE-4.2-v2.1-PHYSICAL-FORMULAS`  
**Classification:** `SCIENTIFIC_VALIDATION`  
**Target Script:** [`gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js)  
**Study Domain:** WPPNRI 573 ($105.0^\circ\text{E} - 117.0^\circ\text{E}$, $6.8^\circ\text{S} - 14.0^\circ\text{S}$)  
**Date of Validation:** 2026-11-02  

---

## 1. PHYSICAL CONSTANTS & CANONICAL VALUES

The geophysical calculations across all modules adhere strictly to International Oceanographic Standards (TEOS-10 / Gill, 1982):

| Symbol | Physical Description | Canonical Value | Units | Code Identifier |
| :--- | :--- | :--- | :--- | :--- |
| $\rho_a$ | Surface Air Density | $1.225$ | $\text{kg/m}^3$ | `CONFIG.PHYSICAL.RHO_AIR` |
| $\rho_w$ | Seawater Reference Density | $1025.0$ | $\text{kg/m}^3$ | `CONFIG.PHYSICAL.RHO_WATER` |
| $\Omega$ | Earth Angular Rotation Rate | $7.2921 \times 10^{-5}$ | $\text{rad/s}$ | `CONFIG.PHYSICAL.OMEGA` |
| $R_e$ | Mean Earth Radius | $6.371 \times 10^6$ | $\text{m}$ | `CONFIG.PHYSICAL.EARTH_RADIUS` |
| $f_{\text{min}}$| Minimum Coriolis Threshold | $1.0 \times 10^{-5}$ | $\text{s}^{-1}$ | `CONFIG.PHYSICAL.F_MIN` |

---

## 2. SURFACE WIND STRESS (WU, 1982)

### 2.1 Drag Coefficient Formulation
Surface wind stress over open water is parameterized following Wu (1982) for neutral atmospheric stability at 10 meters height:

$$C_d(W_{10}) = \left(0.8 + 0.065 \, W_{10}\right) \times 10^{-3}$$

To prevent non-physical stress values under light winds or extreme convective gusts, $C_d$ is bounded:
$$C_d = \min\left(\max\left(C_d, \, 0.0010\right), \, 0.0025\right)$$

- At $W_{10} = 0\text{ m/s}$: $C_d = 0.0010$ (clamped).
- At $W_{10} = 10\text{ m/s}$: $C_d = (0.8 + 0.65) \times 10^{-3} = 0.001450$.
- At $W_{10} \ge 26.15\text{ m/s}$: $C_d = 0.0025$ (clamped).

### 2.2 Instantaneous Stress Components
Wind stress vectors are computed at each 6-hour synoptic time step $t_k$:

$$\tau_x(t_k) = \rho_a \, C_d(W_{10}) \, W_{10} \, u_{10}$$
$$\tau_y(t_k) = \rho_a \, C_d(W_{10}) \, W_{10} \, v_{10}$$
$$\tau(t_k) = \sqrt{\tau_x^2 + \tau_y^2} = \rho_a \, C_d(W_{10}) \, W_{10}^2$$

### 2.3 Instantaneous vs Monthly Mean Averaging Justification
Wind stress is quadratic with respect to wind speed: $\tau \propto W_{10}^2$. By Jensen's inequality:

$$\overline{W_{10}^2} \ge \left(\overline{W_{10}}\right)^2$$

Computing wind stress from monthly averaged winds $(\overline{u}, \overline{v})$ systematically underestimates true momentum transfer by $20\%$ to $45\%$, particularly in monsoonal upwelling systems where synoptic wind bursts drive mixing. In `v2.1`, wind stress is computed instantaneously across every 6-hour CFSv2 frame before temporal reduction to monthly means.

---

## 3. EKMAN PUMPING VELOCITY & UPWELLING FORMULATION

### 3.1 Mathematical Derivation
The steady-state horizontal Ekman transport integrated over the surface boundary layer is:

$$\mathbf{M}_E = \left(M_{E,x}, M_{E,y}\right) = \left(\frac{\tau_y}{\rho_w f}, \, -\frac{\tau_x}{\rho_w f}\right)$$

Conservation of mass in an incompressible fluid yields vertical Ekman pumping velocity at the base of the Ekman layer ($z = -d_E$):

$$w_E = \nabla \cdot \mathbf{M}_E = \frac{\partial M_{E,x}}{\partial x} + \frac{\partial M_{E,y}}{\partial y}$$
$$w_E = \frac{\partial}{\partial x}\left(\frac{\tau_y}{\rho_w f}\right) + \frac{\partial}{\partial y}\left(-\frac{\tau_x}{\rho_w f}\right)$$

Applying the product rule and recognizing that $f = f(y)$ depends only on latitude:

$$\frac{\partial}{\partial y}\left(-\frac{\tau_x}{\rho_w f}\right) = -\frac{1}{\rho_w f}\frac{\partial \tau_x}{\partial y} - \frac{\tau_x}{\rho_w}\frac{\partial (f^{-1})}{\partial y}$$

Since $\frac{\partial (f^{-1})}{\partial y} = -\frac{1}{f^2}\frac{\partial f}{\partial y} = -\frac{\beta}{f^2}$, the double negative produces a positive term:

$$-\frac{\tau_x}{\rho_w}\left(-\frac{\beta}{f^2}\right) = +\frac{\beta \tau_x}{\rho_w f^2}$$

Combining terms yields the complete governing equation implemented in Module 11:

$$w_E = \frac{1}{\rho_w f}\left(\frac{\partial \tau_y}{\partial x} - \frac{\partial \tau_x}{\partial y}\right) + \frac{\beta \tau_x}{\rho_w f^2} = \frac{\text{curl}(\boldsymbol{\tau})}{\rho_w f} + \frac{\beta \tau_x}{\rho_w f^2} \quad [\text{m/s}]$$

### 3.2 Southern Hemisphere Dynamics & Equatorial Boundary
- **Latitude Domain:** WPPNRI 573 spans $-14.0^\circ\text{S} \le \phi \le -6.8^\circ\text{S}$.
- **Planetary Vorticity:** $\phi < 0 \implies f = 2\Omega\sin\phi < 0$.
- **Beta Parameter:** $\beta = \frac{2\Omega\cos\phi}{R_e} > 0$.
- **Equatorial Margin Stability:**
  At the northernmost boundary of WPPNRI 573 ($\phi = -6.80^\circ$):
  $$|f| = 2 \times (7.2921 \times 10^{-5}) \times \sin(6.80^\circ) = 1.727 \times 10^{-5}\text{ s}^{-1}$$
  Because $|f| = 1.727 \times 10^{-5}\text{ s}^{-1} > f_{\text{min}} = 1.0 \times 10^{-5}\text{ s}^{-1}$, the entire analysis domain is oceanographically and numerically stable without division-by-zero singularities.
- **Physical Sign Convention:**
  In the Southern Hemisphere ($f < 0$), a negative wind stress curl ($\text{curl}(\boldsymbol{\tau}) < 0$, clockwise cyclonic rotation) drives divergent Ekman transport, producing positive vertical velocity ($w_E > 0$, upwelling).

---

## 4. VERTICAL VELOCITY UNIT CONVERSION (UT-06 / UT-07 CORRECTION)

### 4.1 Canonical Conversion
Standard SI velocity in $\text{m/s}$ converts to daily displacement in $\text{m/day}$ via the exact diurnal factor:

$$T_{\text{day}} = 86,400\text{ seconds}$$
$$w_{\text{m/day}} = w_{\text{m/s}} \times 86,400\text{ s/day}$$

### 4.2 Remediation of Transcription Discrepancy
For the canonical baseline velocity benchmark:
$$w_E = 1.755 \times 10^{-5}\text{ m/s}$$
$$w_{E, \text{day}} = 1.755 \times 10^{-5}\text{ m/s} \times 86,400\text{ s/day} = 1.51632\text{ m/day} \approx 1.52\text{ m/day}$$

The previously transcribed value of $1.68\text{ m/day}$ represents an erroneous arithmetic product ($1.755 \times 10^{-5} \times 95726$). The correct, verified value of $1.516\text{ m/day}$ ($1.52\text{ m/day}$ rounded) is formally documented and validated.

---

## 5. PHYSICAL THERMAL FRONT SOBEL GRADIENT

### 5.1 Metric Distance Convergence
Horizontal gradients on spherical coordinates require scaling degree intervals by local physical distance:

$$\Delta y = \Delta\phi_{\text{deg}} \times \left(\frac{\pi}{180}\right) \times R_e = 0.25^\circ \times \frac{\pi}{180} \times 6371\text{ km} = 27.799\text{ km}$$
$$\Delta x(\phi) = \Delta\lambda_{\text{deg}} \times \left(\frac{\pi}{180}\right) \times R_e \times \cos\phi = 27.799 \times \cos\phi\text{ [km]}$$

- At $\phi = -10.0^\circ\text{S}$: $\cos(-10.0^\circ) = 0.9848 \implies \Delta x = 27.377\text{ km}$.
- At $\phi = -14.0^\circ\text{S}$: $\cos(-14.0^\circ) = 0.9703 \implies \Delta x = 26.973\text{ km}$.

### 5.2 2D Sobel Kernel & Physical Gradient Magnitude
The standard $3 \times 3$ Sobel convolution kernel over native 0.25° OISST pixels computes unscaled finite differences:

$$\delta_x = \text{Sobel}_X * \text{SST}, \quad \delta_y = \text{Sobel}_Y * \text{SST}$$

The physical spatial gradients in $^\circ\text{C/km}$ are obtained by dividing by kernel span ($8 \times \text{cell spacing}$):

$$\frac{\partial T}{\partial x} = \frac{\delta_x}{8 \times \Delta x(\phi)}, \quad \frac{\partial T}{\partial y} = \frac{\delta_y}{8 \times \Delta y}$$
$$|\nabla T| = \sqrt{\left(\frac{\partial T}{\partial x}\right)^2 + \left(\frac{\partial T}{\partial y}\right)^2} \quad [^\circ\text{C/km}]$$

This ensures front strength metrics are physically invariant across latitudes.

---

## 6. BATHYMETRY & TOPOGRAPHY SEMANTICS

ETOPO1 provides digital elevation relative to the sea surface datum:

$$\text{bedrock} = \begin{cases} < 0 & \text{Submerged Marine Topography (Elevation in meters)} \\ \ge 0 & \text{Terrestrial Topography (Elevation in meters)} \end{cases}$$

To eliminate physical ambiguity in oceanographic aggregations:
1. Marine Elevation: Assigned as `elevation_m` (strictly non-positive).
2. Derived Physical Ocean Depth:
   $$\text{depth\_m} = -\text{elevation\_m} \quad (\text{for elevation\_m} \le 0)$$
3. Land Mask: All cells with $\text{elevation\_m} > 0$ are masked from marine oceanographic analysis.

