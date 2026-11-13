# Desain Spesifikasi: Penataan Navbar, Hero Fit-to-Screen, dan Background Foto Sinematik Samudera

## 1. Latar Belakang & Masalah
Berdasarkan screenshot evaluasi tampilan pengguna pada resolusi laptop standar:
1. **Potongan Layar Bawah (Cut-off):** Hero section memiliki padding vertikal dan margin yang terlalu besar (`pt-20 pb-24`, margin `mb-6/8/12`), sehingga 3 kartu metrik di bagian bawah terpotong horizontal tepat di angka statistik saat pertama kali dimuat.
2. **Ketiadaan Background Foto Maritim:** Tampilan hero saat ini masih menggunakan gradien polos dan titik-titik radial sintetis tanpa foto atmosferik kapal penangkap ikan di laut lepas.
3. **Komposisi & Isian Navbar:** Menu navbar perlu dirapikan label dan tujuannya, serta menu "Tentang SEACAST" diposisikan di paling kanan tepat berdampingan dengan tombol Login.

---

## 2. Solusi Desain yang Disepakati

### A. Komposisi & Isian Navbar Baru
Navigasi publik (`components/public/navbar.blade.php`) disusun ulang dengan urutan:
1. **Beranda** (`/` - `route('home')`)
2. **Sains & Model** (`/science` - `route('science')`)
3. **Data Oseanografi** (`/ocean-data` - `route('ocean-data')`)
4. **Dokumentasi** (`/documentation` - `route('documentation')`)
5. **Tentang SEACAST** (`/about` - `route('about')`) — *Ditempatkan di ujung kanan dekat tombol Login*
6. **Tombol Login** (`/login` - `route('login')` / jika sudah login: `Dashboard`)
7. **Mobile Drawer (Hamburger):** Ditambahkan tombol toggle dan menu drawer responsif Alpine.js agar pengguna ponsel/tablet dapat membuka menu yang sama tanpa terpotong.

### B. Layout Hero "Fit-to-Screen" (Anti Terpotong)
- Menggunakan `min-h-[calc(100vh-4.25rem)] flex flex-col justify-between py-6 sm:py-8 lg:py-8`.
- Rasio spasi vertikal dikompresi secara seimbang:
  - Header navbar: tinggi standar ramping `h-16` / `min-h-[4rem]`.
  - Badge pill: `mb-2.5 sm:mb-3`
  - Headline: `text-2xl sm:text-4xl lg:text-[2.65rem] leading-tight mb-3 sm:mb-4`
  - Subtitle: `text-xs sm:text-sm lg:text-[15px] leading-relaxed max-w-2xl mx-auto mb-5 sm:mb-6`
  - Dual CTA: `mb-5 sm:mb-6`
  - 3 Kartu Metrik: `p-3 sm:p-3.5 rounded-2xl bg-[#091F30]/85 backdrop-blur-md`
- **Hasil:** Seluruh blok Hero—dari logo, headline, subtitle, tombol aksi, hingga 3 kartu metrik—tampil utuh dan pas 100% di layar pertama (first fold) tanpa terpotong.

### C. Background Foto Sinematik Kapal & Samudera Lepas
- Berkas citra maritim: `public/images/hero-marine.jpg` (resolusi tinggi kapal navigasi pelagis di samudera biru).
- Lapisan peredup (*atmospheric overlay*):
  - `bg-cover bg-center`
  - Lapisan gradien gelap: `linear-gradient(to bottom, rgba(7, 27, 43, 0.90), rgba(7, 27, 43, 0.85), rgba(7, 27, 43, 0.95))`
  - Aksen ambient glow cyan di tengah (`bg-cyan-500/15 blur-3xl`)
  - Menghasilkan kontras tajam untuk teks putih dan aksen cyan, sekaligus menghadirkan visual nyata kapal pelagis samudera.

---

## 3. Rencana Berkas yang Diubah
1. `seacast-prigi-laravel/resources/views/components/public/navbar.blade.php`:
   - Tata ulang urutan 5 link + login button.
   - Tambahkan mobile hamburger toggle & drawer Alpine.js.
2. `seacast-prigi-laravel/resources/views/public/home.blade.php`:
   - Ganti wrapper hero dengan background image `hero-marine.jpg` dan gradient overlays.
   - Set ukuran `min-h-[calc(100vh-4rem)]` dan proporsi vertikal fit-to-screen.
3. `seacast-prigi-laravel/tests/Feature/Wpp573GeneralizationTest.php`:
   - Perbarui assertion untuk memverifikasi teks menu baru: `Sains & Model`, `Data Oseanografi`, `Dokumentasi`, `Tentang SEACAST`.

