<?php
require_once __DIR__ . '/../dbconnection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new DBconnection();
$conn = $db->get_connection();

$role_aktif = $_SESSION['role'] ?? '';

if (!empty($_SESSION['iduser'])) {
    $iduser_aktif = intval($_SESSION['iduser']);
    $q_role = mysqli_query($conn, "
        SELECT r.*
        FROM user u
        LEFT JOIN role r ON u.idrole = r.idrole
        WHERE u.iduser = $iduser_aktif
        LIMIT 1
    ");
    if ($q_role && $row_role = mysqli_fetch_assoc($q_role)) {
        $role_aktif = $row_role['nama_role'] ?? $row_role['role'] ?? $row_role['nama'] ?? $role_aktif;
        $_SESSION['role'] = $role_aktif;
    }
}

$is_admin = (strtolower(trim((string)$role_aktif)) === 'administrator');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    if (!$is_admin) {
        echo "<script>alert('Akses ditolak! Hanya Administrator yang dapat menambah atau mengubah margin penjualan.'); window.location='index.php?page=margin';</script>";
        exit();
    }

    $persen = floatval($_POST['persen']);
    
    mysqli_begin_transaction($conn);
    try {
        if ($_POST['aksi'] == 'tambah') {
            $cek = mysqli_query($conn, "SELECT COUNT(*) as total FROM margin_penjualan");
            $data_cek = mysqli_fetch_assoc($cek);
            $status_baru = ($data_cek['total'] == 0) ? 1 : 0;

            if (!mysqli_query($conn, "INSERT INTO margin_penjualan (persen, status, created_at, updated_at) VALUES ($persen, $status_baru, NOW(), NOW())")) {
                throw new Exception(mysqli_error($conn));
            }
        } elseif ($_POST['aksi'] == 'update') {
            $id = intval($_POST['idmargin_penjualan']);
            if (!mysqli_query($conn, "UPDATE margin_penjualan SET persen = $persen, updated_at = NOW() WHERE idmargin_penjualan = $id")) {
                throw new Exception(mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
    }

    echo "<script>window.location='index.php?page=margin';</script>";
    exit();
}

if (isset($_GET['aksi'])) {
    if (!$is_admin) {
        echo "<script>alert('Akses ditolak! Hanya Administrator yang dapat mengubah status atau menghapus margin penjualan.'); window.location='index.php?page=margin';</script>";
        exit();
    }

    if ($_GET['aksi'] == 'hapus') {
        $id = intval($_GET['id']);
        mysqli_query($conn, "DELETE FROM margin_penjualan WHERE idmargin_penjualan = $id");
    } elseif ($_GET['aksi'] == 'aktifkan') {
        $id = intval($_GET['id']);
        mysqli_begin_transaction($conn);
        try {
            if (!mysqli_query($conn, "UPDATE margin_penjualan SET status = 0")) {
                throw new Exception(mysqli_error($conn));
            }
            if (!mysqli_query($conn, "UPDATE margin_penjualan SET status = 1 WHERE idmargin_penjualan = $id")) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
        }
    }
    echo "<script>window.location='index.php?page=margin';</script>";
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM margin_penjualan ORDER BY idmargin_penjualan DESC");
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Margin Penjualan</h3>
                <p class="text-muted mb-0">Kelola persentase keuntungan eceran.</p>
            </div>
            <?php if ($is_admin): ?>
                <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMargin">+ Tambah Margin</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3">Persen (%)</th>
                        <th class="py-3">Dibuat / Diubah</th>
                        <th class="py-3 text-center">Status</th>
                        <?php if ($is_admin): ?>
                            <th class="py-3 text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_data = false;
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $has_data = true;
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['idmargin_penjualan']; ?></td>
                            <td class="fw-bold text-success"><?= $row['persen']; ?>%</td>
                            <td class="small text-muted"><?= $row['updated_at'] ?? $row['created_at']; ?></td>
                            <td class="text-center">
                                <?php if ($row['status'] == 1): ?>
                                    <span class="badge bg-success text-white px-3 py-1 rounded-2">Aktif</span>
                                <?php else: ?>
                                    <?php if ($is_admin): ?>
                                        <a href="index.php?page=margin&aksi=aktifkan&id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-primary btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm">Aktifkan</a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-2">Nonaktif</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($is_admin): ?>
                                    <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3 py-1 rounded-2 shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditMargin<?= $row['idmargin_penjualan']; ?>">Edit</button>
                                    <a href="index.php?page=margin&aksi=hapus&id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-danger btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if ($is_admin): ?>
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
                                        </div>
                                        <div class="modal-footer border-0 px-4 pb-4">
                                            <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endwhile; endif; ?>

                    <?php if (!$has_data): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data margin penjualan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
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
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">+ Tambah Margin</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>