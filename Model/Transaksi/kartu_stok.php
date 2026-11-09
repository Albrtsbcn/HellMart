<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$query = "SELECT ks.*, b.nama AS nama_barang 
          FROM kartu_stok ks 
          JOIN barang b ON ks.idbarang = b.idbarang 
          ORDER BY ks.idkartu_stok DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Kartu Stok</h3>
                <h4 class="text-muted mb-0">Riwayat keluar masuk barang.</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3">Waktu / Tanggal</th>
                        <th class="py-3">Nama Barang</th>
                        <th class="py-3 text-center">Jenis Transaksi</th>
                        <th class="py-3 text-center">Masuk</th>
                        <th class="py-3 text-center">Keluar</th>
                        <th class="py-3 text-center">Sisa Stok</th>
                        <th class="py-3 px-4">ID Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $jenis = $row['jenis_transaksi'];
                            $badge_bg = 'bg-secondary';
                            $jenis_teks = $jenis;
                            
                            if ($jenis == 'M') {
                                $badge_bg = 'bg-success';
                                $jenis_teks = 'Masuk';
                            } elseif ($jenis == 'P') {
                                $badge_bg = 'bg-danger';
                                $jenis_teks = 'Keluar';
                            } elseif ($jenis == 'R') {
                                $badge_bg = 'bg-warning text-dark';
                                $jenis_teks = 'Retur';
                            }
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['idkartu_stok']; ?></td>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td class="text-center">
                                <span class="badge <?= $badge_bg; ?> px-2 py-1 rounded-2 fw-semibold fs-8">
                                    <?= $jenis_teks; ?>
                                </span>
                            </td>
                            <td class="text-center fw-semibold text-success"><?= $row['masuk'] > 0 ? '+' . number_format($row['masuk'], 0, ',', '.') : '-'; ?></td>
                            <td class="text-center fw-semibold text-danger"><?= $row['keluar'] > 0 ? '-' . number_format($row['keluar'], 0, ',', '.') : '-'; ?></td>
                            <td class="text-center fw-bold text-dark"><?= number_format($row['stock'], 0, ',', '.'); ?></td>
                            <td class="px-4 text-muted small"><?= $row['idtransaksi']; ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat pergerakan kartu stok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>