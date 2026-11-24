<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../dbconnection.php';

$db = new DBconnection();
$conn = $db->get_connection();
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$iduser_aktif = intval($_SESSION['iduser']);
$q_user_aktif = mysqli_query($conn, "
    SELECT u.*, r.nama_role
    FROM user u
    LEFT JOIN role r ON u.idrole = r.idrole
    WHERE u.iduser = $iduser_aktif
    LIMIT 1
");
$current_user = mysqli_fetch_assoc($q_user_aktif);

$nama_user = $current_user['username'] ?? $_SESSION['username'] ?? 'User';
$role_user = $current_user['nama_role'] ?? $current_user['role'] ?? $_SESSION['role'] ?? 'User';
$_SESSION['role'] = $role_user;

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
        body {
            background-color: #ffffff; 
            color: #000000;  
            padding-top: 40px; 
        }

        .navbar {
            position: fixed !important;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            z-index: 1030;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #ffffff;
            border-right: 1px solid #000000; 
            position: fixed;
            
        }
        .nav-pills .nav-link.active {
            background-color: #198754 !important; 
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
        .avatar-kosong {
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.45);
            color: #ffffff;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-success px-4 shadow-sm py-2 d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold fs-4 text-white" href="index.php?page=dashboard">🌱 HellMart <span class="fs-6 fw-normal text-light opacity-75">| TOKO PERTANIAN</span></a>
        
        <div class="d-flex align-items-center gap-2">
            <div class="text-end text-white lh-sm">
                <div class="fw-bold small"><?= htmlspecialchars($nama_user); ?></div>
                <span class="badge bg-dark" style="font-size: 10px;"><?= htmlspecialchars($role_user); ?></span>
            </div>
            <div class="d-flex align-items-center justify-content-center rounded-circle avatar-kosong flex-shrink-0"
                 title="<?= htmlspecialchars($nama_user); ?> (<?= htmlspecialchars($role_user); ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                </svg>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            
            <nav class="col-md-3 col-lg-2 d-md-block sidebar py-4 px-3 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase fw-bold mb-3 px-2 fs-7">Menu Utama</h6>
                    <ul class="nav flex-column nav-pills gap-1">
                        <li class="nav-item">
                            <a class="nav-link <?= ($page == 'home' || $page == 'dashboard') ? 'active' : ''; ?>" href="index.php?page=dashboard">Dashboard</a>
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
                        <li class="nav-item">
                            <a class="nav-link <?= ($page == 'kartu_stok') ? 'active' : ''; ?>" href="index.php?page=kartu_stok">Kartu Stok</a>
                        </li>
                    </ul>
                </div>

                <div class="pt-3 border-top mt-3">
                    <a href="logout.php" class="btn btn-outline-danger w-100 fw-bold py-2 rounded-3 text-start px-3 d-flex align-items-center justify-content-between" onclick="return confirm('Yakin ingin keluar dari sistem?')">
                        <span>Logout</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                            <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                        </svg>
                    </a>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php if ($page == 'home' || $page == 'dashboard'): ?>
                    <div class="container-fluid px-0">
                        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                            <h2 class="fw-bold text-dark mb-2">Selamat Datang Kembali, <?= htmlspecialchars($nama_user); ?>!</h2>
                            <p class="text-muted mb-0">HellMart adalah sistem manajemen operasional dan toko pertanian terpadu untuk mengelola data master komoditas, inventaris multi-satuan, pengadaan PO vendor, serta transaksi kasir & penjualan real-time.</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-uppercase fw-bold text-muted small mb-3">Akses Cepat & Pintasan Menu Utama</h6>
                            <div class="row g-3">
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=barang" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">📦</div>
                                        <h6 class="fw-bold text-dark mb-1">Kelola Barang</h6>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=satuan" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">⚖️</div>
                                        <h6 class="fw-bold text-dark mb-1">Kelola Satuan</h6>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=vendor" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">🤝</div>
                                        <h6 class="fw-bold text-dark mb-1">Data Vendor</h6>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=pengadaan" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">🛒</div>
                                        <h6 class="fw-bold text-dark mb-1">Pengadaan PO</h6>
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <a href="index.php?page=penjualan" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                                        <div class="text-success mb-2 fs-4">💰</div>
                                        <h6 class="fw-bold text-dark mb-1">Kasir & Penjualan</h6>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-uppercase fw-bold text-muted small mb-0">Ringkasan Data Master & Akses Sistem</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">JUMLAH BARANG</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_barang; ?> <span class="fs-6 fw-bold text-muted">Barang</span></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">JUMLAH SATUAN</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_satuan; ?> <span class="fs-6 fw-bold text-muted">Satuan</span></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">VENDOR SUPLAI</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_vendor; ?> <span class="fs-6 fw-bold text-muted">Mitra</span></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <small class="text-muted d-block fw-bold mb-1">USER & HAK AKSES</small>
                                        <h4 class="fw-bold text-dark mb-0"><?= $jml_user; ?> <span class="fs-6 fw-bold text-muted">Aktif</span></h4>
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
                <?php elseif ($page == 'konfirmasi_penerimaan'): include '../Model/Transaksi/konfirmasi_penerimaan.php'; ?>
                <?php elseif ($page == 'proses_konfirmasi_penerimaan'): include '../Model/Transaksi/proses_konfirmasi_penerimaan.php'; ?>
                <?php elseif ($page == 'detail_penerimaan'): include '../Model/Transaksi/detail_penerimaan.php'; ?>
                <?php elseif ($page == 'retur'): include '../Model/Transaksi/retur.php'; ?>
                <?php elseif ($page == 'penjualan'): include '../Model/Transaksi/penjualan.php'; ?>
                <?php elseif ($page == 'tambah_penjualan'): include '../Model/Transaksi/tambah_penjualan.php'; ?>
                <?php elseif ($page == 'tambah_retur'): include '../Model/Transaksi/tambah_retur.php'; ?>
                <?php elseif ($page == 'detail_penjualan'): include '../Model/Transaksi/detail_penjualan.php'; ?>

                <?php elseif ($page == 'detail_retur'): include '../Model/Transaksi/detail_retur.php'; ?>
                <?php elseif ($page == 'kartu_stok'): include '../Model/Transaksi/kartu_stok.php'; ?>
                
                <?php endif; ?>
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>