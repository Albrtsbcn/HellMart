<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$pengadaan_result = mysqli_query($conn, "SELECT pengadaan.*, vendor.nama_vendor FROM pengadaan JOIN vendor ON pengadaan.vendor_idvendor = vendor.idvendor");
$user_result = mysqli_query($conn, "SELECT * FROM user");

if (isset($_POST['simpan'])) {
    $idpengadaan = $_POST['idpengadaan'];
    $user_iduser = $_POST['user_iduser'];

    $q_detail_pengadaan = mysqli_query($conn, "SELECT * FROM detail_pengadaan WHERE idpengadaan = '$idpengadaan'");
    $d_pengadaan = mysqli_fetch_assoc($q_detail_pengadaan);
    
    $idbarang = $d_pengadaan['idbarang'];
    $jumlah_terima = $d_pengadaan['jumlah'];
    $harga_satuan_terima = $d_pengadaan['harga_satuan'];
    $sub_total_terima = $d_pengadaan['sub_total'];

    $query_insert_penerimaan = "INSERT INTO penerimaan (created_at, status, idpengadaan, iduser) 
                                VALUES (NOW(), '1', '$idpengadaan', '$user_iduser')";
    
    if (mysqli_query($conn, $query_insert_penerimaan)) {
        $idpenerimaan_baru = mysqli_insert_id($conn);

        $query_detail_penerimaan = "INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima) 
                                    VALUES ('$idpenerimaan_baru', '$idbarang', '$jumlah_terima', '$harga_satuan_terima', '$sub_total_terima')";
        mysqli_query($conn, $query_detail_penerimaan);

        echo "<script>alert('Penerimaan berhasil disetujui dan stok barang bertambah!'); window.location='/HellMart/includes/index.php?page=penerimaan';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menyimpan penerimaan: " . mysqli_error($conn) . "</div>";
    }
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">
        Persetujuan Penerimaan Barang dari Pengadaan
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Pengadaan</label>
                <select name="idpengadaan" class="form-select" required>
                    <option value="">-- Pilih Transaksi Pengadaan --</option>
                    <?php while($p = mysqli_fetch_assoc($pengadaan_result)): ?>
                        <option value="<?= $p['idpengadaan']; ?>">
                            ID Pengadaan: <?= $p['idpengadaan']; ?> | Vendor: <?= $p['nama_vendor']; ?> | Total: Rp <?= number_format($p['total_nilai'], 0, ',', '.'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">User / Admin Penerima</label>
                <select name="user_iduser" class="form-select" required>
                    <option value="">-- Pilih User --</option>
                    <?php while($u = mysqli_fetch_assoc($user_result)): ?>
                        <option value="<?= $u['iduser']; ?>"><?= $u['username']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <a href="/HellMart/includes/index.php?page=penerimaan" class="btn btn-secondary">Kembali</a>
                <button type="submit" name="simpan" class="btn btn-dark fw-bold">Setujui & Terima Barang</button>
            </div>
        </form>
    </div>
</div>