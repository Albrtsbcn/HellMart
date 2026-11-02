<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$query = "SELECT pengadaan.*, vendor.nama_vendor, user.username 
          FROM pengadaan 
          JOIN vendor ON pengadaan.vendor_idvendor = vendor.idvendor 
          JOIN user ON pengadaan.user_iduser = user.iduser 
          ORDER BY pengadaan.idpengadaan DESC";

$result = mysqli_query($conn, $query);
?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data Transaksi Pengadaan Barang</span>
        <a href="/HellMart/includes/index.php?page=tambah_pengadaan" class="btn btn-sm btn-light fw-bold">+ Tambah Pengadaan</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu / Tanggal</th>
                    <th>Vendor</th>
                    <th>Subtotal</th>
                    <th>PPN</th>
                    <th>Total Nilai</th>
                    <th>Admin / User</th>
                    <th>Aksi</th> 
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if ($result && mysqli_num_rows($result) > 0):
                    while($row = mysqli_fetch_assoc($result)): 
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $row['timestamp']; ?></td>
                        <td><?= htmlspecialchars($row['nama_vendor']); ?></td>
                        <td>Rp <?= number_format($row['subtotal_nilai'], 0, ',', '.'); ?></td>
                        <td>Rp <?= number_format($row['ppn'], 0, ',', '.'); ?></td>
                        <td>Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td>
                            <a href="/HellMart/includes/index.php?page=detail_pengadaan&id=<?= $row['idpengadaan']; ?>" class="btn btn-sm btn-dark">
                                <i class="bi bi-list-ul"></i> Lihat Detail
                            </a>
                        </td>
                    </tr>
                <?php 
                    endwhile; 
                else:
                ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data transaksi pengadaan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>