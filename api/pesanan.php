<?php
/**
 * REST API Endpoint: Pesanan (Pembuatan Transaksi Pesanan & Cek Invoice)
 * Herbapedia — PT Herbal Sejati Indonesia (Soal 12)
 * 
 * HTTP Methods:
 * - POST /api/pesanan.php          -> Membuat pesanan baru dari JSON body (dengan validasi stok & transaksi ACID)
 * - GET  /api/pesanan.php?kode=... -> Mengambil detail pesanan berdasarkan kode invoice (opsional/ekstensi)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
requireHttpMethod(['GET', 'POST']);

$db = getDBConnection();

// ============================================================================
// METODE GET: CEK STATUS & DETAIL PESANAN BERDASARKAN KODE INVOICE ATAU ID
// ============================================================================
if ($method === 'GET') {
    $kode = isset($_GET['kode']) ? trim($_GET['kode']) : null;
    $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
    
    if (empty($kode) && empty($id)) {
        sendJsonResponse('error', 400, 'Parameter kode invoice (?kode=INV-...) atau ID pesanan (?id=...) wajib disertakan.');
    }
    
    if (!empty($kode)) {
        $stmt = $db->prepare("SELECT * FROM pesanan WHERE kode_pesanan = ? LIMIT 1");
        $stmt->bind_param('s', $kode);
    } else {
        $stmt = $db->prepare("SELECT * FROM pesanan WHERE id_pesanan = ? LIMIT 1");
        $stmt->bind_param('i', $id);
    }
    
    $stmt->execute();
    $pesanan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$pesanan) {
        sendJsonResponse('error', 404, 'Data pesanan tidak ditemukan di sistem.');
    }
    
    // Ambil rincian item sediaan dari detail_pesanan
    $stmtItems = $db->prepare("SELECT id_detail, id_produk, nama_produk, harga_satuan, jumlah, subtotal FROM detail_pesanan WHERE id_pesanan = ?");
    $stmtItems->bind_param('i', $pesanan['id_pesanan']);
    $stmtItems->execute();
    $resItems = $stmtItems->get_result();
    
    $items = [];
    while ($item = $resItems->fetch_assoc()) {
        $items[] = [
            'id_detail'    => (int)$item['id_detail'],
            'id_produk'    => (int)$item['id_produk'],
            'nama_produk'  => $item['nama_produk'],
            'harga_satuan' => (float)$item['harga_satuan'],
            'jumlah'       => (int)$item['jumlah'],
            'subtotal'     => (float)$item['subtotal']
        ];
    }
    $stmtItems->close();
    
    $pesanan['items'] = $items;
    sendJsonResponse('success', 200, 'Detail pesanan berhasil ditemukan.', $pesanan);
}

// ============================================================================
// METODE POST: MEMBUAT TRANSAKSI PESANAN BARU DENGAN BODY RAW JSON
// ============================================================================
if ($method === 'POST') {
    $input = getJsonRequestBody();
    
    // Fallback jika dikirim via x-www-form-urlencoded
    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }
    
    // 1. Validasi Input Header Pesanan
    $namaPembeli   = trim($input['nama_pembeli'] ?? '');
    $telepon       = trim($input['telepon_pembeli'] ?? '');
    $alamat        = trim($input['alamat_pengiriman'] ?? '');
    $catatan       = trim($input['catatan'] ?? '');
    $metodeBayar   = trim($input['metode_pembayaran'] ?? 'Transfer Bank (BCA Virtual Account)');
    $idPengguna    = !empty($input['id_pengguna']) ? (int)$input['id_pengguna'] : null;
    $items         = $input['items'] ?? [];
    
    $errors = [];
    if (empty($namaPembeli)) {
        $errors[] = "Field 'nama_pembeli' wajib diisi (minimal 3 karakter).";
    }
    if (empty($telepon)) {
        $errors[] = "Field 'telepon_pembeli' wajib diisi.";
    }
    if (empty($alamat)) {
        $errors[] = "Field 'alamat_pengiriman' wajib diisi lengkap untuk tujuan pengiriman.";
    }
    if (empty($items) || !is_array($items)) {
        $errors[] = "Field 'items' wajib berupa array dan berisi minimal 1 sediaan produk.";
    }
    
    if (!empty($errors)) {
        sendJsonResponse('error', 400, 'Validasi data pemesanan gagal.', null, ['error_details' => $errors]);
    }
    
    // 2. Mulai Database Transaction (ACID)
    $db->begin_transaction();
    
    try {
        $totalHarga = 0;
        $itemsToProcess = [];
        
        // Periksa setiap item sediaan dan ketersediaan stok riil
        foreach ($items as $index => $item) {
            $idProduk = filter_var($item['id_produk'] ?? 0, FILTER_VALIDATE_INT);
            $jumlah   = filter_var($item['jumlah'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$idProduk || $idProduk <= 0) {
                throw new Exception("Item ke-" . ($index + 1) . ": 'id_produk' tidak valid.");
            }
            if (!$jumlah || $jumlah <= 0) {
                throw new Exception("Item ke-" . ($index + 1) . ": 'jumlah' sediaan minimal 1 unit.");
            }
            
            // Kunci baris produk untuk validasi stok terkini (Pencegahan Race Condition)
            $stmtCek = $db->prepare("SELECT id_produk, nama_produk, harga, stok, status FROM produk WHERE id_produk = ? FOR UPDATE");
            $stmtCek->bind_param('i', $idProduk);
            $stmtCek->execute();
            $produk = $stmtCek->get_result()->fetch_assoc();
            $stmtCek->close();
            
            if (!$produk) {
                throw new Exception("Produk dengan ID {$idProduk} tidak ditemukan.");
            }
            
            if ($produk['status'] !== 'aktif') {
                throw new Exception("Sediaan herbal '{$produk['nama_produk']}' sedang tidak aktif.");
            }
            
            if ((int)$produk['stok'] < $jumlah) {
                throw new Exception("Stok sediaan '{$produk['nama_produk']}' tidak mencukupi. Sisa stok: {$produk['stok']}, jumlah dipesan: {$jumlah}.");
            }
            
            $subtotal = (float)$produk['harga'] * $jumlah;
            $totalHarga += $subtotal;
            
            $itemsToProcess[] = [
                'id_produk'    => $idProduk,
                'nama_produk'  => $produk['nama_produk'],
                'harga_satuan' => (float)$produk['harga'],
                'jumlah'       => $jumlah,
                'subtotal'     => $subtotal
            ];
        }
        
        // 3. Generate Kode Invoice Unik
        $kodePesanan = 'INV-HP' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        
        // 4. Simpan Header Pesanan ke tabel `pesanan`
        $sqlPesanan = "INSERT INTO pesanan 
                        (kode_pesanan, id_pengguna, nama_pembeli, telepon_pembeli, alamat_pengiriman, catatan, total_harga, metode_pembayaran, status_pesanan)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'menunggu_konfirmasi')";
        $stmtPesanan = $db->prepare($sqlPesanan);
        if (!$stmtPesanan) {
            throw new Exception("Gagal mempersiapkan SQL pesanan: " . $db->error);
        }
        $stmtPesanan->bind_param('sissssds', $kodePesanan, $idPengguna, $namaPembeli, $telepon, $alamat, $catatan, $totalHarga, $metodeBayar);
        $stmtPesanan->execute();
        $idPesanan = $db->insert_id;
        $stmtPesanan->close();
        
        if (!$idPesanan) {
            throw new Exception("Gagal menyimpan data pesanan induk ke database.");
        }
        
        // 5. Simpan Rincian Item ke `detail_pesanan` & Potong Stok Sediaan di `produk`
        $sqlDetail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, nama_produk, harga_satuan, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtDetail = $db->prepare($sqlDetail);
        
        $sqlPotongStok = "UPDATE produk SET stok = stok - ? WHERE id_produk = ?";
        $stmtPotongStok = $db->prepare($sqlPotongStok);
        
        foreach ($itemsToProcess as $item) {
            // Simpan detail baris
            $stmtDetail->bind_param('iisdid', $idPesanan, $item['id_produk'], $item['nama_produk'], $item['harga_satuan'], $item['jumlah'], $item['subtotal']);
            $stmtDetail->execute();
            
            // Kurangi stok produk secara atomik
            $stmtPotongStok->bind_param('ii', $item['jumlah'], $item['id_produk']);
            $stmtPotongStok->execute();
        }
        
        $stmtDetail->close();
        $stmtPotongStok->close();
        
        // 6. Commit Transaksi Database (Berhasil Penuh)
        $db->commit();
        
        // Struktur data respon pesanan baru berhasil dibuat
        $orderData = [
            'id_pesanan'        => (int)$idPesanan,
            'kode_pesanan'      => $kodePesanan,
            'nama_pembeli'      => $namaPembeli,
            'telepon_pembeli'   => $telepon,
            'alamat_pengiriman' => $alamat,
            'total_harga'       => (float)$totalHarga,
            'total_format'      => 'Rp ' . number_format($totalHarga, 0, ',', '.'),
            'metode_pembayaran' => $metodeBayar,
            'status_pesanan'    => 'menunggu_konfirmasi',
            'jumlah_item'       => count($itemsToProcess),
            'items'             => $itemsToProcess
        ];
        
        sendJsonResponse('success', 201, 'Pesanan baru berhasil dibuat dan stok produk telah dipotong secara otomatis di database.', $orderData);
        
    } catch (Exception $e) {
        // Rollback seluruh perubahan SQL jika terjadi kegagalan/stok tidak cukup
        $db->rollback();
        sendJsonResponse('error', 400, $e->getMessage());
    }
}

