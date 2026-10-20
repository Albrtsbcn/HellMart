<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM user WHERE iduser = '$id'");
$row = mysqli_fetch_assoc($data);

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $idrole = $_POST['idrole'];
    mysqli_query($conn, "UPDATE user SET username='$username', password='$password', idrole='$idrole' WHERE iduser='$id'");
    header("Location: index.php?page=user");
    exit;
}
$role_result = mysqli_query($conn, "SELECT * FROM role");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Update User</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container my-5 col-md-6"><div class="card shadow-sm"><div class="card-header bg-dark text-white fw-bold">Update User</div><div class="card-body">
<form method="POST">
    <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?= $row['username']; ?>" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" value="<?= $row['password']; ?>" required></div>
    <div class="mb-3"><label class="form-label">Role</label>
        <select name="idrole" class="form-control" required>
            <?php while($r = mysqli_fetch_assoc($role_result)): ?>
                <option value="<?= $r['idrole']; ?>" <?= ($r['idrole'] == $row['idrole']) ? 'selected' : ''; ?>><?= $r['nama_role']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit" name="submit" class="btn btn-dark w-100">Simpan Perubahan</button><a href="index.php?page=user" class="btn btn-secondary w-100 mt-2">Batal</a>
</form>
</div></div></div></body></html>