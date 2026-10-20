<?php
// Naik 1 level dari folder includes/ ke folder utama HellMart/ untuk menemukan dbconnection.php
require_once __DIR__ . '/../dbconnection.php';

$db = new DBconnection();
$conn = $db->get_connection();
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HellMart - Manajemen Data Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Top Navbar Sederhana untuk Judul Aplikasi -->
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm py-3">
        <a class="navbar-brand fw-bold fs-4" href="index.php">HellMart - Toko Pertanian</a>
    </nav>

    <!-- Layout Utama: Sidebar Kiri & Konten Kanan -->
    <div class="container-fluid">
        <div class="row">
            
            <!-- SIDEBAR MENU DI KIRI -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar shadow-sm min-vh-100 py-4 px-3">
                <h6 class="text-muted text-uppercase fw-bold mb-3 px-2">Menu Utama</h6>
                <ul class="nav flex-column nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link <?= ($page == 'home') ? 'active bg-dark text-white' : 'text-dark'; ?>" href="index.php?page=home">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['satuan', 'tambah_satuan', 'update_satuan'])) ? 'active bg-dark text-white' : 'text-dark'; ?>" href="index.php?page=satuan">Satuan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['barang', 'tambah_barang', 'update_barang'])) ? 'active bg-dark text-white' : 'text-dark'; ?>" href="index.php?page=barang">Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['role', 'tambah_role', 'update_role'])) ? 'active bg-dark text-white' : 'text-dark'; ?>" href="index.php?page=role">Role</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['user', 'tambah_user', 'update_user'])) ? 'active bg-dark text-white' : 'text-dark'; ?>" href="index.php?page=user">User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['vendor', 'tambah_vendor', 'update_vendor'])) ? 'active bg-dark text-white' : 'text-dark'; ?>" href="index.php?page=vendor">Vendor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (in_array($page, ['margin', 'tambah_margin', 'update_margin'])) ? 'active bg-dark text-white' : 'text-dark'; ?>" href="index.php?page=margin">Margin Penjualan</a>
                    </li>
                </ul>
            </nav>

            <!-- KONTEN UTAMA DI KANAN -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5">
                <?php if ($page == 'home'): ?>
                    <div class="py-5 text-center">
                        <div class="card shadow-sm border-0 p-5 bg-white">
                            <h1 class="fw-bold mb-3 text-dark">Selamat Datang di Dashboard HellMart</h1>
                        </div>
                    </div>

                <!-- Modul Barang -->
                <?php elseif ($page == 'barang'): include '../Model/Barang/index.php'; ?>
                <?php elseif ($page == 'tambah_barang'): include '../Model/Barang/tambah_barang.php'; ?>
                <?php elseif ($page == 'update_barang'): include '../Model/Barang/update_barang.php'; ?>

                <!-- Modul Satuan -->
                <?php elseif ($page == 'satuan'): include '../Model/Satuan/index.php'; ?>
                <?php elseif ($page == 'tambah_satuan'): include '../Model/Satuan/tambah_satuan.php'; ?>
                <?php elseif ($page == 'update_satuan'): include '../Model/Satuan/update_satuan.php'; ?>

                <!-- Modul Role -->
                <?php elseif ($page == 'role'): include '../Model/Role/index.php'; ?>
                <?php elseif ($page == 'tambah_role'): include '../Model/Role/tambah_role.php'; ?>
                <?php elseif ($page == 'update_role'): include '../Model/Role/update_role.php'; ?>

                <!-- Modul User -->
                <?php elseif ($page == 'user'): include '../Model/User/index.php'; ?>
                <?php elseif ($page == 'tambah_user'): include '../Model/User/tambah_user.php'; ?>
                <?php elseif ($page == 'update_user'): include '../Model/User/update_user.php'; ?>

                <!-- Modul Vendor -->
                <?php elseif ($page == 'vendor'): include '../Model/Vendor/index.php'; ?>
                <?php elseif ($page == 'tambah_vendor'): include '../Model/Vendor/tambah_vendor.php'; ?>
                <?php elseif ($page == 'update_vendor'): include '../Model/Vendor/update_vendor.php'; ?>

                <!-- Modul Margin Penjualan -->
                <?php elseif ($page == 'margin'): include '../Model/Margin_penjualan/index.php'; ?>
                <?php elseif ($page == 'tambah_margin'): include '../Model/Margin_penjualan/tambah_marginpenjualan.php'; ?>
                <?php elseif ($page == 'update_margin'): include '../Model/Margin_penjualan/update_marginpenjualan.php'; ?>

                <?php endif; ?>
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>