# Panduan Resmi Deployment: Hostinger VPS & CloudPanel
## SEACAST WPPNRI 573 (Sistem Prediksi Geospasial & Intelijen Perikanan Pelagis)

Dokumen ini mencatat seluruh konfigurasi teknis, arsitektur server, dan langkah-langkah deployment terperinci untuk mendeploy sistem **SEACAST** di atas layanan **VPS Hostinger (Ubuntu Linux)** menggunakan panel kontrol **CloudPanel**.

---

### 1. Spesifikasi Lingkungan Server (Target Environment)

| Komponen | Spesifikasi / Konfigurasi | Keterangan |
| :--- | :--- | :--- |
| **Provider VPS** | Hostinger VPS (KVM) | Dedicated IP, Root SSH Access, NVMe Storage |
| **Sistem Operasi** | Ubuntu 22.04 LTS / 24.04 LTS | Standar Linux Server bereputasi tinggi |
| **Control Panel** | CloudPanel 2.x | High-performance NGINX + PHP-FPM Panel |
| **Runtime Web** | PHP 8.3-FPM + Composer 2.x | Framework Laravel 11 |
| **Web Server** | NGINX (Reverse Proxy & FastCGI) | Mengarahkan root ke `/public` |
| **Database Server** | PostgreSQL 16 / 17 + PostGIS 3.4 / 3.6 | Gudang data spasial kanonikal 8.129 sel |
| **Runtime Machine Learning** | Python 3.10 / 3.12 / 3.14 (Virtualenv) | scikit-learn, joblib, earthengine-api, psycopg2 |
| **Sertifikat Keamanan** | Let's Encrypt SSL (HTTPS Otomatis) | 1-Click SSL via CloudPanel |
| **Otomasi Jadwal** | CloudPanel Cron Job Service | Eksekusi harian otomatis 04:00 WIB |

---

### 2. Konfigurasi CloudPanel untuk Laravel

#### A. Pembuatan Situs di CloudPanel:
1. Masuk ke Dashboard CloudPanel (`https://IP_VPS:8443`).
2. Klik **Add Site** $\to$ Pilih **Create a PHP Site**.
3. Masukkan parameter:
   * **Domain Name:** `seacast.id` (atau subdomain yang digunakan).
   * **Site User:** `seacast` (user Linux terisolasi).
   * **PHP Version:** `PHP 8.3`.
   * **Application:** `Generic` (atau `Laravel`).
4. Setelah dibuat, buka **Site Settings** $\to$ pastikan **Root Directory** mengarah ke:
   ```
   /home/seacast/htdocs/seacast.id/seacast-prigi-laravel/public
   ```

#### B. Konfigurasi NGINX Vhost (CloudPanel):
Pastikan blok NGINX menangani routing Laravel dengan benar:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass php-fpm;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    fastcgi_param DOCUMENT_ROOT $realpath_root;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
    fastcgi_busy_buffers_size 256k;
}
```

---

### 3. Instalasi Database PostgreSQL 17 + PostGIS di VPS

Jalankan perintah berikut melalui terminal SSH (`ssh root@IP_VPS`):

```bash
# 1. Update repository dan install PostgreSQL + PostGIS
sudo apt update && sudo apt upgrade -y
sudo apt install -y postgresql postgresql-contrib postgis postgresql-16-postgis-3

# 2. Masuk ke PostgreSQL dan buat database serta user SEACAST
sudo -u postgres psql << 'EOF'
CREATE DATABASE seacast_prigi;
CREATE USER seacast_user WITH ENCRYPTED PASSWORD 'PASSWORD_AMAN_ANDA';
GRANT ALL PRIVILEGES ON DATABASE seacast_prigi TO seacast_user;

-- Hubungkan ke database dan aktifkan ekstensi geospasial PostGIS
\c seacast_prigi
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;

