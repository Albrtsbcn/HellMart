<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();
$result = mysqli_query($conn, "SELECT * FROM v_role");
?>
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data Role</span>
        <a href="Role/tambah_role.php" class="btn btn-sm btn-light fw-bold">+ Tambah Role</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead><tr><th>ID Role</th><th>Nama Role</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['idrole']; ?></td>
                    <td><?= $row['nama_role']; ?></td>
                    <td><a href="Role/hapus_role.php?id=<?= $row['idrole']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>