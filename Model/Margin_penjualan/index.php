<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();
$result = mysqli_query($conn, "SELECT * FROM v_margin_penjualan");
?>
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data Margin Penjualan</span>
        <a href="index.php?page=tambah_margin" class="btn btn-sm btn-light fw-bold">+ Tambah Margin</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Persen</th>
                    <th>Status</th>
                    <th>User</th>
                    <th>Terakhir Diubah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['idmargin_penjualan']; ?></td>
                    <td><?= $row['persen']; ?>%</td>
                    <td><?= $row['status']; ?></td>
                    <td><?= $row['username']; ?></td>
                    <td><?= $row['updated_at']; ?></td>
                    <td>
                        <a href="index.php?page=update_margin&id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="Margin_penjualan/hapus_marginpenjualan.php?id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>