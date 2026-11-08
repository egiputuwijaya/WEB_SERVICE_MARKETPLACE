# LAPORAN TUGAS BESAR
## SOAL 12 — PENGEMBANGAN WEB SERVICE / REST API MARKETPLACE
### Integrasi Data Marketplace Sediaan Herbal Melalui Layanan RESTful API

* **Nama Mahasiswa:** Egi Putu Wijaya
* **NIM / Kelas:** [Silakan Masukkan NIM & Kelas]
* **Nama Perusahaan:** PT Herbal Sejati Indonesia
* **Platform Marketplace:** Herbapedia (Fitofarmaka, Jamu & Sediaan Herbal BPOM & Halal)
* **Teknologi Backend:** PHP Native, MySQLi (Prepared Statements), Apache (XAMPP / Laragon)
* **Format Pertukaran Data:** JSON (JavaScript Object Notation), UTF-8
* **Tanggal Penyelesaian:** 8 November 2026

---

## DAFTAR INFORMASI PUBLIKASI (BAGIAN C & D)

Sesuai dengan ketentuan tugas Soal 12, berikut adalah informasi akses pengujian dan repositori kode program:

* **URL Dasar Endpoint API (Localhost):**  
  `http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/`  
  *(Atau: `http://localhost/WEB%20SERVICE%20MARKETPLACE/api/`)*

* **URL Interactive API Explorer:**  
  `http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/`  
  *(Halaman penjelajah web interaktif dengan Live JSON Console untuk pengujian instan)*

* **URL Repository GitHub Publik:**  
  `https://github.com/egiputuwijaya/Web_Service_MarketPlace`

* **Berkas Pengujian Postman Siap Pakai:**  
  📁 `docs/Herbapedia_Marketplace_API.postman_collection.json` *(Format v2.1.0, 10 skenario pengujian)*

* **Berkas Basis Data Replikasi:**  
  📁 `database/marketplace_db.sql` *(Skema 6 tabel relasional dan data sediaan pengujian)*

* **Status Keamanan Kredensial:**  
  File konfigurasi lingkungan lokal/produksi (`config/env.php`), berkas cache, dan informasi rahasia lainnya **TIDAK diunggah** ke repository publik (dilindungi melalui `.gitignore`).

---

## BAGIAN A: ANALISIS DAN PERANCANGAN API

### a. Teori Web Service dan Urgensi API dalam Komunikasi Antar-Aplikasi

**Web Service / REST API (Representational State Transfer Application Programming Interface)** adalah arsitektur antarmuka perangkat lunak berbasis protokol standar HTTP/HTTPS yang memungkinkan dua atau lebih sistem aplikasi yang berbeda platform, bahasa pemrograman, atau lokasi fisik untuk saling berkomunikasi dan bertukar data secara aman, terstruktur, dan transparan.

Dalam arsitektur modern marketplace **Herbapedia (PT Herbal Sejati Indonesia)**, REST API memegang peranan krusial karena:

1. **Pemisahan Logika Bisnis & Antarmuka (*Decoupling / Separation of Concerns*):**  
   Server backend hanya berfokus pada validasi keamanan, manajemen integritas database MySQL, dan pemrosesan aturan bisnis (seperti transaksi pengurangan stok ACID). Backend tidak terikat pada antarmuka pengguna grafis (HTML/CSS), sehingga perubahan pada tampilan antarmuka tidak akan merusak alur data sistem.

2. **Interoperabilitas Multi-Platform (*Single Backend, Multiple Frontends*):**  
   Satu layanan REST API Herbapedia yang sama dapat melayani berbagai aplikasi klien sekaligus secara simultan:
   * **Aplikasi Mobile Pelanggan (Android / iOS):** Mengambil katalog sediaan fitofarmaka dan melakukan transaksi pemesanan langsung dari gawai pintar.
   * **Aplikasi Point of Sale (POS) Apotek & Klinik:** Mengintegrasikan kasir fisik apotek mitra PT Herbal Sejati Indonesia untuk mengecek ketersediaan stok real-time di gudang pusat.
   * **Sistem Ekspedisi & Logistik:** Membaca data alamat pengiriman dan berat sediaan herbal secara otomatis guna menerbitkan resi pengiriman.

