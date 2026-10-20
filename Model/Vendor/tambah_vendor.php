<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if (isset($_POST['submit'])) {
    $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
    $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum']);
    $status = $_POST['status'];
    mysqli_query($conn, "INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES ('$nama_vendor', '$badan_hukum', '$status')");
    header("Location: ../index.php?page=vendor");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Tambah Vendor</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container my-5 col-md-6"><div class="card shadow-sm"><div class="card-header bg-dark text-white fw-bold">Tambah Vendor</div><div class="card-body">
<form method="POST">
    <div class="mb-3"><label class="form-label">Nama Vendor</label><input type="text" name="nama_vendor" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Badan Hukum</label><input type="text" name="badan_hukum" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-control"><option value="1">Aktif (1)</option><option value="0">Tidak Aktif (0)</option></select></div>
    <button type="submit" name="submit" class="btn btn-dark w-100">Simpan</button><a href="index.php?page=vendor" class="btn btn-secondary w-100 mt-2">Batal</a>
</form>
</div></div></div></body></html>