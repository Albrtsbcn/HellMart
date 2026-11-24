<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role_aktif = $_SESSION['role'] ?? '';

if (!empty($_SESSION['iduser'])) {
    $iduser_aktif = intval($_SESSION['iduser']);
    $q_role = mysqli_query($conn, "
        SELECT r.*
        FROM user u
        LEFT JOIN role r ON u.idrole = r.idrole
        WHERE u.iduser = $iduser_aktif
        LIMIT 1
    ");
    if ($q_role && $row_role = mysqli_fetch_assoc($q_role)) {
        $role_aktif = $row_role['nama_role'] ?? $row_role['role'] ?? $row_role['nama'] ?? $role_aktif;
        $_SESSION['role'] = $role_aktif;
    }
}

$role_clean = strtolower(trim((string)$role_aktif));
$is_admin = ($role_clean === 'administrator');
$is_gudang = ($role_clean === 'gudang');
$can_proses_penerimaan = ($is_admin || $is_gudang);

if (!$can_proses_penerimaan) {
    echo "<script>alert('Akses ditolak! Hanya Gudang dan Administrator yang dapat memproses penerimaan barang.'); window.location='index.php?page=penerimaan';</script>";
    exit();
}

if (isset($_POST['simpan_penerimaan'])) {
    $idpengadaan        = intval($_POST['idpengadaan']);
    $iduser             = intval($_SESSION['iduser'] ?? 1); 
    
    $arr_idbarang       = $_POST['idbarang'] ?? [];
    $arr_jumlah_terima  = $_POST['jumlah_terima'] ?? [];
    $arr_harga_terima   = $_POST['harga_satuan_terima'] ?? ($_POST['harga_satuan'] ?? []);

    mysqli_begin_transaction($conn);

    try {
        $q_penerimaan = "INSERT INTO penerimaan (created_at, idpengadaan, iduser) VALUES (NOW(), $idpengadaan, $iduser)";
        if (!mysqli_query($conn, $q_penerimaan)) {
            throw new Exception("Gagal menyimpan header penerimaan: " . mysqli_error($conn));
        }
        $idpenerimaan_baru = intval(mysqli_insert_id($conn));

        $ada_barang_diterima = false;

        foreach ($arr_idbarang as $i => $idbarang) {
            $idbarang_clean = intval($idbarang);
            $jumlah_terima  = intval($arr_jumlah_terima[$i] ?? 0);
            $harga_terima   = floatval($arr_harga_terima[$i] ?? 0);

            if ($idbarang_clean <= 0 || $jumlah_terima <= 0) continue;

            $ada_barang_diterima = true;

            $q_sp = "CALL sp_tambah_detail_penerimaan($idpenerimaan_baru, $idbarang_clean, $jumlah_terima, $harga_terima)";
            if (!mysqli_query($conn, $q_sp)) {
                throw new Exception("Gagal mengeksekusi SP detail penerimaan: " . mysqli_error($conn));
            }
            while (mysqli_more_results($conn)) {
                mysqli_next_result($conn);
            }
        }

        if (!$ada_barang_diterima) {
            throw new Exception("Minimal harus ada 1 barang dengan jumlah diterima lebih dari 0.");
        }

        $q_cek_lunas = mysqli_query($conn, "
            SELECT 
                (SELECT SUM(jumlah) FROM detail_pengadaan WHERE idpengadaan = $idpengadaan) AS total_pesan,
                (SELECT SUM(dt.jumlah_terima) FROM detail_penerimaan dt JOIN penerimaan p ON dt.idpenerimaan = p.idpenerimaan WHERE p.idpengadaan = $idpengadaan) AS total_terima
        ");
        $d_cek = mysqli_fetch_assoc($q_cek_lunas);

        if (($d_cek['total_terima'] ?? 0) >= ($d_cek['total_pesan'] ?? 0)) {
            mysqli_query($conn, "UPDATE pengadaan SET status = '1' WHERE idpengadaan = $idpengadaan");
        } else {
            mysqli_query($conn, "UPDATE pengadaan SET status = '0' WHERE idpengadaan = $idpengadaan");
        }

        mysqli_commit($conn);
        echo "<script>alert('Penerimaan barang berhasil dikonfirmasi! Stok dan harga master terbarui.'); window.location='index.php?page=penerimaan';</script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location='index.php?page=konfirmasi_penerimaan&idpengadaan=$idpengadaan';</script>";
        exit();
    }
}
?>