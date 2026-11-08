<?php
/**
 * REST API Endpoint: Kategori (Daftar Pengelompokan Sediaan Herbal)
 * Herbapedia — PT Herbal Sejati Indonesia (Soal 12)
 * 
 * HTTP Method:
 * - GET /api/kategori.php -> Menampilkan daftar seluruh kategori sediaan beserta statistik jumlah produk
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

// Validasi hanya metode GET yang diizinkan untuk endpoint ini
requireHttpMethod('GET');

$db = getDBConnection();

// Query seluruh kategori dan hitung jumlah produk aktif di tiap kategori
$sql = "SELECT 
            k.id_kategori,
            k.nama_kategori,
            k.slug,
            k.deskripsi,
            COUNT(p.id_produk) AS total_produk
        FROM kategori k
        LEFT JOIN produk p ON k.id_kategori = p.id_kategori AND p.status = 'aktif'
        GROUP BY k.id_kategori, k.nama_kategori, k.slug, k.deskripsi
        ORDER BY k.id_kategori ASC";

$result = $db->query($sql);

if (!$result) {
    sendJsonResponse('error', 500, 'Gagal mengambil data kategori dari database: ' . $db->error);
}

$daftarKategori = [];
while ($row = $result->fetch_assoc()) {
    $daftarKategori[] = [
        'id_kategori'   => (int)$row['id_kategori'],
        'nama_kategori' => $row['nama_kategori'],
        'slug'          => $row['slug'],
        'deskripsi'     => $row['deskripsi'],
        'total_produk'  => (int)$row['total_produk']
    ];
}

sendJsonResponse('success', 200, 'Daftar kategori sediaan herbal berhasil diambil.', $daftarKategori, [
    'total_data' => count($daftarKategori)
]);

