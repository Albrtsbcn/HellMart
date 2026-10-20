<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM barang WHERE idbarang = '$id'");
$row = mysqli_fetch_assoc($data);

if (isset($_POST['submit'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = $_POST['harga'];
    $idsatuan = $_POST['idsatuan'];
    $status = $_POST['status'];

    $query = "UPDATE barang SET jenis='$jenis', nama='$nama', harga='$harga', idsatuan='$idsatuan', status='$status' WHERE idbarang='$id'";
    if (mysqli_query($conn, $query)) {
        header("Location: index.php?page=barang");
        exit;
    }
}
$satuan_result = mysqli_query($conn, "SELECT * FROM satuan WHERE status=1");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Update Barang</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container my-5 col-md-6"><div class="card shadow-sm"><div class="card-header bg-dark text-white fw-bold">Update Barang</div><div class="card-body">
<form method="POST">
    <div class="mb-3"><label class="form-label">Jenis (P/O/B/A)</label><input type="text" name="jenis" class="form-control" value="<?= $row['jenis']; ?>" maxlength="1" required></div>
    <div class="mb-3"><label class="form-label">Nama Barang</label><input type="text" name="nama" class="form-control" value="<?= $row['nama']; ?>" required></div>
    <div class="mb-3"><label class="form-label">Harga</label><input type="number" name="harga" class="form-control" value="<?= $row['harga']; ?>" required></div>
    <div class="mb-3"><label class="form-label">Satuan</label>
        <select name="idsatuan" class="form-control" required>
            <?php while($s = mysqli_fetch_assoc($satuan_result)): ?>
                <option value="<?= $s['idsatuan']; ?>" <?= ($s['idsatuan'] == $row['idsatuan']) ? 'selected' : ''; ?>><?= $s['nama_satuan']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-control"><option value="1" <?= ($row['status'] == 1) ? 'selected' : ''; ?>>Aktif (1)</option><option value="0" <?= ($row['status'] == 0) ? 'selected' : ''; ?>>Tidak Aktif (0)</option></select></div>
    <button type="submit" name="submit" class="btn btn-dark w-100">Simpan Perubahan</button><a href="index.php?page=barang" class="btn btn-secondary w-100 mt-2">Batal</a>
</form>
</div></div></div></body></html>