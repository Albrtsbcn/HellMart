<?php
session_start();
require_once __DIR__ . '/../dbconnection.php'; 
$db = new DBconnection();
$conn = $db->get_connection();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM user WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if ($password == $user['password'] || password_verify($password, $user['password'])) {
            $_SESSION['iduser']   = $user['iduser'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] ?? 'Administrator';
            
            echo "<script>window.location='index.php?page=dashboard';</script>";
            exit();
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HellMart | Toko Pertanian</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        }
        .brand-header {
            background-color: #198754;
            color: white;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            padding: 1.75rem 1.5rem;
            text-align: center;
        }
        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }
        .btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card bg-white">
                    <div class="brand-header">
                        <h4 class="fw-bold mb-1">🌱 HellMart</h4>
                        <p class="text-white-50 small mb-0">Sistem Manajemen Toko Pertanian</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                                <?= $error; ?>
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Username</label>
                                <input type="text" name="username" class="form-control rounded-3 py-2" placeholder="Masukkan username" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold small text-secondary">Password</label>
                                <input type="password" name="password" class="form-control rounded-3 py-2" placeholder="Masukkan password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold py-2.5 rounded-3 shadow-sm">
                                Masuk 
                            </button>
                        </form>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4 text-muted small">
                        &copy; HellMart | Toko Pertanian
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>