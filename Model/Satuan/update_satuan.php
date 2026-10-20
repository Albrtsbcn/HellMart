<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM satuan WHERE idsatuan = '$id'");
$row = mysqli_fetch_assoc($data);

if (isset($_POST['submit'])) {
    $nama_satuan = mysqli_real_escape_string($conn, $_POST['nama_satuan']);
    $status = $_POST['status'];
    mysqli_query($conn, "UPDATE satuan SET nama_satuan='$nama_satuan', status='$status' WHERE idsatuan='$id'");
    header("Location: index.php?page=satuan");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Update Satuan</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container my-5 col-md-6"><div class="card shadow-sm"><div class="card-header bg-dark text-white fw-bold">Update Satuan</div><div class="card-body">
<form method="POST">
    <div class="mb-3"><label class="form-label">Nama Satuan</label><input type="text" name="nama_satuan" class="form-control" value="<?= $row['nama_satuan']; ?>" required></div>
    <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-control"><option value="1" <?= ($row['status'] == 1) ? 'selected' : ''; ?>>Aktif (1)</option><option value="0" <?= ($row['status'] == 0) ? 'selected' : ''; ?>>Tidak Aktif (0)</option></select></div>
    <button type="submit" name="submit" class="btn btn-dark w-100">Simpan Perubahan</button><a href="index.php?page=satuan" class="btn btn-secondary w-100 mt-2">Batal</a>
</form>
</div></div></div></body></html>