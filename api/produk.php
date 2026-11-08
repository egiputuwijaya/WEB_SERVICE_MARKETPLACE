<?php
/**
 * REST API Endpoint: Produk (Katalog & Detail Sediaan Herbal)
 * Herbapedia — PT Herbal Sejati Indonesia (Soal 12)
 * 
 * HTTP Methods:
 * - GET /api/produk.php            -> Menampilkan seluruh katalog produk (mendukung search, filter kategori, limit)
 * - GET /api/produk.php?id={id}    -> Menampilkan detail 1 produk spesifik (respek 404 jika tidak ditemukan)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

// Validasi hanya metode GET yang diizinkan untuk endpoint ini
requireHttpMethod('GET');

$db = getDBConnection();

// ============================================================================
// SKENARIO 1: DETAIL PRODUK TUNGGAL BERDASARKAN PARAMETER ID (?id=...)
// ============================================================================
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if ($id === false || $id <= 0) {
        sendJsonResponse('error', 400, 'Parameter ID produk harus berupa bilangan bulat positif.');
    }
    
    // Query data produk detail berelasi dengan tabel kategori dan toko
    $sql = "SELECT 
                p.id_produk,
                p.nama_produk,
                p.deskripsi,
                p.harga,
                p.stok,
                p.satuan,
                p.foto,
                p.status,
                p.created_at,
                p.updated_at,
                k.id_kategori,
                k.nama_kategori,
                k.slug AS slug_kategori,
                t.id_toko,
                t.nama_toko,
                t.kota AS kota_toko,
                t.kontak_wa AS wa_toko
            FROM produk p
            INNER JOIN kategori k ON p.id_kategori = k.id_kategori
            INNER JOIN toko t ON p.id_toko = t.id_toko
            WHERE p.id_produk = ?
            LIMIT 1";
            
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        sendJsonResponse('error', 500, 'Gagal mempersiapkan query database: ' . $db->error);
    }
    
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produk = $result->fetch_assoc();
    $stmt->close();
    
    // Tangani jika produk tidak ditemukan di database (HTTP 404 Not Found)
    if (!$produk) {
        sendJsonResponse('error', 404, "Produk dengan ID {$id} tidak ditemukan di sistem.");
    }
    
    // Ekstraksi nomor registrasi BPOM TR jika tertera dalam judul/deskripsi
    $nomorBPOM = null;
    if (preg_match('/(BPOM\s*(TR|SD|MD|TI|QD|SL)?\s*[\d]+)/i', $produk['nama_produk'] . ' ' . $produk['deskripsi'], $matches)) {
        $nomorBPOM = strtoupper(trim($matches[0]));
    }
    
    // Format struktur data respon JSON yang rapi dan informatif
    $responseData = [
        'id_produk'       => (int)$produk['id_produk'],
        'nama_produk'     => $produk['nama_produk'],
        'deskripsi'       => $produk['deskripsi'],
        'harga'           => (float)$produk['harga'],
        'harga_format'    => 'Rp ' . number_format($produk['harga'], 0, ',', '.'),
        'stok'            => (int)$produk['stok'],
        'satuan'          => $produk['satuan'],
        'foto'            => $produk['foto'],
        'status'          => $produk['status'],
        'legalitas'       => [
            'nomor_bpom'        => $nomorBPOM ?: 'Tersertifikasi BPOM TR',
            'standar_mutu'      => 'CPOTB (Cara Pembuatan Obat Tradisional yang Baik)',
            'sertifikasi_halal' => 'LPPOM MUI Halal Terdaftar'
        ],
        'kategori'        => [
            'id_kategori'   => (int)$produk['id_kategori'],
            'nama_kategori' => $produk['nama_kategori'],
            'slug'          => $produk['slug_kategori']
        ],
        'toko'            => [
            'id_toko'   => (int)$produk['id_toko'],
            'nama_toko' => $produk['nama_toko'],
            'kota'      => $produk['kota_toko'],
            'kontak_wa' => $produk['wa_toko']
        ],
        'created_at'      => $produk['created_at'],
        'updated_at'      => $produk['updated_at']
    ];
    
    sendJsonResponse('success', 200, "Detail produk ID {$id} berhasil ditemukan.", $responseData);
}

// ============================================================================
// SKENARIO 2: DAFTAR SELURUH PRODUK DENGAN FITUR PENCARIAN & FILTER KATEGORI
// ============================================================================
$conditions = [];
$params = [];
$types = '';

// Filter status aktif secara default (kecuali diminta status tertentu)
if (isset($_GET['status']) && in_array($_GET['status'], ['aktif', 'nonaktif', 'all'])) {
    if ($_GET['status'] !== 'all') {
        $conditions[] = "p.status = ?";
        $params[] = $_GET['status'];
        $types .= 's';
    }
} else {
    $conditions[] = "p.status = 'aktif'";
}

// Filter berdasarkan Kategori (ID atau Slug)
if (!empty($_GET['kategori'])) {
    if (is_numeric($_GET['kategori'])) {
        $conditions[] = "p.id_kategori = ?";
        $params[] = (int)$_GET['kategori'];
        $types .= 'i';
    } else {
        $conditions[] = "k.slug = ?";
        $params[] = trim($_GET['kategori']);
        $types .= 's';
    }
}

// Filter pencarian berdasarkan nama produk atau deskripsi khasiat
if (!empty($_GET['search'])) {
    $searchTerm = '%' . trim($_GET['search']) . '%';
    $conditions[] = "(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

// Batas jumlah data (limit)
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;

$whereSql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sql = "SELECT 
            p.id_produk,
            p.nama_produk,
            p.deskripsi,
            p.harga,
            p.stok,
            p.satuan,
            p.foto,
            p.status,
            k.id_kategori,
            k.nama_kategori,
            k.slug AS slug_kategori,
            t.id_toko,
            t.nama_toko,
            t.kota AS kota_toko
        FROM produk p
        INNER JOIN kategori k ON p.id_kategori = k.id_kategori
        INNER JOIN toko t ON p.id_toko = t.id_toko
        {$whereSql}
        ORDER BY p.id_produk ASC
        LIMIT ?";

$params[] = $limit;
$types .= 'i';

$stmt = $db->prepare($sql);
if (!$stmt) {
    sendJsonResponse('error', 500, 'Gagal mempersiapkan query katalog: ' . $db->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$daftarProduk = [];
while ($row = $result->fetch_assoc()) {
    $daftarProduk[] = [
        'id_produk'     => (int)$row['id_produk'],
        'nama_produk'   => $row['nama_produk'],
        'harga'         => (float)$row['harga'],
        'harga_format'  => 'Rp ' . number_format($row['harga'], 0, ',', '.'),
        'stok'          => (int)$row['stok'],
        'satuan'        => $row['satuan'],
        'foto'          => $row['foto'],
        'status'        => $row['status'],
        'kategori'      => [
            'id_kategori'   => (int)$row['id_kategori'],
            'nama_kategori' => $row['nama_kategori'],
            'slug'          => $row['slug_kategori']
        ],
        'toko'          => [
            'id_toko'   => (int)$row['id_toko'],
            'nama_toko' => $row['nama_toko'],
            'kota'      => $row['kota_toko']
        ]
    ];
}
$stmt->close();

sendJsonResponse('success', 200, 'Data katalog produk berhasil diambil.', $daftarProduk, [
    'total_data' => count($daftarProduk),
    'filters'    => [
        'search'   => $_GET['search'] ?? null,
        'kategori' => $_GET['kategori'] ?? null,
        'limit'    => $limit
    ]
]);

