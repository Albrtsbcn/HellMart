<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if (isset($_GET['terima_id'])) {
    $id_pengadaan = intval($_GET['terima_id']);
    $iduser = $_SESSION['iduser'] ?? 1; 

    mysqli_begin_transaction($conn);

    try {
        $query_penerimaan = "INSERT INTO penerimaan (created_at, status, idpengadaan, iduser) VALUES (NOW(), '1', '$id_pengadaan', '$iduser')";
        if (!mysqli_query($conn, $query_penerimaan)) {
            throw new Exception("Gagal menyimpan header penerimaan: " . mysqli_error($conn));
        }
        
        $idpenerimaan_baru = mysqli_insert_id($conn);

        $q_detail_po = mysqli_query($conn, "SELECT * FROM detail_pengadaan WHERE idpengadaan = '$id_pengadaan'");
        
        while ($item = mysqli_fetch_assoc($q_detail_po)) {
            $query_sp = "CALL sp_tambah_detail_penerimaan('$idpenerimaan_baru', '{$item['idbarang']}', '{$item['jumlah']}', '{$item['harga_satuan']}')";
            if (!mysqli_query($conn, $query_sp)) {
                throw new Exception("Gagal mengeksekusi SP detail penerimaan: " . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        echo "<script>alert('Pengadaan berhasil dikonfirmasi! Stok otomatis bertambah.'); window.location='index.php?page=penerimaan';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location='index.php?page=penerimaan';</script>";
    }
}

$result_pending = mysqli_query($conn, "SELECT p.*, v.nama_vendor, u.username FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor JOIN user u ON p.user_iduser = u.iduser WHERE p.idpengadaan NOT IN (SELECT idpengadaan FROM penerimaan) ORDER BY p.idpengadaan DESC");

$result_selesai = mysqli_query($conn, "SELECT p.*, u.username FROM penerimaan p JOIN user u ON p.iduser = u.iduser ORDER BY p.idpenerimaan DESC");
?>


<div class="container-fluid px-4">
    <h2 class="mt-4 fw-bold">Data Penerimaan Barang</h2>
    <p class="text-muted">Kelola catatan penerimaan barang dari pesanan pengadaan vendor.</p>

    <div class="card shadow-sm mb-4">
        <div class="card-header text-dark fw-bold">
            Daftar Pengadaan Menunggu Konfirmasi Penerimaan
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID PO</th>
                        <th>Tanggal Pengadaan</th>
                        <th>Vendor</th>
                        <th>Total Nilai</th>
                        <th>Admin PO</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($result_pending && mysqli_num_rows($result_pending) > 0):
                        while($row = mysqli_fetch_assoc($result_pending)): 
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= $row['idpengadaan']; ?></strong></td>
                            <td><?= $row['timestamp']; ?></td>
                            <td><?= htmlspecialchars($row['nama_vendor']); ?></td>
                            <td>Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td>
                                <a href="index.php?page=penerimaan&terima_id=<?= $row['idpengadaan']; ?>" 
                                   class="btn btn-sm btn-dark fw-bold"
                                   onclick="return confirm('Konfirmasi pengadaan #<?= $row['idpengadaan']; ?>? Stok akan bertambah otomatis.')">
                                    Konfirmasi Terima
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada pengadaan baru yang perlu dikonfirmasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header text-dar fw-bold">
            Daftar Riwayat Penerimaan Barang
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID Penerimaan</th>
                        <th>Tanggal / Waktu</th>
                        <th>ID Pengadaan (PO)</th>
                        <th>Petugas (User)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_selesai && mysqli_num_rows($result_selesai) > 0):
                        while($selesai = mysqli_fetch_assoc($result_selesai)): 
                    ?>
                        <tr>
                            <td><?= $selesai['idpenerimaan']; ?></td>
                            <td><?= $selesai['created_at']; ?></td>
                            <td><strong><?= $selesai['idpengadaan']; ?></strong></td>
                            <td><?= htmlspecialchars($selesai['username']); ?></td>
                            <td><span class="badge bg-success text-white">Selesai</span></td>
                        </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada riwayat penerimaan barang.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>