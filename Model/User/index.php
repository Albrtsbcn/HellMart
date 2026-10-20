<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();
$result = mysqli_query($conn, "SELECT * FROM v_user");
?>
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data User</span>
        <a href="index.php?page=tambah_user" class="btn btn-sm btn-light fw-bold">+ Tambah User</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID User</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['iduser']; ?></td>
                    <td><?= $row['username']; ?></td>
                    <td><?= $row['nama_role']; ?></td>
                    <td>
                        <a href="../index.php?page=update_user&id=<?= $row['iduser']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="../User/hapus_user.php?id=<?= $row['iduser']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>