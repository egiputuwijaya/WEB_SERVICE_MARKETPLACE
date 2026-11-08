<?php
/**
 * Konfigurasi Koneksi Database REST API Marketplace
 * Herbapedia — PT Herbal Sejati Indonesia
 * Menggunakan MySQLi dengan charset utf8mb4 dan penanganan error standar REST
 */

// Memuat env.php jika tersedia (untuk produksi / hosting)
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}

// Nilai default untuk lingkungan lokal (XAMPP / Laragon)
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'marketplace_db');
if (!defined('DB_PORT')) define('DB_PORT', 3306);

/**
 * Mendapatkan instance koneksi database MySQLi
 * @return mysqli
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Nonaktifkan default reporting mysqli agar dapat ditangani secara elegan dalam format JSON
        mysqli_report(MYSQLI_REPORT_OFF);
        
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($conn->connect_errno) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'status'    => 'error',
                'code'      => 500,
                'message'   => 'Koneksi ke database gagal: ' . $conn->connect_error,
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $conn->set_charset('utf8mb4');
    }
    
    return $conn;
}

