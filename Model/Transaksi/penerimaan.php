<?php
require_once __DIR__ . '/../../dbconnection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new DBconnection();
$conn = $db->get_connection();

$role_aktif = $_SESSION['role'] ?? '';

if (!empty($_SESSION['iduser'])) {
    $iduser_aktif = intval($_SESSION['iduser']);
    $q_role = mysqli_query($conn, "
        SELECT r.*
        FROM user u
        LEFT JOIN role r ON u.idrole = r.idrole
        WHERE u.iduser = $iduser_aktif
        LIMIT 1
    ");
    if ($q_role && $row_role = mysqli_fetch_assoc($q_role)) {
        $role_aktif = $row_role['nama_role'] ?? $row_role['role'] ?? $row_role['nama'] ?? $role_aktif;
        $_SESSION['role'] = $role_aktif;
    }
}

$role_clean = strtolower(trim((string)$role_aktif));
$is_admin = ($role_clean === 'administrator');
$is_gudang = ($role_clean === 'gudang');
$can_proses_penerimaan = ($is_admin || $is_gudang);

$result_pending = null;
if ($can_proses_penerimaan) {
    $query_pending = "SELECT p.*, v.nama_vendor, u.username 
                      FROM pengadaan p 
                      JOIN vendor v ON p.vendor_idvendor = v.idvendor 
                      JOIN user u ON p.user_iduser = u.iduser 
                      WHERE p.status != '1' 
                      ORDER BY p.idpengadaan DESC";
    $result_pending = mysqli_query($conn, $query_pending);
}

$query_selesai = "SELECT penerimaan.*, 
                         pengadaan.idpengadaan, 
                         vendor.nama_vendor, 
                         user.username,
                         COALESCE(dp_sum.subtotal_diterima, 0) AS subtotal_diterima
                  FROM penerimaan 
                  JOIN pengadaan ON penerimaan.idpengadaan = pengadaan.idpengadaan 
                  JOIN vendor ON pengadaan.vendor_idvendor = vendor.idvendor 
                  JOIN user ON penerimaan.iduser = user.iduser 
                  LEFT JOIN (
                      SELECT idpenerimaan, 
                             SUM(jumlah_terima * harga_satuan_terima) AS subtotal_diterima
                      FROM detail_penerimaan
                      GROUP BY idpenerimaan
                  ) dp_sum ON penerimaan.idpenerimaan = dp_sum.idpenerimaan
                  ORDER BY penerimaan.idpenerimaan DESC";
$result_selesai = mysqli_query($conn, $query_selesai);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div>
            <h3 class="fw-bold text-dark mb-1">Data Penerimaan Barang</h3>
            <p class="text-muted mb-0">Kelola catatan penerimaan barang dari pesanan pengadaan vendor secara parsial atau penuh.</p>
        </div>
    </div>

    <?php if ($can_proses_penerimaan): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white text-dark fw-bold py-3 px-4 rounded-top-4">
                Daftar Pengadaan 
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">No</th>
                            <th class="py-3">ID PO</th>
                            <th class="py-3">Tanggal Pengadaan</th>
                            <th class="py-3">Vendor</th>
                            <th class="py-3">Total Nilai</th>
                            <th class="py-3">Admin PO</th>
                            <th class="py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($result_pending && mysqli_num_rows($result_pending) > 0):
                            while($row = mysqli_fetch_assoc($result_pending)): 
                        ?>
                            <tr>
                                <td class="px-4 text-muted"><?= $no++; ?></td>
                                <td><strong class="text-dark"><?= $row['idpengadaan']; ?></strong></td>
                                <td><?= htmlspecialchars($row['timestamp']); ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_vendor']); ?></td>
                                <td>Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td class="text-center">
                                    <a href="index.php?page=konfirmasi_penerimaan&idpengadaan=<?= $row['idpengadaan']; ?>" class="btn btn-success btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm">
                                        Terima Barang
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada pengadaan aktif yang perlu diterima.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white text-dark fw-bold py-3 px-4 rounded-top-4">
            Daftar Riwayat Penerimaan Barang
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID Penerimaan</th>
                        <th class="py-3">Tanggal / Waktu</th>
                        <th class="py-3">ID Pengadaan (PO)</th>
                        <th class="py-3">Vendor</th>
                        <th class="py-3">Petugas (User)</th>
                        <th class="py-3">Subtotal</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_selesai && mysqli_num_rows($result_selesai) > 0): ?>
                        <?php while($selesai = mysqli_fetch_assoc($result_selesai)): ?>
                            <tr>
                                <td class="px-4 fw-bold text-muted"><?= $selesai['idpenerimaan']; ?></td>
                                <td><?= htmlspecialchars($selesai['created_at']); ?></td>
                                <td><strong class="text-dark"><?= $selesai['idpengadaan']; ?></strong></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($selesai['nama_vendor']); ?></td>
                                <td><?= htmlspecialchars($selesai['username']); ?></td>
                                <td class="fw-bold text-success">Rp <?= number_format((float)$selesai['subtotal_diterima'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <a href="index.php?page=detail_penerimaan&id=<?= $selesai['idpenerimaan']; ?>" class="btn btn-success btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat penerimaan barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>