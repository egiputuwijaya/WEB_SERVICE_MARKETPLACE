<?php
require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/response.php';

$db = getDBConnection();

$query = $db->query("SELECT COUNT(*) AS total_produk FROM produk");
$row = $query ? $query->fetch_assoc() : null;

sendJsonResponse('success', 200, 'Koneksi database REST API berhasil terhubung ke marketplace_db.', [
    'server_info'   => $db->server_info,
    'host_info'     => $db->host_info,
    'total_produk'  => (int)($row['total_produk'] ?? 0),
    'status_engine' => 'Ready for REST API Endpoints'
]);

