<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();
$result = mysqli_query($conn, "SELECT * FROM v_vendor");
?>
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data Vendor</span>
        <a href="index.php?page=tambah_vendor" class="btn btn-sm btn-light fw-bold">+ Tambah Vendor</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID Vendor</th>
                    <th>Nama Vendor</th>
                    <th>Badan Hukum</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['idvendor']; ?></td>
                    <td><?= $row['nama_vendor']; ?></td>
                    <td><?= $row['badan_hukum']; ?></td>
                    <td><?= $row['status']; ?></td>
                    <td>
                        <a href="index.php?page=update_vendor&id=<?= $row['idvendor']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="Vendor/hapus_vendor.php?id=<?= $row['idvendor']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>