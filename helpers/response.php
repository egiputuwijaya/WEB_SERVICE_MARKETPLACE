<?php
/**
 * Helper Respon REST API Universal
 * Herbapedia Marketplace Web Service (Soal 12)
 */

/**
 * Mengirimkan respon JSON terstandarisasi dan menghentikan eksekusi script
 * 
 * @param string $status 'success' atau 'error'
 * @param int $statusCode Kode status HTTP (200, 201, 400, 404, 405, 500)
 * @param string $message Pesan informatif untuk client API
 * @param mixed $data Data payload (array, objek, null)
 * @param array $extra Metadata tambahan opsional (misal: total_data, filters)
 */
function sendJsonResponse($status, $statusCode, $message, $data = null, array $extra = []) {
    http_response_code($statusCode);
    
    $payload = [
        'status'    => $status,
        'code'      => $statusCode,
        'message'   => $message,
    ];
    
    if ($data !== null) {
        $payload['data'] = $data;
    }
    
    // Gabungkan metadata tambahan jika ada
    foreach ($extra as $key => $value) {
        $payload[$key] = $value;
    }
    
    $payload['timestamp'] = date('c');
    
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Membaca dan mem-parsing raw JSON input dari HTTP Request Body (POST / PUT)
 * 
 * @return array
 */
function getJsonRequestBody() {
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        return [];
    }
    
    $decoded = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse('error', 400, 'Format JSON dalam request body tidak valid: ' . json_last_error_msg());
    }
    
    return is_array($decoded) ? $decoded : [];
}

/**
 * Memvalidasi apakah HTTP Method dari request sesuai dengan yang diizinkan
 * 
 * @param string|array $allowedMethod Metode yang diizinkan, misal 'GET' atau ['GET', 'POST']
 */
function requireHttpMethod($allowedMethod) {
    $allowed = is_array($allowedMethod) ? array_map('strtoupper', $allowedMethod) : [strtoupper($allowedMethod)];
    $current = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    
    if (!in_array($current, $allowed, true)) {
        sendJsonResponse('error', 405, "Metode HTTP '{$current}' tidak diizinkan untuk endpoint ini. Metode yang diterima: " . implode(', ', $allowed));
    }
}

