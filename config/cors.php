<?php
/**
 * Konfigurasi Header CORS & JSON Universal
 * Herbapedia REST API Marketplace
 */

// Mengizinkan akses lintas domain (CORS) untuk Postman, Web Browser, dan Aplikasi Mobile
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Content-Type: application/json; charset=UTF-8");

// Tangani HTTP OPTIONS preflight request langsung dengan status 200 OK
if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
    http_response_code(200);
    exit;
}