-- Berikan hak akses skema
GRANT ALL ON SCHEMA public TO seacast_user;
EOF
```

---

### 4. Setup Python Environment & Dependensi Machine Learning

SEACAST membutuhkan *isolated virtual environment* di server agar pustaka ML berjalan stabil:

```bash
# 1. Install pip dan python venv
sudo apt install -y python3-pip python3-venv

# 2. Berpindah ke user seacast
cd /home/seacast/htdocs/seacast.id/
python3 -m venv venv
source venv/bin/activate

# 3. Install pustaka ilmiah resmi (Versi Terkunci)
pip install --upgrade pip
pip install numpy pandas scikit-learn joblib earthengine-api psycopg2-binary requests
```

---

### 5. Setup Kredensial Google Earth Engine (GEE) di VPS

Kredensial GEE yang sudah diautentikasi di laptop dipindahkan ke VPS agar penarikan data satelit berjalan tanpa interaksi browser:

```bash
# Buat folder konfigurasi Earth Engine untuk user seacast
mkdir -p /home/seacast/.config/earthengine

# Salin isi file C:\Users\ThinkPad\.config\earthengine\credentials dari laptop
# dan letakkan di:
# /home/seacast/.config/earthengine/credentials

# Atur kepemilikan izin file
chown -R seacast:seacast /home/seacast/.config/earthengine
chmod 600 /home/seacast/.config/earthengine/credentials
```

---

### 6. Pengaturan Cron Job Otomatis di CloudPanel

Agar sistem berjalan **100% otomatis setiap hari pada pukul 04:00 WIB** tanpa laptop menyala:

1. Buka menu **Cron Jobs** di CloudPanel situs `seacast.id`.
2. Klik **Add Cron Job**.
3. Masukkan parameter:
   * **Schedule:** `Every Minute` (`* * * * *`)
   * **Command:**
     ```bash
     cd /home/seacast/htdocs/seacast.id/seacast-prigi-laravel && /usr/bin/php8.3 artisan schedule:run >> /dev/null 2>&1
     ```
4. Di dalam Laravel (`routes/console.php`), tugas harian sudah terdaftar otomatis:
   * **Pukul 04:00 WIB:** `php artisan seacast:sync-live` (Ekstraksi GEE terbaru, ingest live BMKG, kalkulasi model ML .joblib, dan update PostgreSQL).

---

### 7. File Konfigurasi Lingkungan Produksi (`.env.production`)

```ini
APP_NAME="SEACAST"
APP_ENV=production
APP_KEY=base64:179GfpE0mGyD5RBv/k3iMzJJ8tzRgEh30PrWTAbMToc=
APP_DEBUG=false
APP_URL=https://seacast.id

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database Spasial PostGIS Produksi
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=seacast_prigi
DB_USERNAME=seacast_user
DB_PASSWORD=PASSWORD_AMAN_ANDA

# Cache & Session
SESSION_DRIVER=database
CACHE_STORE=file

# Jalur Eksekusi Python Bridge di Linux
PYTHON_BINARY_PATH=/home/seacast/htdocs/seacast.id/venv/bin/python
EE_PROJECT_ID=seca-513505
```

---

### 8. Checklist Verifikasi Pasca-Deploy (Smoke Test)

Setelah deployment di CloudPanel selesai, lakukan verifikasi:
- [ ] Buka `https://seacast.id/` $\to$ Pastikan gembok hijau HTTPS aktif.
- [ ] Buka `https://seacast.id/dashboard/dpi-radar` $\to$ Peta Leaflet memuat 8.129 sel dan animasi arus mengalir.
- [ ] Cek widget cuaca di header $\to$ Menampilkan data live BMKG Prigi & Cilacap.
- [ ] Jalankan uji terminal manual:
  ```bash
  cd /home/seacast/htdocs/seacast.id/seacast-prigi-laravel
  php artisan seacast:sync-live
  ```
  *Pastikan output: `INFERENCE PIPELINE EXECUTED SUCCESSFULLY — ZERO ERRORS`*.
