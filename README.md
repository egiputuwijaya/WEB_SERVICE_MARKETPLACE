# RESTful API Marketplace — Herbapedia (PT Herbal Sejati Indonesia)
## Tugas Soal 12: Pengembangan Web Service / REST API Marketplace

Repositori ini berisi implementasi resmi layanan Web Service / REST API mandiri yang dibangun menggunakan **PHP Native** dan **MySQLi Prepared Statements** berdasarkan database marketplace pada Soal 11. Layanan API ini memungkinkan data katalog sediaan obat herbal, izin edar BPOM, kategori, serta transaksi pemesanan dapat diakses dan diintegrasikan secara aman oleh aplikasi pihak ketiga (Aplikasi Mobile Android/iOS, Apotek Rekanan, dan Sistem Logistik).

---

### ⚠️ PANDUAN PENTING UNTUK REPLIKASI DATABASE

> **PENTING DIBACA SEBELUM MENJALANKAN PENGUJIAN:**  
> Sesuai ketentuan **Soal 12 Poin j** (*"Sertakan file SQL database yang digunakan untuk pengujian agar aplikasi dapat direplikasi dan diperiksa oleh dosen"*), seluruh struktur tabel dan data awal pengujian telah disertakan secara lengkap di dalam folder:
> 
> 📁 **`database/marketplace_db.sql`**

#### Langkah Cepat Menjalankan Database:
1. Pastikan modul **Apache** dan **MySQL** aktif pada **XAMPP / Laragon**.
2. Buka browser ke **phpMyAdmin**:  
   👉 `http://localhost/phpmyadmin/`
3. Buat database baru bernama: **`marketplace_db`**
4. Klik database `marketplace_db` tersebut, lalu buka tab **Import**.
5. Pilih berkas **`database/marketplace_db.sql`** dari repositori ini, lalu klik tombol **Import / Kirim**.
6. **Selesai!** Seluruh 6 tabel relasional (`kategori`, `produk`, `pengguna`, `toko`, `pesanan`, `pesanan_detail`) beserta sampel produk herbal berizin BPOM TR & Halal MUI langsung terisi dan siap diuji.

---

### 🚀 Daftar Endpoint REST API Resmi:

Seluruh endpoint menghasilkan respon dalam format standar **JSON** (`Content-Type: application/json; charset=UTF-8`) dan menangani status HTTP secara ketat (200, 201, 400, 404, 405, 500):

| No | HTTP Method | Endpoint URI | Fungsi & Deskripsi | HTTP Status Sukses |
| :---: | :---: | :--- | :--- | :---: |
| 1 | `GET` | `/api/produk.php` | Menampilkan seluruh katalog produk herbal aktif (mendukung `?search=`, `?kategori=`, `?limit=`) | `200 OK` |
| 2 | `GET` | `/api/produk.php?id={id}` | Menampilkan detail 1 produk spesifik (Izin BPOM TR, Halal, aturan pakai, stok, info toko) | `200 OK` |
| 3 | `GET` | `/api/kategori.php` | Menampilkan seluruh daftar kategori sediaan obat herbal beserta total produk | `200 OK` |
| 4 | `POST` | `/api/pesanan.php` | Membuat pesanan baru via raw JSON, kunci stok baris atomik (`FOR UPDATE`), dan kurangi stok (ACID) | `201 Created` |

#### Penanganan Kesalahan (Error Handling):
* **Data Tidak Ditemukan (`404 Not Found`):**  
  Jika mengakses produk dengan ID yang tidak ada (contoh: `GET /api/produk.php?id=9999`), API mengembalikan status `404 Not Found` dan JSON ramah klien:
  ```json
  {
    "status": "error",
    "code": 404,
    "message": "Produk dengan ID 9999 tidak ditemukan",
    "data": null,
    "timestamp": "2026-11-08T..."
  }
  ```
* **Validasi Input Gagal (`400 Bad Request`):**  
  Jika request `POST /api/pesanan.php` dikirimkan dengan format salah atau field kosong.
* **Metode Tidak Diizinkan (`405 Method Not Allowed`):**  
  Jika mengirimkan `POST` ke endpoint yang hanya mendukung `GET`.

---

### 🧪 Cara Pengujian API (2 Opsi Mudah):

#### Opsi 1: Menggunakan Koleksi Postman Resmi (Siap Pakai)
Berkas koleksi pengujian Postman telah disertakan di folder `docs/`:
* 📁 **`docs/Herbapedia_Marketplace_API.postman_collection.json`**
* Cukup buka **Postman** -> klik **Import** -> pilih file tersebut.
* Koleksi ini sudah mencakup 10 skenario pengujian lengkap (GET All, Filter Kategori, Search, Detail ID 1, Uji 404 Not Found, POST Buat Pesanan 201, POST Validasi Gagal 400, dan Uji Method 405).

#### Opsi 2: Menggunakan Web API Explorer (Browser)
Aplikasi ini dilengkapi dengan antarmuka web interaktif berbasis Bootstrap yang dapat dibuka langsung di browser:
* `http://localhost/MARKETPLACE/WEB%20SERVICE%20MARKETPLACE/`  
  *(Atau: `http://localhost/WEB%20SERVICE%20MARKETPLACE/`)*
* Dosen/penguji dapat mengklik tombol **"Uji Endpoint"** dan melihat respon nyata JSON di **Live Console**.

---

### 📂 Struktur Direktori Proyek:

```text
WEB SERVICE MARKETPLACE/
├── api/
│   ├── index.php             # Root Service Discovery Endpoint
│   ├── kategori.php          # Endpoint Kategori Herbal (GET)
│   ├── pesanan.php           # Endpoint Transaksi Pesanan ACID (POST & GET)
│   └── produk.php            # Endpoint Katalog & Detail Produk (GET)
├── config/
│   ├── cors.php              # Header CORS Universal & JSON Response
│   └── database.php          # Koneksi MySQLi (Fallback localhost:3306 root)
├── database/
│   └── marketplace_db.sql    # ⭐️ SKEMA DATABASE & DATA UJI RESMI (Poin j)
├── docs/
│   └── Herbapedia_Marketplace_API.postman_collection.json # Koleksi Postman Siap Pakai
├── helpers/
│   └── response.php          # Standardisasi Respon JSON, Method Guard & Parser
├── .gitignore                # Standar Keamanan Git (Abaikan password & env rahasia)
├── index.php                 # Web API Explorer Interaktif dengan Live Console
└── README.md                 # Dokumentasi Teknis Proyek untuk Dosen & Penguji
```

---

### 🛡️ Standar Keamanan & Ketentuan Pengerjaan:
1. **PHP Native & MySQLi:** Menggunakan Prepared Statements (`bind_param`) untuk mencegah SQL Injection.
2. **Kerahasiaan Kredensial:** Password database dan informasi sensitif diabaikan melalui `.gitignore` (memenuhi Ketentuan D Soal 12).
3. **Database Replikasi:** Menggunakan basis data mandiri yang kompatibel dengan Soal 11 untuk kelancaran pemeriksaan dosen.
