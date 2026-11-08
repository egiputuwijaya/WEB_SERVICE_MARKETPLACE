# RESTful API Marketplace — Herbapedia (PT Herbal Sejati Indonesia)
## Tugas Soal 12: Pengembangan Web Service / REST API Marketplace

Repositori ini berisi implementasi layanan Web Service / REST API mandiri yang dirancang menggunakan **PHP Native** dan **MySQLi** untuk mengintegrasikan data transaksi dan katalog produk marketplace **Herbapedia** ke berbagai platform client eksternal (Aplikasi Mobile Android/iOS, Sistem Apotek POS, dan Integrator Logistik).

---

### 🚀 Daftar Endpoint REST API:

| No | Method | Endpoint URI | Deskripsi |
| :---: | :---: | :--- | :--- |
| 1 | `GET` | `/api/produk.php` | Menampilkan seluruh katalog produk herbal aktif (mendukung search & filter) |
| 2 | `GET` | `/api/produk.php?id={id}` | Menampilkan detail 1 produk herbal spesifik |
| 3 | `GET` | `/api/kategori.php` | Menampilkan seluruh daftar kategori sediaan obat herbal |
| 4 | `POST` | `/api/pesanan.php` | Membuat pesanan baru dan memotong stok sediaan secara atomik (ACID) |

---

### 📂 Struktur Direktori Proyek:
```text
WEB SERVICE MARKETPLACE/
├── api/
│   ├── produk.php          # Endpoint Produk (List & Detail)
│   ├── kategori.php        # Endpoint Kategori Herbal
│   └── pesanan.php         # Endpoint Pembuatan Pesanan Baru (POST)
├── config/
│   ├── database.php        # Koneksi Database MySQLi Prepared Statements
│   └── cors.php            # Middleware Header CORS & JSON
├── helpers/
│   └── response.php        # Helper Format Respon JSON Standar & HTTP Codes
├── database/
│   └── marketplace_db.sql  # Berkas SQL Skema & Data Pengujian
├── docs/
│   ├── LAPORAN_SOAL_12_REST_API.md     # Dokumen Laporan Pengujian Resmi
│   └── postman_collection.json         # Koleksi Pengujian Postman Siap Import
├── .gitignore              # Standar Keamanan Git (Abaikan Kredensial)
└── README.md               # Dokumentasi Teknis Proyek
```

---

### 🛠️ Persyaratan Lingkungan & Cara Menjalankan:
1. Pastikan modul **Apache** dan **MySQL** aktif pada Laragon / XAMPP.
2. Buat database bernama `marketplace_db` di phpMyAdmin (atau gunakan database dari Soal 11).
3. Import berkas `database/marketplace_db.sql` jika belum tersedia.
4. Akses endpoint API melalui browser atau aplikasi Postman pada alamat:
   * `http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/produk.php`
   * Atau `http://localhost/WEB%20SERVICE%20MARKETPLACE/api/produk.php`

