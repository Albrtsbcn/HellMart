<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $idrole = intval($_POST['idrole']);
    
    if ($_POST['aksi'] == 'tambah') {
        mysqli_query($conn, "INSERT INTO user (username, password, idrole) VALUES ('$username', '$password', $idrole)");
    } elseif ($_POST['aksi'] == 'update') {
        $id = intval($_POST['iduser']);
        mysqli_query($conn, "UPDATE user SET username='$username', password='$password', idrole=$idrole WHERE iduser=$id");
    }
    header("Location: index.php?page=user");
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM user WHERE iduser = $id");
    header("Location: index.php?page=user");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM v_user");
$role_res = mysqli_query($conn, "SELECT * FROM role");
$data_role = mysqli_fetch_all($role_res, MYSQLI_ASSOC);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data User</h3>
                <p class="text-muted mb-0">Kelola akun administrator dan staf operasional.</p>
            </div>
            <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">+ Tambah User</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3">Username</th>
                        <th class="py-3">Password</th>
                        <th class="py-3">Role</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['iduser']; ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['username']); ?></td>
                            <td><span class="text-muted font-monospace">••••••••</span></td>
                            <td><span class="badge bg-primary-subtle text-primary px-2 py-1"><?= htmlspecialchars($row['nama_role'] ?? '-'); ?></span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3 py-1 rounded-2 shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= $row['iduser']; ?>">Edit</button>
                                <a href="index.php?page=user&aksi=hapus&id=<?= $row['iduser']; ?>" class="btn btn-danger btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>

                        <!-- MODAL EDIT USER -->
                        <div class="modal fade" id="modalEditUser<?= $row['iduser']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form action="index.php?page=user" method="POST">
                                        <div class="modal-header border-0 pb-0 px-4 pt-4">
                                            <h5 class="fw-bold text-dark">Edit Data User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-3">
                                            <input type="hidden" name="aksi" value="update">
                                            <input type="hidden" name="iduser" value="<?= $row['iduser']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Username</label>
                                                <input type="text" class="form-control rounded-3 py-2" name="username" value="<?= htmlspecialchars($row['username']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Password</label>
                                                <input type="text" class="form-control rounded-3 py-2" name="password" value="<?= htmlspecialchars($row['password']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Role</label>
                                                <select class="form-select rounded-3 py-2" name="idrole" required>
                                                    <?php foreach ($data_role as $rl): ?>
                                                        <option value="<?= $rl['idrole']; ?>" <?= ($rl['idrole'] == $row['idrole']) ? 'selected' : ''; ?>><?= $rl['nama_role']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
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
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data user.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=user" method="POST">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="fw-bold text-dark">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Username</label>
                        <input type="text" class="form-control rounded-3 py-2" name="username" placeholder="Username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Password</label>
                        <input type="password" class="form-control rounded-3 py-2" name="password" placeholder="Password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Role</label>
                        <select class="form-select rounded-3 py-2" name="idrole" required>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($data_role as $rl): ?>
                                <option value="<?= $rl['idrole']; ?>"><?= $rl['nama_role']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">+ Tambah User</button>
                </div>
            </form>
        </div>
    </div>
</div>