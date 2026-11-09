<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    $persen = floatval($_POST['persen']);
    $status = intval($_POST['status']);
    $iduser = intval($_POST['iduser']);
    
    if ($_POST['aksi'] == 'tambah') {
        mysqli_query($conn, "INSERT INTO margin_penjualan (persen, status, iduser, created_at, updated_at) VALUES ($persen, $status, $iduser, NOW(), NOW())");
    } elseif ($_POST['aksi'] == 'update') {
        $id = intval($_POST['idmargin_penjualan']);
        mysqli_query($conn, "UPDATE margin_penjualan SET persen=$persen, status=$status, iduser=$iduser, updated_at=NOW() WHERE idmargin_penjualan=$id");
    }
    header("Location: index.php?page=margin");
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM margin_penjualan WHERE idmargin_penjualan = $id");
    header("Location: index.php?page=margin");
    exit();
}
$result = mysqli_query($conn, "SELECT * FROM v_margin_penjualan");
$user_res = mysqli_query($conn, "SELECT * FROM user");
$data_user = mysqli_fetch_all($user_res, MYSQLI_ASSOC);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Margin Penjualan</h3>
                <p class="text-muted mb-0">Kelola persentase keuntungan eceran.</p>
            </div>
            <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMargin">+ Tambah Margin</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3">Persen (%)</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">User / Admin</th>
                        <th class="py-3">Dibuat / Diubah</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['idmargin_penjualan']; ?></td>
                            <td class="fw-bold text-success"><?= $row['persen']; ?>%</td>
                            <td><span class="badge bg-success-subtle text-success px-2 py-1"><?= $row['status']; ?></span></td>
                            <td><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                            <td class="small text-muted"><?= $row['created_at']; ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3 py-1 rounded-2 shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditMargin<?= $row['idmargin_penjualan']; ?>">Edit</button>
                                <a href="index.php?page=margin&aksi=hapus&id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-danger btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEditMargin<?= $row['idmargin_penjualan']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form action="index.php?page=margin" method="POST">
                                        <div class="modal-header border-0 pb-0 px-4 pt-4">
                                            <h5 class="fw-bold text-dark">Edit Data Margin Penjualan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-3">
                                            <input type="hidden" name="aksi" value="update">
                                            <input type="hidden" name="idmargin_penjualan" value="<?= $row['idmargin_penjualan']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Persen (%)</label>
                                                <input type="number" step="0.01" class="form-control rounded-3 py-2" name="persen" value="<?= $row['persen']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Status</label>
                                                <input type="number" class="form-control rounded-3 py-2" name="status" value="<?= $row['status']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">User / Admin</label>
                                                <select class="form-select rounded-3 py-2" name="iduser" required>
                                                    <?php foreach ($data_user as $usr): ?>
                                                        <option value="<?= $usr['iduser']; ?>" <?= ($usr['iduser'] == $row['iduser']) ? 'selected' : ''; ?>><?= $usr['username']; ?></option>
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
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data margin penjualan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahMargin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=margin" method="POST">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="fw-bold text-dark">Tambah Margin Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Persen (%)</label>
                        <input type="number" step="0.01" class="form-control rounded-3 py-2" name="persen" placeholder="Contoh: 10 atau 12.5" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Status</label>
                        <input type="number" class="form-control rounded-3 py-2" name="status" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">User / Admin</label>
                        <select class="form-select rounded-3 py-2" name="iduser" required>
                            <option value="">-- Pilih User --</option>
                            <?php foreach ($data_user as $usr): ?>
                                <option value="<?= $usr['iduser']; ?>"><?= $usr['username']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">+ Tambah Margin</button>
                </div>
            </form>
        </div>
    </div>
</div>