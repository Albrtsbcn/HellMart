<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM role WHERE idrole = '$id'");
$row = mysqli_fetch_assoc($data);

if (isset($_POST['submit'])) {
    $nama_role = mysqli_real_escape_string($conn, $_POST['nama_role']);
    mysqli_query($conn, "UPDATE role SET nama_role='$nama_role' WHERE idrole='$id'");
    header("Location: index.php?page=role");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Update Role</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container my-5 col-md-6"><div class="card shadow-sm"><div class="card-header bg-dark text-white fw-bold">Update Role</div><div class="card-body">
<form method="POST">
    <div class="mb-3"><label class="form-label">Nama Role</label><input type="text" name="nama_role" class="form-control" value="<?= $row['nama_role']; ?>" required></div>
    <button type="submit" name="submit" class="btn btn-dark w-100">Simpan Perubahan</button><a href="index.php?page=role" class="btn btn-secondary w-100 mt-2">Batal</a>
</form>
</div></div></div></body></html>