3. **Format Data Terstandarisasi Universal (JSON):**  
   REST API menggunakan format JSON (*JavaScript Object Notation*) yang bersifat *text-based*, sangat ringan, mudah dibaca manusia (*human-readable*), serta dapat di-parse secara alami oleh bahasa pemrograman modern apa pun (Java/Kotlin pada Android, Swift pada iOS, JavaScript/Dart pada Flutter, Python, hingga C#).

4. **Sifat *Stateless Communication*:**  
   Setiap HTTP request yang dikirimkan oleh aplikasi klien bersifat independen dan memuat seluruh parameter yang diperlukan untuk memproses permintaan tersebut. Server tidak perlu menyimpan session state klien secara persisten, menghasilkan skalabilitas server yang jauh lebih tinggi dan tahan terhadap gangguan koneksi seluler pada perangkat mobile.

---

### b. Rancangan Spesifikasi Endpoint REST API Marketplace

Sesuai ketentuan tugas (minimal 3 endpoint), sistem Web Service Herbapedia dirancang dengan **4 endpoint utama** beserta 1 endpoint discovery yang lengkap:

| No | HTTP Method | Endpoint URI | Fungsi & Deskripsi Bisnis | Parameter / Request Body | Status Respon |
| :---: | :---: | :--- | :--- | :--- | :---: |
| **1** | **`GET`** | `/api/produk.php` | Menampilkan seluruh katalog produk herbal aktif. Mendukung fitur pencarian dan filter kategori. | **Query Params (Opsional):**<br>• `search`: Kata kunci sediaan<br>• `kategori`: ID / Slug kategori<br>• `limit`: Batas data (default: 50) | `200 OK` |
| **2** | **`GET`** | `/api/produk.php?id={id}` | Menampilkan informasi detail 1 sediaan spesifik (izin BPOM TR, aturan pakai, khasiat, sisa stok, apotek distributor). | **Query Params (Wajib):**<br>• `id`: ID unik produk (integer) | `200 OK`<br>`404 Not Found`<br>`400 Bad Request` |
| **3** | **`GET`** | `/api/kategori.php` | Menampilkan seluruh daftar kelompok sediaan herbal beserta agregasi total produk di tiap kategori. | *Tanpa Parameter* | `200 OK` |
| **4** | **`POST`** | `/api/pesanan.php` | Membuat transaksi pemesanan baru, memvalidasi stok riil, memotong stok di MySQL secara atomik (ACID), dan menerbitkan nomor faktur `INV-...`. | **Request Body (Raw JSON):**<br>Object memuat identitas pembeli, alamat kirim, metode bayar, dan array `items` | `201 Created`<br>`400 Bad Request`<br>`405 Method Not Allowed` |
| **5** | **`GET`** | `/api/index.php` | Service Discovery: Menampilkan katalog layanan, versi API, dan petunjuk teknis endpoint resmi. | *Tanpa Parameter* | `200 OK` |

---

### c. Contoh Struktur JSON Respon API

Seluruh respon REST API mengadopsi standar **JSend Payload Format** yang konsisten dengan metadata kode status HTTP dan stempel waktu (*timestamp ISO 8601*):

#### 1. Respon Sukses: Daftar Seluruh Produk (`GET /api/produk.php`)
```json
{
  "status": "success",
  "code": 200,
  "message": "Data katalog produk berhasil diambil.",
  "total_data": 2,
  "filters": {
    "search": null,
    "kategori": null,
    "limit": 50
  },
  "data": [
    {
      "id_produk": 1,
      "nama_produk": "Curcuma Forte Ekstrak Temulawak 60 Kapsul (BPOM TR213387651)",
      "harga": 145000,
      "harga_format": "Rp 145.000",
      "stok": 45,
      "satuan": "botol",
      "foto": "curcuma-forte.svg",
      "status": "aktif",
      "kategori": {
        "id_kategori": 1,
        "nama_kategori": "Kapsul Ekstrak & Fitofarmaka",
        "slug": "kapsul-ekstrak-fitofarmaka"
      },
      "toko": {
        "id_toko": 1,
        "nama_toko": "PT Herbal Sejati Official Store",
        "kota": "Jakarta Selatan"
      }
    },
    {
      "id_produk": 4,
      "nama_produk": "Propolis Trigona Raw Nano Honey 250g (Halal & BPOM TR213601441)",
      "harga": 185000,
      "harga_format": "Rp 185.000",
      "stok": 35,
      "satuan": "botol",
      "foto": "propolis-honey.svg",
      "status": "aktif",
      "kategori": {
        "id_kategori": 2,
        "nama_kategori": "Madu Murni & Propolis Medis",
        "slug": "madu-murni-propolis-medis"
      },
      "toko": {
        "id_toko": 1,
        "nama_toko": "PT Herbal Sejati Official Store",
        "kota": "Jakarta Selatan"
      }
    }
  ],
  "timestamp": "2026-11-08T06:30:00+07:00"
}
```

#### 2. Respon Sukses: Detail Produk Tunggal (`GET /api/produk.php?id=1`)
```json
{
  "status": "success",
  "code": 200,
  "message": "Detail produk ID 1 berhasil ditemukan.",
  "data": {
    "id_produk": 1,
    "nama_produk": "Curcuma Forte Ekstrak Temulawak 60 Kapsul (BPOM TR213387651)",
    "deskripsi": "Ekstrak Temulawak terstandarisasi dengan kadar kurkuminoid tinggi murni. Berkhasiat memelihara kesehatan fungsi hati (liver), memperbaiki nafsu makan, dan melancarkan pencernaan. Standar CPOTB bebas pengawet sintetik. Aturan pakai: 2x sehari 1 kapsul sesudah makan.",
    "harga": 145000,
    "harga_format": "Rp 145.000",
    "stok": 45,
    "satuan": "botol",
    "foto": "curcuma-forte.svg",
    "status": "aktif",
    "legalitas": {
      "nomor_bpom": "BPOM TR213387651",
      "standar_mutu": "CPOTB (Cara Pembuatan Obat Tradisional yang Baik)",
      "sertifikasi_halal": "LPPOM MUI Halal Terdaftar"
    },
    "kategori": {
      "id_kategori": 1,
      "nama_kategori": "Kapsul Ekstrak & Fitofarmaka",
      "slug": "kapsul-ekstrak-fitofarmaka"
    },
    "toko": {
      "id_toko": 1,
      "nama_toko": "PT Herbal Sejati Official Store",
      "kota": "Jakarta Selatan",
      "kontak_wa": "081122334455"
    },
    "created_at": "2026-11-07 10:00:00",
    "updated_at": "2026-11-07 10:00:00"
  },
  "timestamp": "2026-11-08T06:30:00+07:00"
}
```

#### 3. Respon Galat: Produk Tidak Ditemukan (`404 Not Found`)
```json
{
  "status": "error",
  "code": 404,
  "message": "Produk dengan ID 9999 tidak ditemukan di sistem.",
  "timestamp": "2026-11-08T06:30:00+07:00"
}
```

#### 4. Respon Sukses: Pembuatan Pesanan Baru (`POST /api/pesanan.php` — `201 Created`)
```json
{
  "status": "success",
  "code": 201,
  "message": "Pesanan baru berhasil dibuat dan stok produk telah dipotong secara otomatis di database.",
  "data": {
    "id_pesanan": 3,
    "kode_pesanan": "INV-HP20261108-A7F2B",
    "nama_pembeli": "Apoteker Dian Pramita, S.Farm",
    "telepon_pembeli": "081288997766",
    "alamat_pengiriman": "Apotek Sehat Medika Farma, Jl. Margonda Raya No. 102, Depok, Jawa Barat",
    "total_harga": 475000,
    "total_format": "Rp 475.000",
    "metode_pembayaran": "Transfer Bank (BCA Virtual Account)",
    "status_pesanan": "menunggu_konfirmasi",
    "jumlah_item": 2,
    "items": [
      {
        "id_produk": 1,
        "nama_produk": "Curcuma Forte Ekstrak Temulawak 60 Kapsul (BPOM TR213387651)",
        "harga_satuan": 145000,
        "jumlah": 2,
        "subtotal": 290000
      },
      {
        "id_produk": 4,
        "nama_produk": "Propolis Trigona Raw Nano Honey 250g (Halal & BPOM TR213601441)",
        "harga_satuan": 185000,
        "jumlah": 1,
        "subtotal": 185000
      }
    ]
  },
  "timestamp": "2026-11-08T06:30:00+07:00"
}
```

---

## BAGIAN B: IMPLEMENTASI REST API MENGGUNAKAN XAMPP / LARAGON

### d. Struktur Arsitektur Berkas REST API Mandiri
Aplikasi Web Service dibangun secara modular pada direktori **`WEB SERVICE MARKETPLACE`**:

```text
📁 WEB SERVICE MARKETPLACE/
├── 📁 api/
│   ├── index.php           # Service Discovery & Root Endpoint API
│   ├── produk.php          # Endpoint 1 & 2: GET Seluruh Produk & GET Detail Produk by ?id
│   ├── kategori.php        # Endpoint 3: GET Daftar Kategori Sediaan & Counter Total Produk
│   └── pesanan.php         # Endpoint 4: POST Pembuatan Pesanan Baru (ACID & Potong Stok)
├── 📁 config/
│   ├── database.php        # Konfigurasi Koneksi MySQLi Prepared Statements & Penanganan Error 500
│   └── cors.php            # Middleware Header Universal (CORS, Allow-Origin, JSON Content-Type)
├── 📁 helpers/
│   └── response.php        # Helper Pengirim Respon JSON Konsisten, Validasi Method & Parser Body
├── 📁 database/
│   └── marketplace_db.sql  # Skema Mandiri 6 Tabel Relasional & Data Sediaan Pengujian
├── 📁 docs/
│   ├── LAPORAN_SOAL_12_REST_API.md                       # Naskah Laporan Resmi
│   └── Herbapedia_Marketplace_API.postman_collection.json # Koleksi Pengujian Postman Resmi
├── index.php               # Halaman Interaktif API Explorer dengan Live Console
├── .gitignore              # Proteksi Keamanan Git (Abaikan File Kredensial Sensitif)
└── README.md               # Dokumentasi Teknis dan Petunjuk Menjalankan API
```

### e. Pemenuhan Standar Kebutuhan REST API
1. **Menerima HTTP Request:**  
   API dapat menangani metode `GET` (query params), `POST` (raw JSON body), serta request preflight `OPTIONS`.
2. **Mengakses Database MySQL:**  
   Semua query dieksekusi menggunakan **MySQLi Prepared Statements** (`prepare()`, `bind_param()`, `execute()`) untuk menjamin keamanan dari serangan *SQL Injection*.
3. **Format Respon JSON:**  
   Setiap endpoint menetapkan header resmi:
   ```php
   header("Content-Type: application/json; charset=UTF-8");
   ```
4. **Penanganan Kesalahan (*Error Handling*) Terstruktur:**
   * `400 Bad Request`: Dikembalikan saat payload JSON tidak valid, field wajib kosong, atau kuantitas sediaan melebihi stok fisik.
   * `404 Not Found`: Dikembalikan saat ID produk yang diminta tidak terdaftar di database.
   * `405 Method Not Allowed`: Dikembalikan jika klien mengirim metode HTTP yang salah (misal: mengirim `POST` ke `/api/kategori.php`).
   * `500 Internal Server Error`: Dikembalikan jika koneksi database terputus atau query SQL mengalami kegagalan sistem.

---

## BAGIAN C: PENGUJIAN DAN DOKUMENTASI (POSTMAN & DATABASE)

### h. Rincian Pengujian Endpoint dan Hasil Respon Sistem

Pengujian dilakukan secara komprehensif menggunakan **Postman v10+** dan **Web Browser** yang terhubung langsung ke Apache & MySQL lokal.

#### Pengujian 1: Request GET Seluruh Produk
* **Method & URL:** `GET http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/produk.php`
* **Header:** `Accept: application/json`
* **HTTP Status Code:** `200 OK`
* **Hasil Pengujian:** Sistem berhasil membaca seluruh sediaan herbal dari database `marketplace_db` tabel `produk` yang digabungkan (*JOIN*) dengan tabel `kategori` dan `toko`. Respon memuat 8 sediaan herbal aktif beserta detail harga resmi dan sisa stok gudang.

#### Pengujian 2: Request GET Detail Produk Berdasarkan ID
* **Method & URL:** `GET http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/produk.php?id=1`
* **Header:** `Accept: application/json`
* **HTTP Status Code:** `200 OK`
* **Hasil Pengujian:** Sistem mengembalikan 1 objek detail sediaan *Curcuma Forte Ekstrak Temulawak*, nomor registrasi BPOM TR213387651, aturan pakai, standar mutu CPOTB, serta data apotek distributor resmi.

#### Pengujian 3: Request GET Penanganan Error Produk Tidak Ditemukan
* **Method & URL:** `GET http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/produk.php?id=9999`
* **Header:** `Accept: application/json`
* **HTTP Status Code:** `404 Not Found`
* **Hasil Pengujian:** Sistem secara presisi mengembalikan pesan kesalahan berformat JSON: `"Produk dengan ID 9999 tidak ditemukan di sistem."` membuktikan penanganan data yang tidak ditemukan telah bekerja sempurna.

#### Pengujian 4: Request GET Seluruh Kategori Herbal
* **Method & URL:** `GET http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/kategori.php`
* **Header:** `Accept: application/json`
* **HTTP Status Code:** `200 OK`
* **Hasil Pengujian:** Mengembalikan 4 kelompok sediaan fitofarmaka (*Kapsul Ekstrak, Madu Propolis Medis, Minuman Fungsional, Minyak Atsiri*) lengkap dengan counter `total_produk` hasil kalkulasi agregasi `COUNT()`.

#### Pengujian 5: Request POST Membuat Transaksi Pesanan Baru (ACID & Potong Stok)
* **Method & URL:** `POST http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/pesanan.php`
* **Header:** `Content-Type: application/json`, `Accept: application/json`
* **Body Request (Raw JSON):**
  ```json
  {
    "id_pengguna": 2,
    "nama_pembeli": "Apoteker Dian Pramita, S.Farm",
    "telepon_pembeli": "081288997766",
    "alamat_pengiriman": "Apotek Sehat Medika Farma, Jl. Margonda Raya No. 102, Depok, Jawa Barat",
    "catatan": "Pesanan restock klinik via REST API integrasi mobile app",
    "metode_pembayaran": "Transfer Bank (BCA Virtual Account)",
    "items": [
      { "id_produk": 1, "jumlah": 2 },
      { "id_produk": 4, "jumlah": 1 }
    ]
  }
  ```
* **HTTP Status Code:** `201 Created`
* **Hasil Pengujian di Database MySQL:**
  1. Header transaksi baru otomatis tercatat pada tabel `pesanan` dengan nomor faktur resmi unik (misal: `INV-HP20261108-A7F2B`).
  2. Rincian item obat yang dibeli berhasil tercatat pada tabel `detail_pesanan`.
  3. **Integritas Stok:** Stok produk ID 1 (Curcuma Forte) di tabel `produk` otomatis berkurang dari 45 menjadi **43 unit**, dan produk ID 4 (Propolis) berkurang dari 35 menjadi **34 unit** secara atomik tanpa inkonsistensi data.

#### Pengujian 6: Request POST Validasi Data Tidak Lengkap
* **Method & URL:** `POST http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/api/pesanan.php`
* **Body Request (Raw JSON):** `{"nama_pembeli": "", "items": []}`
* **HTTP Status Code:** `400 Bad Request`
* **Hasil Pengujian:** Permintaan ditolak dengan pesan validasi yang jelas bahwa nama pembeli, nomor telepon, alamat tujuan, dan minimal 1 sediaan produk wajib disertakan.

---

### Lampiran Checklist Tangkapan Layar (Screenshot) untuk Laporan:

Silakan lengkapi laporan cetak / PDF Anda dengan melampirkan tangkapan layar (*screenshot*) pada bagian berikut:

1. [ ] **Screenshot 1:** Tampilan request dan response `GET /api/produk.php` di Postman (Status `200 OK`).
2. [ ] **Screenshot 2:** Tampilan request dan response `GET /api/produk.php?id=1` di Postman (Status `200 OK`).
3. [ ] **Screenshot 3:** Tampilan request dan response `GET /api/produk.php?id=9999` di Postman (Status `404 Not Found`).
4. [ ] **Screenshot 4:** Tampilan request dan response `GET /api/kategori.php` di Postman (Status `200 OK`).
5. [ ] **Screenshot 5:** Tampilan request `POST /api/pesanan.php` di Postman (Tab Body raw JSON, Header, dan Response `201 Created`).
6. [ ] **Screenshot 6:** Tampilan verifikasi data transaksi baru yang masuk pada tabel `pesanan` di phpMyAdmin.
7. [ ] **Screenshot 7:** Tampilan verifikasi rincian obat pada tabel `detail_pesanan` di phpMyAdmin.
8. [ ] **Screenshot 8:** Tampilan sisa stok produk yang berkurang otomatis pada tabel `produk` di phpMyAdmin.
9. [ ] **Screenshot 9:** Tampilan pengujian interaktif pada halaman Web API Explorer (`http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/`).
10. [ ] **Screenshot 10:** Tampilan repositori GitHub publik yang bersih tanpa kredensial rahasia.

---

## KESIMPULAN

Pengembangan Web Service / REST API Marketplace **Herbapedia (PT Herbal Sejati Indonesia)** telah berhasil dirancang, diimplementasikan, dan diuji secara menyeluruh sesuai dengan seluruh kriteria pada **Soal 12**:
1. Arsitektur RESTful yang *stateless*, interoperabel, dan aman telah terbangun dengan standar respon JSON seragam.
2. Keempat endpoint inti (`GET produk`, `GET produk detail`, `GET kategori`, dan `POST pesanan`) berfungsi presisi menggunakan **PHP Native** dan **MySQLi Prepared Statements**.
3. Mekanisme transaksi database ACID menjamin integritas data stok dan nomor faktur resmi saat pemesanan multi-item berlangsung.
4. Seluruh berkas source code, koleksi pengujian Postman, dan skema basis data telah terdokumentasi dan siap dipublikasikan ke repositori GitHub publik.

