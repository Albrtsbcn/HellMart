<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
    $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum']);
    $status = intval($_POST['status']);
    
    if ($_POST['aksi'] == 'tambah') {
        mysqli_query($conn, "INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES ('$nama_vendor', '$badan_hukum', $status)");
    } elseif ($_POST['aksi'] == 'update') {
        $id = intval($_POST['idvendor']);
        mysqli_query($conn, "UPDATE vendor SET nama_vendor='$nama_vendor', badan_hukum='$badan_hukum', status=$status WHERE idvendor=$id");
    }
    header("Location: index.php?page=vendor");
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM vendor WHERE idvendor = $id");
    header("Location: index.php?page=vendor");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM v_vendor");
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Vendor</h3>
                <p class="text-muted mb-0">Kelola mitra distributor dan pabrik penyedia barang.</p>
            </div>
            <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahVendor">+ Tambah Vendor</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3">Nama Vendor</th>
                        <th class="py-3">Badan Hukum</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['idvendor']; ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_vendor']); ?></td>
                            <td><?= htmlspecialchars($row['badan_hukum'] ?? '-'); ?></td>
                            <td><span class="badge bg-success-subtle text-success px-2 py-1"><?= $row['status'] ?? '1'; ?></span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3 py-1 rounded-2 shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditVendor<?= $row['idvendor']; ?>">Edit</button>
                                <a href="index.php?page=vendor&aksi=hapus&id=<?= $row['idvendor']; ?>" class="btn btn-danger btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEditVendor<?= $row['idvendor']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form action="index.php?page=vendor" method="POST">
                                        <div class="modal-header border-0 pb-0 px-4 pt-4">
                                            <h5 class="fw-bold text-dark">Edit Data Vendor</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-3">
                                            <input type="hidden" name="aksi" value="update">
                                            <input type="hidden" name="idvendor" value="<?= $row['idvendor']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Nama Vendor</label>
                                                <input type="text" class="form-control rounded-3 py-2" name="nama_vendor" value="<?= htmlspecialchars($row['nama_vendor']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Badan Hukum</label>
                                                <input type="text" class="form-control rounded-3 py-2" name="badan_hukum" value="<?= htmlspecialchars($row['badan_hukum']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Status</label>
                                                <input type="number" class="form-control rounded-3 py-2" name="status" value="<?= $row['status'] ?? 1; ?>" required>
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
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data vendor.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahVendor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=vendor" method="POST">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="fw-bold text-dark">Tambah Vendor Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nama Vendor</label>
                        <input type="text" class="form-control rounded-3 py-2" name="nama_vendor" placeholder="Nama perusahaan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Badan Hukum</label>
                        <input type="text" class="form-control rounded-3 py-2" name="badan_hukum" placeholder="Contoh: PT / CV" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Status</label>
                        <input type="number" class="form-control rounded-3 py-2" name="status" value="1" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">+ Tambah Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>