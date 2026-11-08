<?php
/**
 * Root Discovery Endpoint: Herbapedia Marketplace REST API
 * Herbapedia — PT Herbal Sejati Indonesia (Soal 12)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../helpers/response.php';

requireHttpMethod('GET');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$baseApiUrl = $protocol . $host . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

sendJsonResponse('success', 200, 'Selamat Datang di Layanan Web Service / REST API Marketplace Herbapedia (PT Herbal Sejati Indonesia).', [
    'service_name' => 'Herbapedia RESTful API Engine',
    'version'      => '1.0.0',
    'standards'    => [
        'architecture' => 'REST (Representational State Transfer)',
        'data_format'  => 'JSON (JavaScript Object Notation)',
        'charset'      => 'UTF-8',
        'auth_method'  => 'Stateless'
    ],
    'endpoints'    => [
        [
            'method'      => 'GET',
            'endpoint'    => $baseApiUrl . '/produk.php',
            'description' => 'Menampilkan daftar seluruh katalog produk herbal aktif (mendukung query ?search=..., ?kategori=..., ?limit=...)',
            'sample_url'  => $baseApiUrl . '/produk.php?limit=5'
        ],
        [
            'method'      => 'GET',
            'endpoint'    => $baseApiUrl . '/produk.php?id={id}',
            'description' => 'Menampilkan detail lengkap 1 produk spesifik (izin BPOM TR, khasiat, sisa stok gudang, dan respon 404 jika tidak ditemukan)',
            'sample_url'  => $baseApiUrl . '/produk.php?id=1'
        ],
        [
            'method'      => 'GET',
            'endpoint'    => $baseApiUrl . '/kategori.php',
            'description' => 'Menampilkan seluruh daftar kategori sediaan herbal beserta total produk',
            'sample_url'  => $baseApiUrl . '/kategori.php'
        ],
        [
            'method'      => 'POST',
            'endpoint'    => $baseApiUrl . '/pesanan.php',
            'description' => 'Membuat transaksi pesanan baru via JSON payload, memotong stok secara atomik (ACID), dan menerbitkan kode faktur resmi',
            'payload_sample' => [
                'nama_pembeli'      => 'Budi Santoso',
                'telepon_pembeli'   => '081234567890',
                'alamat_pengiriman' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'catatan'           => 'Mohon bubble wrap tebal',
                'metode_pembayaran' => 'Transfer Bank (BCA Virtual Account)',
                'items'             => [
                    ['id_produk' => 1, 'jumlah' => 2]
                ]
            ]
        ]
    ]
]);

