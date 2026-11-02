<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $idpenerimaan = intval($_POST['idpenerimaan']);
    $iduser = intval($_POST['iduser']);
    
    mysqli_query($conn, "INSERT INTO retur (idpenerimaan, iduser) VALUES ($idpenerimaan, $iduser)");
    header("Location: index.php?page=retur");
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM retur WHERE idretur = $id");
    header("Location: index.php?page=retur");
    exit();
}

$result = mysqli_query($conn, "SELECT retur.*, penerimaan.idpenerimaan, user.username 
FROM retur 
LEFT JOIN penerimaan ON retur.idpenerimaan = penerimaan.idpenerimaan 
LEFT JOIN user ON retur.iduser = user.iduser");

$penerimaan_res = mysqli_query($conn, "SELECT * FROM penerimaan");
$data_penerimaan = mysqli_fetch_all($penerimaan_res, MYSQLI_ASSOC);

$userData_res = mysqli_query($conn, "SELECT * FROM user");
$data_user = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM user"), MYSQLI_ASSOC);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Retur Barang</h3>
                <p class="text-muted mb-0">Kelola catatan pengembalian barang dari proses penerimaan sesuai struktur CDM.</p>
            </div>
            <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRetur">
                + Tambah Retur
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID Retur</th>
                        <th class="py-3">Waktu / Tanggal</th>
                        <th class="py-3">ID Penerimaan</th>
                        <th class="py-3">Petugas (User)</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['idretur']; ?></td>
                            <td><?= htmlspecialchars($row['created_at'] ?? '-'); ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary px-2 py-1">Penerimaan #<?= $row['idpenerimaan']; ?></span></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                            <td class="text-center">
                                <a href="index.php?page=retur&aksi=hapus&id=<?= $row['idretur']; ?>" class="btn btn-danger btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm" onclick="return confirm('Yakin ingin menghapus data retur ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data retur barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahRetur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=retur" method="POST">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="fw-bold text-dark">Tambah Retur Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Referensi Penerimaan</label>
                        <select class="form-select rounded-3 py-2" name="idpenerimaan" required>
                            <option value="">-- Pilih ID Penerimaan --</option>
                            <?php foreach ($data_penerimaan as $pen): ?>
                                <option value="<?= $pen['idpenerimaan']; ?>">Penerimaan #<?= $pen['idpenerimaan']; ?> (PO: <?= $pen['idpengadaan']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Petugas / User</label>
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
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">+ Simpan Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>