# CMLABS Website Crawler

Aplikasi Laravel untuk melakukan web crawling/scraping dengan kemampuan menangani website SPA, SSR, dan PWA. Dilengkapi antarmuka web untuk crawl, simpan, lihat, unduh, dan hapus hasil crawling.

---

## Daftar Isi

- [Requirements](#requirements)
- [Instalasi](#instalasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Penggunaan via UI](#penggunaan-via-ui)
- [Penggunaan via API](#penggunaan-via-api)
- [Penggunaan via CLI](#penggunaan-via-cli)
- [Struktur Direktori](#struktur-direktori)
- [Troubleshooting](#troubleshooting)

---

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ & npm
- Database (SQLite / MySQL / PostgreSQL)
- _(Opsional)_ Google Chrome — untuk rendering website berbasis JavaScript

---

## Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd cmlabs-backend-crawler-freelance-test
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Konfigurasi Environment

```bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan konfigurasi database:

```env
# Contoh menggunakan SQLite (paling mudah untuk development)
DB_CONNECTION=sqlite

# Contoh menggunakan MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=crawler
# DB_USERNAME=root
# DB_PASSWORD=
```

Jika menggunakan SQLite, buat file database-nya terlebih dahulu:

```bash
touch database/database.sqlite
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Jalankan Migrasi dan Seeder

```bash
php artisan migrate --seed
```

### 7. Buat Symlink Storage

```bash
php artisan storage:link
```

---

## Menjalankan Aplikasi

### Development (dengan Vite)

Jalankan dua proses secara bersamaan di terminal yang berbeda:

```bash
# Terminal 1 — Laravel development server
php artisan serve

# Terminal 2 — Vite asset bundler
npm run dev
```

Atau gunakan satu perintah jika tersedia `concurrently`:

```bash
npm run start
```

Aplikasi dapat diakses di: **http://localhost:8000**

### Production Build

```bash
npm run build
php artisan serve
```

---

## Penggunaan via UI

Antarmuka web tersedia di **http://localhost:8000** (atau `/crawler`).

### Langkah 1 — Buka Halaman Crawler

Akses URL aplikasi di browser. Halaman utama menampilkan form crawling dan daftar file yang sudah tersimpan.

### Langkah 2 — Masukkan URL Target

Ketik URL website yang ingin di-crawl pada kolom **Website URL**, contoh:

```
https://example.com
```

URL harus diawali dengan `https://` atau `http://`.

### Langkah 3 — Pilih Mode

| Mode             | Keterangan                                                                                    |
| ---------------- | --------------------------------------------------------------------------------------------- |
| **Crawl only**   | Mengambil HTML dan menampilkan info (ukuran, judul halaman, jumlah link) tanpa menyimpan file |
| **Crawl & Save** | Mengambil HTML dan menyimpannya sebagai file `.html` di server                                |

### Langkah 4 — (Opsional) Isi Nama File

Jika memilih mode **Crawl & Save**, kolom **Filename** akan muncul. Biarkan kosong untuk nama otomatis berdasarkan domain dan timestamp, atau isi manual, contoh:

```
hasil-crawl.html
```

### Langkah 5 — Klik Tombol Crawl

Klik tombol **Crawl**. Proses berjalan di background — tombol akan menampilkan loading spinner selama crawling berlangsung.

### Langkah 6 — Lihat Hasil

Setelah selesai, panel hasil muncul menampilkan:

- **Page Title** — judul halaman yang di-crawl
- **URL** — URL target
- **HTML Size** — ukuran HTML yang berhasil diambil
- **Links Found** — jumlah tautan yang ditemukan di halaman
- **Saved As** — nama file (hanya untuk mode Crawl & Save)

Untuk mode **Crawl & Save**, tersedia tombol **View saved file** (membuka hasil di tab baru) dan **Download**.

### Langkah 7 — Kelola File Tersimpan

Di bagian bawah halaman terdapat daftar **Saved Files**. Setiap file memiliki tiga aksi:

| Tombol       | Fungsi                                                                       |
| ------------ | ---------------------------------------------------------------------------- |
| **View**     | Membuka file HTML di tab baru — CSS dan gambar tetap dimuat dari server asli |
| **Download** | Mengunduh file HTML ke komputer                                              |
| **Hapus**    | Menghapus file dari server (muncul konfirmasi sebelum dihapus)               |

Klik tombol **Refresh** untuk memuat ulang daftar file.

---

## Penggunaan via API

Base URL: `http://localhost:8000/api/crawler`

### POST `/crawl`

Crawl website tanpa menyimpan file.

**Request:**

```json
{
    "url": "https://example.com"
}
```

**Response:**

```json
{
    "success": true,
    "url": "https://example.com",
    "message": "Website crawled successfully",
    "html_length": 123456,
    "page_title": "Example Domain",
    "links_count": 42
}
```

---

### POST `/crawl-and-save`

Crawl website dan simpan hasilnya sebagai file HTML.

**Request:**

```json
{
    "url": "https://example.com",
    "filename": "example.html"
}
```

Field `filename` bersifat opsional — jika tidak diisi, nama file dibuat otomatis.

**Response:**

```json
{
    "success": true,
    "url": "https://example.com",
    "message": "Website crawled and saved successfully",
    "html_length": 123456,
    "page_title": "Example Domain",
    "links_count": 42,
    "file_name": "example.html",
    "file_path": "/path/to/storage/crawled-html/example.html"
}
```

---

### GET `/files`

Menampilkan daftar semua file HTML yang sudah tersimpan.

**Response:**

```json
{
    "success": true,
    "count": 2,
    "files": [
        {
            "name": "example_com_2026-04-27_10-00-00.html",
            "size": 123456,
            "size_mb": 0.12,
            "created_at": "2026-04-27 10:00:00",
            "url": "http://localhost:8000/api/crawler/download/example_com_2026-04-27_10-00-00.html"
        }
    ]
}
```

---

### GET `/view/{filename}`

Menampilkan file HTML langsung di browser.

```
GET /api/crawler/view/example_com_2026-04-27_10-00-00.html
```

---

### GET `/download/{filename}`

Mengunduh file HTML.

```
GET /api/crawler/download/example_com_2026-04-27_10-00-00.html
```

---

### DELETE `/files/{filename}`

Menghapus file HTML dari server.

```
DELETE /api/crawler/files/example_com_2026-04-27_10-00-00.html
```

**Response:**

```json
{
    "success": true,
    "message": "File 'example_com_2026-04-27_10-00-00.html' deleted"
}
```

---

## Penggunaan via CLI

### Artisan Command

```bash
# Crawl dan simpan
php artisan crawler:crawl "https://example.com"

# Crawl dengan nama file custom
php artisan crawler:crawl "https://example.com" --filename="hasil.html"

# Crawl beberapa URL sekaligus
php artisan crawler:crawl "https://example.com" --urls="https://example2.com" --urls="https://example3.com"

# Crawl tanpa menyimpan file
php artisan crawler:crawl "https://example.com" --no-save
```

### Standalone Script

Script ini independen dan tidak memerlukan Laravel sepenuhnya:

```bash
php crawler.php "https://example.com" "output.html"
```

---

## Struktur Direktori

```
├── app/
│   ├── Console/Commands/
│   │   └── CrawlWebsiteCommand.php     # Artisan CLI command
│   ├── Http/Controllers/
│   │   └── CrawlerController.php       # Controller untuk semua endpoint
│   └── Services/
│       └── WebCrawlerService.php       # Logic crawling, deteksi SPA, pretty-print HTML
├── resources/views/
│   └── crawler.blade.php               # Halaman UI crawler
├── routes/
│   ├── api.php                         # Endpoint REST API
│   └── web.php                         # Route halaman web
├── storage/app/crawled-html/           # File HTML hasil crawling
├── crawler.php                         # Standalone crawler script
└── .env                                # Konfigurasi environment
```

---

## Troubleshooting

### Halaman tidak bisa diakses

Pastikan server sudah berjalan:

```bash
php artisan serve
```

### Error 500 saat crawl

Periksa log Laravel:

```bash
tail -f storage/logs/laravel.log
```

Pastikan storage dapat ditulis:

```bash
chmod -R 775 storage bootstrap/cache
```

### HTML tidak lengkap untuk website berbasis JavaScript (SPA)

Website seperti React/Vue/Angular memerlukan headless Chrome agar JavaScript bisa dieksekusi. Install Chrome, lalu crawl otomatis menggunakan rendering mode.

Pada Windows, Chrome biasanya terdeteksi di:

```
C:\Program Files\Google\Chrome\Application\chrome.exe
```

### Timeout saat crawling

Beberapa website lambat merespons. Naikkan timeout di `WebCrawlerService.php`:

```php
$this->client = new Client([
    'timeout' => 60, // naikkan dari 30 ke 60 detik
    ...
]);
```
