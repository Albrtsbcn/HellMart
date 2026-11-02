<?php
require_once __DIR__ . '/../dbconnection.php';

$db = new DBconnection();
$conn = $db->get_connection();
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$q_barang = mysqli_query($conn, "SELECT COUNT(*) AS total FROM barang");
$jml_barang = mysqli_fetch_assoc($q_barang)['total'] ?? 0;

$q_satuan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM satuan");
$jml_satuan = mysqli_fetch_assoc($q_satuan)['total'] ?? 0;

$q_vendor = mysqli_query($conn, "SELECT COUNT(*) AS total FROM vendor");
$jml_vendor = mysqli_fetch_assoc($q_vendor)['total'] ?? 0;

$q_user = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user");
$jml_user = mysqli_fetch_assoc($q_user)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HellMart - Manajemen Data Toko Pertanian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html {
        filter: grayscale(100%);
        -webkit-filter: grayscale(100%);
        }
        body {
            background-color: #ffffff; 
            color: #000000;     
        }
        .sidebar {
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #000000; 
        }
        .nav-pills .nav-link.active {
            background-color: #000000 !important; 
            color: #ffffff !important;            
            font-weight: 600;
        }
        .nav-pills .nav-link {
            color: #000000; 
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.2s ease-in-out;
        }
        .nav-pills .nav-link:hover {
            background-color: #e0e0e0; 
            color: #000000;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-success px-4 shadow-sm py-2 d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold fs-4 text-white" href="index.php">🌱 HellMart <span class="fs-6 fw-normal text-light opacity-75">| TOKO PERTANIAN</span></a>
        
        <div class="d-flex align-items-center gap-3">
            <div class="text-end text-white">
                <div class="fw-bold small">Rahel</div>
                <span class="badge bg-dark" style="font-size: 10px;">Administrator</span>
            </div>
            <div class="rounded-circle profile-placeholder shadow-sm"></div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            
            <!-- Sidebar Menu Kiri -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar py-4 px-3">
                <h6 class="text-muted text-uppercase fw-bold mb-3 px-2 fs-7">Menu Utama</h6>
                <ul class="nav flex-column nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'home') ? 'active' : ''; ?>" href="index.php?page=home">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'satuan') ? 'active' : ''; ?>" href="index.php?page=satuan">Satuan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'barang') ? 'active' : ''; ?>" href="index.php?page=barang">Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'role') ? 'active' : ''; ?>" href="index.php?page=role">Role</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'user') ? 'active' : ''; ?>" href="index.php?page=user">User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'vendor') ? 'active' : ''; ?>" href="index.php?page=vendor">Vendor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'margin') ? 'active' : ''; ?>" href="index.php?page=margin">Margin Penjualan</a>
                    </li>

                    <hr class="text-muted my-3">
                    <h6 class="text-muted text-uppercase fw-bold mb-2 px-2 fs-7">Transaksi</h6>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['pengadaan', 'tambah_pengadaan'])) ? 'active' : ''; ?>" href="index.php?page=pengadaan">Pengadaan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['penerimaan', 'tambah_penerimaan'])) ? 'active' : ''; ?>" href="index.php?page=penerimaan">Penerimaan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'retur') ? 'active' : ''; ?>" href="index.php?page=retur">Retur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['penjualan', 'tambah_penjualan'])) ? 'active' : ''; ?>" href="index.php?page=penjualan">Penjualan</a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php if ($page == 'home'): ?>
                    <div class="container-fluid px-0">
                        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                            <h2 class="fw-bold text-dark mb-2">Selamat Datang Kembali, Rahel!</h2>
                            <p class="text-muted mb-0">HellMart adalah sistem manajemen operasional dan toko pertanian terpadu untuk mengelola data master komoditas, inventaris multi-satuan, pengadaan PO vendor, serta transaksi kasir & penjualan real-time.</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-uppercase fw-bold text-muted small mb-3">Akses Cepat & Pintasan Menu Utama</h6>
                            <div class="row g-3">
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=barang" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">📦</div>
                                        <h6 class="fw-bold text-dark mb-1">Kelola Barang</h6>
                                        <small class="text-muted" style="font-size: 11px;">Master Produk & SKU</small>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=satuan" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">⚖️</div>
                                        <h6 class="fw-bold text-dark mb-1">Kelola Satuan</h6>
                                        <small class="text-muted" style="font-size: 11px;">Multi-Uom & Konversi</small>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=vendor" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">🤝</div>
                                        <h6 class="fw-bold text-dark mb-1">Data Vendor</h6>
                                        <small class="text-muted" style="font-size: 11px;">Mitra Suplai Pertanian</small>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=pengadaan" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">🛒</div>
                                        <h6 class="fw-bold text-dark mb-1">Pengadaan PO</h6>
                                        <small class="text-muted" style="font-size: 11px;">Pemesanan ke Vendor</small>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=penjualan" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">💰</div>
                                        <h6 class="fw-bold text-dark mb-1">Kasir & Penjualan</h6>
                                        <small class="text-muted" style="font-size: 11px;">POS Petani & Rekap</small>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-uppercase fw-bold text-muted small mb-0">Ringkasan Data Master & Akses Sistem</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Database Terverifikasi</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">JUMLAH BARANG</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_barang; ?> <span class="fs-6 fw-normal text-muted">Barang</span></h4>
                                        <small class="text-success" style="font-size: 11px;">Terdaftar di database</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">JUMLAH SATUAN</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_satuan; ?> <span class="fs-6 fw-normal text-muted">Satuan</span></h4>
                                        <small class="text-muted" style="font-size: 11px;">Unit & konversi</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">VENDOR SUPLAI</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_vendor; ?> <span class="fs-6 fw-normal text-muted">Mitra</span></h4>
                                        <small class="text-muted" style="font-size: 11px;">Distributor & pabrik</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">USER & HAK AKSES</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_user; ?> <span class="fs-6 fw-normal text-muted">Aktif</span></h4>
                                        <small class="text-muted" style="font-size: 11px;">Administrator & staf</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page == 'barang'): include '../Model/barang.php'; ?>
                <?php elseif ($page == 'satuan'): include '../Model/satuan.php'; ?>
                <?php elseif ($page == 'role'): include '../Model/role.php'; ?>
                <?php elseif ($page == 'user'): include '../Model/user.php'; ?>
                <?php elseif ($page == 'vendor'): include '../Model/vendor.php'; ?>
                <?php elseif ($page == 'margin'): include '../Model/margin_penjualan.php'; ?>

                <?php elseif ($page == 'pengadaan'): include '../Model/Transaksi/pengadaan.php'; ?>
                <?php elseif ($page == 'tambah_pengadaan'): include '../Model/Transaksi/tambah_pengadaan.php'; ?>
                <?php elseif ($page == 'penerimaan'): include '../Model/Transaksi/penerimaan.php'; ?>
                <?php elseif ($page == 'tambah_penerimaan'): include '../Model/Transaksi/tambah_penerimaan.php'; ?>
                <?php elseif ($page == 'retur'): include '../Model/Transaksi/retur.php'; ?>
                <?php elseif ($page == 'penjualan'): include '../Model/Transaksi/penjualan.php'; ?>
                <?php elseif ($page == 'tambah_penjualan'): include '../Model/Transaksi/tambah_penjualan.php'; ?>

                <?php endif; ?>
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>