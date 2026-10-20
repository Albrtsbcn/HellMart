<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();
$result = mysqli_query($conn, "SELECT * FROM v_satuan");
?>
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data Satuan</span>
        <a href="index.php?page=tambah_satuan" class="btn btn-sm btn-light fw-bold">+ Tambah Satuan</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Satuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['idsatuan']; ?></td>
                    <td><?= $row['nama_satuan']; ?></td>
                    <td><?= $row['status']; ?></td>
                    <td>
                        <a href="index.php?page=update_satuan&id=<?= $row['idsatuan']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="Satuan/hapus_satuan.php?id=<?= $row['idsatuan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>