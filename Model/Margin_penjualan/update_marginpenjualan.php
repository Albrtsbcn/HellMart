<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM margin_penjualan WHERE idmargin_penjualan = '$id'");
$row = mysqli_fetch_assoc($data);

if (isset($_POST['submit'])) {
    $persen = $_POST['persen'];
    $status = $_POST['status'];
    $iduser = $_POST['iduser'];
    mysqli_query($conn, "UPDATE margin_penjualan SET persen='$persen', status='$status', iduser='$iduser' WHERE idmargin_penjualan='$id'");
    header("Location: index.php?page=margin");
    exit;
}
$user_result = mysqli_query($conn, "SELECT * FROM user");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Update Margin Penjualan</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container my-5 col-md-6"><div class="card shadow-sm"><div class="card-header bg-dark text-white fw-bold">Update Margin Penjualan</div><div class="card-body">
<form method="POST">
    <div class="mb-3"><label class="form-label">Persen (%)</label><input type="number" step="0.01" name="persen" class="form-control" value="<?= $row['persen']; ?>" required></div>
    <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-control"><option value="1" <?= ($row['status'] == 1) ? 'selected' : ''; ?>>Aktif (1)</option><option value="0" <?= ($row['status'] == 0) ? 'selected' : ''; ?>>Tidak Aktif (0)</option></select></div>
    <div class="mb-3"><label class="form-label">User</label>
        <select name="iduser" class="form-control" required>
            <?php while($u = mysqli_fetch_assoc($user_result)): ?>
                <option value="<?= $u['iduser']; ?>" <?= ($u['iduser'] == $row['iduser']) ? 'selected' : ''; ?>><?= $u['username']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit" name="submit" class="btn btn-dark w-100">Simpan Perubahan</button><a href="index.php?page=margin" class="btn btn-secondary w-100 mt-2">Batal</a>
</form>
</div></div></div></body></html>
