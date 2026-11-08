-- ============================================================
-- DATABASE SKEMA: HERBAPEDIA (PT HERBAL SEJATI INDONESIA)
-- RESTful Web Service / API Marketplace (Soal 12)
-- Sesuai Soal 12: Integrasi Data Marketplace melalui REST API
-- ============================================================

DROP TABLE IF EXISTS `detail_pesanan`;
DROP TABLE IF EXISTS `pesanan`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `toko`;
DROP TABLE IF EXISTS `pengguna`;

-- ------------------------------------------------------------
-- 1. Tabel: pengguna (Users - Distributor, Mitra Penjual, Pelanggan)
-- ------------------------------------------------------------
CREATE TABLE `pengguna` (
  `id_pengguna` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('penjual', 'pembeli') NOT NULL DEFAULT 'pembeli',
  `telepon` VARCHAR(20) NOT NULL,
  `alamat` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Tabel: toko (Official Store & Mitra Resmi PT Herbal Sejati)
-- ------------------------------------------------------------
CREATE TABLE `toko` (
  `id_toko` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pengguna` INT NOT NULL,
  `nama_toko` VARCHAR(100) NOT NULL,
  `deskripsi_toko` TEXT,
  `kota` VARCHAR(100) NOT NULL,
  `kontak_wa` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_toko_pengguna` FOREIGN KEY (`id_pengguna`) 
    REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Tabel: kategori (Kategori Produk Herbal & Kesehatan Alami)
-- ------------------------------------------------------------
CREATE TABLE `kategori` (
  `id_kategori` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `deskripsi` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Tabel: produk (Katalog Produk Herbal Resmi Terdaftar)
-- ------------------------------------------------------------
CREATE TABLE `produk` (
  `id_produk` INT AUTO_INCREMENT PRIMARY KEY,
  `id_toko` INT NOT NULL,
  `id_kategori` INT NOT NULL,
  `nama_produk` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `harga` DECIMAL(12,2) NOT NULL,
  `stok` INT NOT NULL DEFAULT 0,
  `satuan` VARCHAR(30) NOT NULL DEFAULT 'botol',
  `foto` VARCHAR(255) DEFAULT 'default-product.svg',
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_produk_toko` FOREIGN KEY (`id_toko`) 
    REFERENCES `toko` (`id_toko`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) 
    REFERENCES `kategori` (`id_kategori`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. Tabel: pesanan (Header Transaksi Pemesanan Produk)
-- ------------------------------------------------------------
CREATE TABLE `pesanan` (
  `id_pesanan` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_pesanan` VARCHAR(30) NOT NULL UNIQUE,
  `id_pengguna` INT NULL,
  `nama_pembeli` VARCHAR(100) NOT NULL,
  `telepon_pembeli` VARCHAR(20) NOT NULL,
  `alamat_pengiriman` TEXT NOT NULL,
  `catatan` TEXT,
  `total_harga` DECIMAL(12,2) NOT NULL,
  `metode_pembayaran` VARCHAR(50) NOT NULL DEFAULT 'Transfer Bank (Verifikasi Otomatis)',
  `status_pesanan` ENUM('menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu_konfirmasi',
  `tanggal_pesanan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pesanan_pengguna` FOREIGN KEY (`id_pengguna`) 
    REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. Tabel: detail_pesanan (Detail Rincian Item Obat Herbal Dipesan)
-- ------------------------------------------------------------
CREATE TABLE `detail_pesanan` (
  `id_detail` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pesanan` INT NOT NULL,
  `id_produk` INT NOT NULL,
  `nama_produk` VARCHAR(150) NOT NULL,
  `harga_satuan` DECIMAL(12,2) NOT NULL,
  `jumlah` INT NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) 
    REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) 
    REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DUMMY DATA LENGKAP: PT HERBAL SEJATI INDONESIA
-- ============================================================

-- Data Pengguna
INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `email`, `password`, `role`, `telepon`, `alamat`) VALUES
(1, 'Admin Official PT Herbal Sejati Indonesia', 'official@herbapedia.id', '$2y$10$eA0v0G4YmY9QGv7uK39P0OnYnF1XqE9o8o/2Z51kQ2V8Q0fD1eW.m', 'penjual', '081122334455', 'Gedung Herbal Sejati Tower Lt. 8, Jl. TB Simatupang Kav. 15, Jakarta Selatan'),
(2, 'Dr. Hendra Gunawan, Sp.Far', 'hendra.gunawan@medika.com', '$2y$10$eA0v0G4YmY9QGv7uK39P0OnYnF1XqE9o8o/2Z51kQ2V8Q0fD1eW.m', 'pembeli', '081298765432', 'Klinik Sehat Alami Medika, Jl. Raya Menteng No. 88, Jakarta Pusat');

-- Data Toko Official
INSERT INTO `toko` (`id_toko`, `id_pengguna`, `nama_toko`, `deskripsi_toko`, `kota`, `kontak_wa`) VALUES
(1, 1, 'PT Herbal Sejati Official Store', 'Produsen dan distributor resmi obat herbal fitofarmaka, ekstrak bahan alam berstandar CPOTB (Cara Pembuatan Obat Tradisional yang Baik), terdaftar BPOM dan Halal MUI.', 'Jakarta Selatan', '081122334455');

-- Data Kategori Herbal
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `slug`, `deskripsi`) VALUES
(1, 'Kapsul Ekstrak & Fitofarmaka', 'kapsul-ekstrak-fitofarmaka', 'Formula kapsul ekstrak herbal terstandarisasi untuk imunitas, diabetes, asam urat, dan vitalitas.'),
(2, 'Madu Murni & Propolis Medis', 'madu-murni-propolis-medis', 'Madu hutan liar murni, propolis trigona konsentrat tinggi, dan madu hitam pahit terapi.'),
(3, 'Minuman Fungsional & Teh Herbal', 'minuman-fungsional-teh-herbal', 'Minuman seduh kesehatan alami dari jahe merah organik, temulawak instan, dan herbal detox.'),
(4, 'Minyak Atsiri & Aromaterapi Terapi', 'minyak-atsiri-aromaterapi', 'Minyak esensial murni food-grade, habbatussauda softgel, dan minyak kayu putih suling murni.');

-- Data Produk Herbal Terstandarisasi
INSERT INTO `produk` (`id_produk`, `id_toko`, `id_kategori`, `nama_produk`, `deskripsi`, `harga`, `stok`, `satuan`, `foto`, `status`) VALUES
(1, 1, 1, 'Curcuma Forte Ekstrak Temulawak 60 Kapsul (BPOM TR213387651)', 'Ekstrak Temulawak terstandarisasi dengan kadar kurkuminoid tinggi murni. Berkhasiat memelihara kesehatan fungsi hati (liver), memperbaiki nafsu makan, dan melancarkan pencernaan. Standar CPOTB bebas pengawet sintetik. Aturan pakai: 2x sehari 1 kapsul sesudah makan.', 145000.00, 45, 'botol', 'curcuma-forte.svg', 'aktif'),
(2, 1, 1, 'Moringa Max Ekstrak Daun Kelor 500mg 50 Kapsul (BPOM TR223319081)', 'Ekstrak daun kelor organik murni dengan kandungan 46 antioksidan kuat dan 18 asam amino esensial. Sangat efektif menurunkan kadar kolesterol jahat, menjaga kadar gula darah stabil, dan meningkatkan daya tahan tubuh harian.', 120000.00, 30, 'botol', 'moringa-max.svg', 'aktif'),
(3, 1, 1, 'Andrografit Ekstrak Sambiloto Imunitas 60 Kapsul (BPOM TR203348121)', 'Mengandung ekstrak daun sambiloto (Andrographis paniculata) murni terstandar. Terbukti secara empiris dan uji farmakologi meredakan radang tenggorokan, flu, demam, serta sebagai imunomodulator penguat antibodi.', 135000.00, 25, 'botol', 'andrografit.svg', 'aktif'),
(4, 1, 2, 'Propolis Trigona Raw Nano Honey 250g (Halal & BPOM TR213601441)', 'Madu murni lebah tanpa sengat (Trigona / Klanceng) diperkaya bioflavonoid propolis konsentrat tinggi. Berfungsi sebagai antibiotik alami, menangkal infeksi bakteri, dan meregenerasi sel jaringan tubuh yang rusak.', 185000.00, 35, 'botol', 'propolis-honey.svg', 'aktif'),
(5, 1, 2, 'Madu Hitam Pahit Liar Plus Sambiloto & Pasak Bumi 500g', 'Kombinasi madu hutan alami murni dengan ekstrak herbal pahit pilihan. Sangat direkomendasikan untuk penderita diabetes militus, penstabil tekanan darah, dan membantu mengatasi gangguan asam lambung / maag kronis.', 160000.00, 20, 'botol', 'madu-hitam.svg', 'aktif'),
(6, 1, 3, 'Red Ginger Pure Ekstrak Jahe Merah Instan 250g (Kemasan Pouch)', 'Bubuk minuman sari jahe merah murni dipadukan dengan gula aren organik dan lada hitam. Memberikan sensasi hangat seketika, melegakan pernapasan, meredakan masuk angin, pegal linu, dan meningkatkan stamina tubuh.', 65000.00, 50, 'pouch', 'red-ginger.svg', 'aktif'),
(7, 1, 3, 'Organic Butterfly Pea Tea - Teh Bunga Telang 50g (Herbal Detox)', 'Kelopak bunga telang organik pilihan yang dikeringkan pada suhu rendah untuk menjaga kemurnian antosianin dan antioksidan. Efektif menjaga kesehatan mata, mencerahkan kulit alami, dan membantu relaksasi tidur pulas.', 45000.00, 40, 'kotak', 'butterfly-pea.svg', 'aktif'),
(8, 1, 4, 'Habbatussauda Extra Softgel Habbats Oil 100 Butir (BPOM TR203378901)', 'Minyak jintan hitam murni (Nigella Sativa Oil) yang diekstrak dengan metode cold pressed food-grade kualitas premium. Menjaga elastisitas pembuluh darah, meningkatkan vitalitas, dan mengoptimalkan respon imun.', 115000.00, 60, 'botol', 'habbatussauda.svg', 'aktif');

-- Data Pesanan Awal (Contoh Transaksi Aktif & Riwayat Selesai)
INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_pengguna`, `nama_pembeli`, `telepon_pembeli`, `alamat_pengiriman`, `catatan`, `total_harga`, `metode_pembayaran`, `status_pesanan`, `tanggal_pesanan`) VALUES
(1, 'INV-HP202611-001', 2, 'Dr. Hendra Gunawan, Sp.Far', '081298765432', 'Klinik Sehat Alami Medika, Jl. Raya Menteng No. 88, Menteng, Jakarta Pusat, DKI Jakarta 10310', 'Mohon disertakan brosur uji klinis dan sertifikat BPOM produk, terima kasih.', 330000.00, 'Transfer Bank (BCA Virtual Account)', 'diproses', '2026-11-07 10:15:00'),
(2, 'INV-HP202611-002', 2, 'Dr. Hendra Gunawan, Sp.Far', '081298765432', 'Klinik Sehat Alami Medika, Jl. Raya Menteng No. 88, Menteng, Jakarta Pusat, DKI Jakarta 10310', 'Pesanan batch fitofarmaka telah diterima apotek dengan baik.', 115000.00, 'Transfer Bank (BCA Virtual Account)', 'selesai', '2026-11-06 14:20:00');

-- Data Detail Pesanan Awal
INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `nama_produk`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 'Curcuma Forte Ekstrak Temulawak 60 Kapsul (BPOM TR213387651)', 145000.00, 1, 145000.00),
(2, 1, 4, 'Propolis Trigona Raw Nano Honey 250g (Halal & BPOM TR213601441)', 185000.00, 1, 185000.00),
(3, 2, 8, 'Habbatussauda Extra Softgel Habbats Oil 100 Butir (BPOM TR203378901)', 115000.00, 1, 115000.00);

