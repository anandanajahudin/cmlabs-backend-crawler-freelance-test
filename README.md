# CMLABS Website Crawler API

Aplikasi Laravel untuk melakukan web scraping/crawling website dengan kemampuan handle SPA, SSR, dan PWA. Hasil crawling disimpan sebagai file HTML.

## Fitur

- Crawl website tipe SPA (Single Page Application)
- Crawl website tipe SSR (Server-Side Rendering)
- Crawl website tipe PWA (Progressive Web App)
- Menyimpan hasil crawling sebagai file HTML
- API REST untuk crawling
- Artisan command untuk CLI crawling
- List dan download file HTML yang sudah dicrawl

## Website yang Sudah Dicrawl

1. **https://cmlabs.co** - 601.8 KB
2. **https://sequence.day** - 151.2 KB

File hasil crawling tersimpan di: `storage/crawled-html/`

## Instalasi

### Requirements

- PHP 8.3+
- Composer
- Laravel 13+

### Setup

1. Clone repository

```bash
git clone <repository-url>
cd cmlabs-backend-crawler-freelance-test
```

2. Install dependencies

```bash
composer install
```

3. Copy environment file

```bash
cp .env.example .env
```

4. Generate app key

```bash
php artisan key:generate
```

## Penggunaan

### Method 1: CLI Command (Artisan)

Crawl single website:

```bash
php artisan crawler:crawl "https://example.com"
```

Crawl dengan filename custom:

```bash
php artisan crawler:crawl "https://example.com" --filename="custom_name.html"
```

Crawl multiple websites:

```bash
php artisan crawler:crawl "https://example1.com" --urls="https://example2.com" --urls="https://example3.com"
```

Crawl tanpa menyimpan file (hanya fetch content):

```bash
php artisan crawler:crawl "https://example.com" --no-save
```

### Method 2: API REST

#### Endpoint 1: Crawl Website

**POST** `/api/crawler/crawl`

Request:

```json
{
    "url": "https://example.com"
}
```

Response:

```json
{
    "success": true,
    "url": "https://example.com",
    "message": "Website crawled successfully",
    "html_length": 123456
}
```

#### Endpoint 2: Crawl dan Save

**POST** `/api/crawler/crawl-and-save`

Request:

```json
{
    "url": "https://example.com",
    "filename": "custom_name.html"
}
```

Response:

```json
{
    "success": true,
    "url": "https://example.com",
    "message": "Website crawled and saved successfully",
    "file_path": "/path/to/storage/crawled-html/custom_name.html",
    "file_name": "custom_name.html"
}
```

#### Endpoint 3: List Crawled Files

**GET** `/api/crawler/files`

Response:

```json
{
    "success": true,
    "files": [
        {
            "name": "cmlabs_co.html",
            "size": 601822,
            "size_mb": 0.57,
            "created_at": "2026-04-25 08:22:00",
            "url": "http://localhost/api/crawler/download/cmlabs_co.html"
        }
    ],
    "count": 1
}
```

#### Endpoint 4: Download File

**GET** `/api/crawler/download/{filename}`

Downloads the HTML file untuk disimpan ke komputer.

#### Endpoint 5: View File in Browser

**GET** `/api/crawler/view/{filename}`

Menampilkan file HTML langsung di browser.

### Method 3: Standalone Script

```bash
php crawler.php "https://example.com" "filename.html"
```

Script ini independen dan tidak memerlukan Laravel fully setup.

## Struktur Direktori

```
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── CrawlWebsiteCommand.php      # Artisan command
│   ├── Http/
│   │   └── Controllers/
│   │       └── CrawlerController.php         # API controller
│   ├── Services/
│   │   └── WebCrawlerService.php             # Crawler service
│   └── Models/
├── routes/
│   ├── api.php                               # API routes
│   ├── web.php                               # Web routes
│   └── crawler.php                           # Crawler routes
├── storage/
│   └── crawled-html/                         # Tempat menyimpan HTML hasil crawl
├── crawler.php                               # Standalone crawler script
├── composer.json
└── README.md
```

## Fitur Teknis

### Deteksi Website Type

Script otomatis mendeteksi tipe website:

- **SPA**: Mencari `#app`, `#root`, `#__next`, `react`, `vue`, `angular` dalam HTML
- **SSR**: Website yang sudah ter-render di server
- **PWA**: Website dengan manifest.json dan service worker

### Fallback Mechanism

Jika content tidak lengkap, script akan mencoba:

1. Curl dengan berbagai headers untuk simulate browser
2. Rendering dengan headless Chrome (jika tersedia)
3. Fallback ke basic curl request

## Error Handling

Script menghandle berbagai error:

- Connection timeout
- Invalid URL
- HTTP errors (4xx, 5xx)
- SSL certificate issues
- Empty response

## Performance

- Timeout per crawl: 30 detik
- User-Agent: Modern Firefox
- Compression: Gzip dan Deflate

## Limitasi & Catatan

1. Beberapa website mungkin memblokir crawling - gunakan User-Agent spoofing
2. JavaScript-heavy websites memerlukan headless browser (Chrome/Chromium) untuk rendering sempurna
3. File size bergantung pada kompleksitas website
4. Storage harus memiliki ruang disk yang cukup

## Troubleshooting

### Masalah: "Failed to open stream: No such file or directory"

- Pastikan `composer install` sudah dijalankan
- Pastikan vendor/autoload.php ada

### Masalah: Timeout saat crawling

- Beberapa website mungkin lambat, coba increase timeout
- Check internet connection
- Website mungkin memblokir crawler

### Masalah: HTML tidak lengkap untuk SPA

- Install headless Chrome/Chromium
- Atau gunakan external rendering service

## Development

### Testing API

Gunakan tools seperti:

- Postman
- cURL
- Thunder Client
- REST Client di VS Code

Contoh cURL:

```bash
curl -X POST http://localhost/api/crawler/crawl-and-save \
  -H "Content-Type: application/json" \
  -d '{"url":"https://example.com","filename":"test.html"}'
```

### Contributing

Untuk menambah fitur atau fix bugs:

1. Create feature branch
2. Make changes
3. Test thoroughly
4. Create pull request

## License

MIT License - Lihat LICENSE file untuk detail

## Author

Dibuat untuk CMLABS Crawler Freelance Test

---

**Last Updated**: 2026-04-25
