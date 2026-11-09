<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    $nama_role = mysqli_real_escape_string($conn, $_POST['nama_role']);
    
    if ($_POST['aksi'] == 'tambah') {
        mysqli_query($conn, "INSERT INTO role (nama_role) VALUES ('$nama_role')");
    } elseif ($_POST['aksi'] == 'update') {
        $id = intval($_POST['idrole']);
        mysqli_query($conn, "UPDATE role SET nama_role = '$nama_role' WHERE idrole = $id");
    }
    header("Location: index.php?page=role");
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM role WHERE idrole = $id");
    header("Location: index.php?page=role");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM v_role");
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Role</h3>
                <p class="text-muted mb-0">Kelola hak akses dan peran pengguna.</p>
            </div>
            <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRole">+ Tambah Role</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3">Nama Role</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['idrole']; ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_role']); ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3 py-1 rounded-2 shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditRole<?= $row['idrole']; ?>">Edit</button>
                                <a href="index.php?page=role&aksi=hapus&id=<?= $row['idrole']; ?>" class="btn btn-danger btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>

                        <!-- MODAL EDIT ROLE -->
                        <div class="modal fade" id="modalEditRole<?= $row['idrole']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form action="index.php?page=role" method="POST">
                                        <div class="modal-header border-0 pb-0 px-4 pt-4">
                                            <h5 class="fw-bold text-dark">Edit Data Role</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-3">
                                            <input type="hidden" name="aksi" value="update">
                                            <input type="hidden" name="idrole" value="<?= $row['idrole']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Nama Role</label>
                                                <input type="text" class="form-control rounded-3 py-2" name="nama_role" value="<?= htmlspecialchars($row['nama_role']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 px-4 pb-4">
                                            <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data role.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=role" method="POST">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="fw-bold text-dark">Tambah Role Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nama Role</label>
                        <input type="text" class="form-control rounded-3 py-2" name="nama_role" placeholder="Contoh: Admin, Kasir" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">+ Tambah Role</button>
                </div>
            </form>
        </div>
    </div>
</div>