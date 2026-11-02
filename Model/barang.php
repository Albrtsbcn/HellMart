<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $idsatuan = intval($_POST['idsatuan']);
    $harga = intval($_POST['harga']);
    $status = intval($_POST['status']);
    
    if ($_POST['aksi'] == 'tambah') {
        mysqli_query($conn, "INSERT INTO barang (jenis, nama, idsatuan, harga, status) VALUES ('$jenis', '$nama', $idsatuan, $harga, $status)");
    } elseif ($_POST['aksi'] == 'update') {
        $id = intval($_POST['idbarang']);
        mysqli_query($conn, "UPDATE barang SET jenis='$jenis', nama='$nama', idsatuan=$idsatuan, harga=$harga, status=$status WHERE idbarang=$id");
    }
    header("Location: index.php?page=barang");
    exit();
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM barang WHERE idbarang = $id");
    header("Location: index.php?page=barang");
    exit();
}

$result = mysqli_query($conn, "SELECT barang.*, satuan.nama_satuan FROM barang LEFT JOIN satuan ON barang.idsatuan = satuan.idsatuan");
$satuan_res = mysqli_query($conn, "SELECT * FROM satuan");
$data_satuan = mysqli_fetch_all($satuan_res, MYSQLI_ASSOC);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Barang</h3>
                <p class="text-muted mb-0">Kelola master katalog komoditas pertanian sesuai struktur CDM.</p>
            </div>
            <button type="button" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                + Tambah Barang
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3">Jenis</th>
                        <th class="py-3">Nama</th>
                        <th class="py-3">Harga</th>
                        <th class="py-3">Satuan</th>
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
                            <td class="px-4 fw-bold text-muted"><?= $row['idbarang']; ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary px-2 py-1"><?= htmlspecialchars($row['jenis']); ?></span></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama']); ?></td>
                            <td class="fw-semibold">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($row['nama_satuan'] ?? '-'); ?></td>
                            <td><span class="badge bg-success-subtle text-success px-2 py-1"><?= $row['status']; ?></span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3 py-1 rounded-2 shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditBarang<?= $row['idbarang']; ?>">Edit</button>
                                <a href="index.php?page=barang&aksi=hapus&id=<?= $row['idbarang']; ?>" class="btn btn-danger btn-sm fw-bold px-3 py-1 rounded-2 shadow-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>

                        <!-- MODAL EDIT BARANG -->
                        <div class="modal fade" id="modalEditBarang<?= $row['idbarang']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form action="index.php?page=barang" method="POST">
                                        <div class="modal-header border-0 pb-0 px-4 pt-4">
                                            <h5 class="fw-bold text-dark">Edit Data Barang</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-3">
                                            <input type="hidden" name="aksi" value="update">
                                            <input type="hidden" name="idbarang" value="<?= $row['idbarang']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Jenis</label>
                                                <input type="text" class="form-control rounded-3 py-2" name="jenis" value="<?= htmlspecialchars($row['jenis']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Nama Barang</label>
                                                <input type="text" class="form-control rounded-3 py-2" name="nama" value="<?= htmlspecialchars($row['nama']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Satuan</label>
                                                <select class="form-select rounded-3 py-2" name="idsatuan" required>
                                                    <?php foreach ($data_satuan as $sat): ?>
                                                        <option value="<?= $sat['idsatuan']; ?>" <?= ($sat['idsatuan'] == $row['idsatuan']) ? 'selected' : ''; ?>><?= $sat['nama_satuan']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Harga</label>
                                                <input type="number" class="form-control rounded-3 py-2" name="harga" value="<?= $row['harga']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary small">Status</label>
                                                <input type="number" class="form-control rounded-3 py-2" name="status" value="<?= $row['status']; ?>" required>
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
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH BARANG -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=barang" method="POST">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="fw-bold text-dark">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Jenis</label>
                        <input type="text" class="form-control rounded-3 py-2" name="jenis" placeholder="Contoh: A / B" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nama Barang</label>
                        <input type="text" class="form-control rounded-3 py-2" name="nama" placeholder="Nama produk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Satuan</label>
                        <select class="form-select rounded-3 py-2" name="idsatuan" required>
                            <option value="">-- Pilih Satuan --</option>
                            <?php foreach ($data_satuan as $sat): ?>
                                <option value="<?= $sat['idsatuan']; ?>"><?= $sat['nama_satuan']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Harga</label>
                        <input type="number" class="form-control rounded-3 py-2" name="harga" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Status</label>
                        <input type="number" class="form-control rounded-3 py-2" name="status" value="1" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">+ Tambah Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>