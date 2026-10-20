<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();
$result = mysqli_query($conn, "SELECT * FROM v_barang");
?>
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data Barang</span>
        <a href="../includes/index.php?page=tambah_barang" class="btn btn-sm btn-light fw-bold">+ Tambah Barang</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Jenis</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Satuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['idbarang']; ?></td>
                    <td><?= $row['jenis']; ?></td>
                    <td><?= $row['nama']; ?></td>
                    <td><?= number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td><?= $row['nama_satuan']; ?></td>
                    <td><?= $row['status']; ?></td>
                    <td>
                        <a href="index.php?page=update_barang&id=<?= $row['idbarang']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="Barang/hapus_barang.php?id=<?= $row['idbarang']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>