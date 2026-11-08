<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herbapedia REST API Explorer — PT Herbal Sejati Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg-body: #f8fafc;
            --card-border: #e2e8f0;
        }
        body {
            background-color: var(--bg-body);
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
        }
        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        }
        .api-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .api-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.1);
            transform: translateY(-2px);
        }
        .badge-method {
            font-family: 'Consolas', monospace;
            font-size: 0.78rem;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: bold;
        }
        .method-get { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .method-post { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .console-box {
            background: #0f172a;
            color: #38bdf8;
            border-radius: 12px;
            padding: 20px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.85rem;
            max-height: 480px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="py-5">

<div class="container" style="max-width: 1040px;">
    
    <!-- Top Hero Banner -->
    <div class="hero-banner mb-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-semibold mb-2">
                    <i class="bi bi-cpu-fill me-1"></i> RESTful Web Service Engine • Soal 12
                </span>
                <h2 class="fw-bold mb-1">Herbapedia Marketplace REST API</h2>
                <p class="text-light text-opacity-75 mb-0" style="font-size: 0.95rem;">
                    Layanan Integrasi Data Multi-Platform: Katalog Sediaan Herbal, Izin BPOM TR, Kategori &amp; Transaksi Pemesanan (ACID)
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-success px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i> API Service Active
                </span>
                <div class="small text-light text-opacity-50 mt-1">PHP Native &bull; MySQLi &bull; JSON</div>
            </div>
        </div>
        <div class="mt-3 pt-3 border-top border-white border-opacity-15 d-flex flex-wrap gap-2">
            <a href="docs/Herbapedia_Marketplace_API.postman_collection.json" download class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-filetype-json me-1"></i> Download Postman Collection
            </a>
            <a href="../WEB%20MARKET%20PLACE/index.php" target="_blank" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-shop me-1"></i> Buka Web Marketplace (Soal 11)
            </a>
        </div>
    </div>

    <!-- Layout 2 Kolom: Daftar Endpoint & Interactive Console -->
    <div class="row g-4">
        
        <!-- Kolom Kiri: Kartu Endpoint -->
        <div class="col-12 col-lg-7">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-hdd-network-fill text-primary me-2"></i>Daftar Endpoint REST API Resmi
            </h5>

            <!-- Endpoint 1: GET All Products -->
            <div class="api-card p-3 p-md-4 mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-method method-get">GET</span>
                        <code class="text-dark fw-bold">/api/produk.php</code>
                    </div>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="testEndpoint('api/produk.php')">
                        <i class="bi bi-play-fill me-1"></i>Uji Endpoint
                    </button>
                </div>
                <div class="small text-muted mb-2">Menampilkan seluruh katalog sediaan herbal aktif. Mendukung query <code>?search=</code>, <code>?kategori=</code>, dan <code>?limit=</code>.</div>
                <div class="small text-secondary">
                    <i class="bi bi-arrow-return-right me-1"></i>Response: <code>200 OK</code> dengan array produk &amp; metadata filter.
                </div>
            </div>

            <!-- Endpoint 2: GET Product by ID -->
            <div class="api-card p-3 p-md-4 mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-method method-get">GET</span>
                        <code class="text-dark fw-bold">/api/produk.php?id=1</code>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="testEndpoint('api/produk.php?id=1')">
                            <i class="bi bi-play-fill me-1"></i>Uji ID 1
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2 ms-1" onclick="testEndpoint('api/produk.php?id=9999')" title="Uji Respon 404">
                            Uji 404
                        </button>
                    </div>
                </div>
                <div class="small text-muted mb-2">Menampilkan detail 1 sediaan spesifik (izin BPOM TR, aturan pakai, khasiat, sisa stok, &amp; data apotek distributor).</div>
                <div class="small text-secondary">
                    <i class="bi bi-arrow-return-right me-1"></i>Response: <code>200 OK</code> jika ditemukan, <code>404 Not Found</code> jika ID tidak ada.
                </div>
            </div>

            <!-- Endpoint 3: GET Categories -->
            <div class="api-card p-3 p-md-4 mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-method method-get">GET</span>
                        <code class="text-dark fw-bold">/api/kategori.php</code>
                    </div>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="testEndpoint('api/kategori.php')">
                        <i class="bi bi-play-fill me-1"></i>Uji Endpoint
                    </button>
                </div>
                <div class="small text-muted mb-2">Menampilkan seluruh daftar kategori sediaan herbal beserta total produk terdaftar pada masing-masing kelompok.</div>
                <div class="small text-secondary">
                    <i class="bi bi-arrow-return-right me-1"></i>Response: <code>200 OK</code> dengan array kategori &amp; hitungan produk.
                </div>
            </div>

            <!-- Endpoint 4: POST Create Order -->
            <div class="api-card p-3 p-md-4 mb-3" style="border-left: 4px solid #16a34a;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-method method-post">POST</span>
                        <code class="text-dark fw-bold">/api/pesanan.php</code>
                    </div>
                    <button class="btn btn-sm btn-success rounded-pill px-3 text-white fw-semibold" onclick="testCreateOrder()">
                        <i class="bi bi-send-fill me-1"></i>Kirim JSON Order
                    </button>
                </div>
                <div class="small text-muted mb-2">Membuat transaksi pemesanan baru dari body JSON, memotong stok sediaan secara atomik (ACID), dan menerbitkan nomor faktur <code>INV-...</code>.</div>
                <div class="small text-secondary">
                    <i class="bi bi-arrow-return-right me-1"></i>Response: <code>201 Created</code> saat sukses, <code>400 Bad Request</code> jika data/stok tidak valid.
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Live Interactive Console -->
        <div class="col-12 col-lg-5">
            <div class="sticky-top" style="top: 24px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-terminal-fill text-dark me-2"></i>Live JSON Console
                    </h5>
                    <span id="responseStatusBadge" class="badge bg-secondary px-2 py-1" style="font-size: 0.72rem;">Standby</span>
                </div>
                
                <div class="console-box" id="jsonConsole">
// Klik tombol "Uji Endpoint" pada salah satu kartu
// di sebelah kiri untuk melihat respon JSON nyata
// yang dikembalikan langsung oleh server Apache & MySQL.

{
  "service": "Herbapedia REST API Engine",
  "status": "ready",
  "message": "Menunggu eksekusi request..."
}
                </div>

                <div class="mt-3 p-3 bg-white border rounded-4 small">
                    <div class="fw-bold text-dark mb-1">
                        <i class="bi bi-info-circle-fill text-primary me-1"></i>Informasi Pengujian:
                    </div>
                    <div class="text-muted">
                        Endpoint di atas juga dapat diuji menggunakan <strong>Postman</strong> atau <strong>curl</strong> dengan mengarahkan request ke URL localhost Anda.
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="text-center text-muted small mt-5 pt-4 border-top">
        Herbapedia Marketplace RESTful API &bull; PT Herbal Sejati Indonesia &bull; Soal 12
    </div>

</div>

<script>
function testEndpoint(endpointUrl) {
    const consoleBox = document.getElementById('jsonConsole');
    const statusBadge = document.getElementById('responseStatusBadge');
    
    consoleBox.textContent = '// Menghubungi server ' + endpointUrl + '...';
    statusBadge.className = 'badge bg-warning text-dark';
    statusBadge.textContent = 'Memuat...';

    fetch(endpointUrl)
        .then(async response => {
            const data = await response.json();
            statusBadge.className = response.ok ? 'badge bg-success' : 'badge bg-danger';
            statusBadge.textContent = 'HTTP ' + response.status;
            consoleBox.textContent = JSON.stringify(data, null, 2);
        })
        .catch(err => {
            statusBadge.className = 'badge bg-danger';
            statusBadge.textContent = 'Error';
            consoleBox.textContent = '// Gagal mengambil respon: ' + err.message;
        });
}

function testCreateOrder() {
    const consoleBox = document.getElementById('jsonConsole');
    const statusBadge = document.getElementById('responseStatusBadge');
    
    consoleBox.textContent = '// Mengirimkan HTTP POST JSON pesanan baru ke /api/pesanan.php...';
    statusBadge.className = 'badge bg-warning text-dark';
    statusBadge.textContent = 'Posting...';

    const sampleOrder = {
        nama_pembeli: "Budi Santoso (Uji Postman/Web)",
        telepon_pembeli: "081234567890",
        alamat_pengiriman: "Jl. Gatot Subroto Kav. 12, Jakarta Selatan, DKI Jakarta",
        catatan: "Pesanan via REST API integrasi mobile app",
        metode_pembayaran: "Transfer Bank (BCA Virtual Account)",
        items: [
            { id_produk: 1, jumlah: 1 },
            { id_produk: 6, jumlah: 2 }
        ]
    };

    fetch('api/pesanan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(sampleOrder)
    })
    .then(async response => {
        const data = await response.json();
        statusBadge.className = (response.status === 201) ? 'badge bg-success' : 'badge bg-danger';
        statusBadge.textContent = 'HTTP ' + response.status;
        consoleBox.textContent = JSON.stringify(data, null, 2);
    })
    .catch(err => {
        statusBadge.className = 'badge bg-danger';
        statusBadge.textContent = 'Error';
        consoleBox.textContent = '// Gagal mengirim pesanan: ' + err.message;
    });
}
</script>

</body>
</html>

