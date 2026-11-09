<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$idretur = isset($_GET['id']) ? intval($_GET['id']) : 0;

$q_header = mysqli_query($conn, "SELECT retur.*, user.username 
                                 FROM retur 
                                 LEFT JOIN user ON retur.iduser = user.iduser 
                                 WHERE retur.idretur = $idretur");
$header = mysqli_fetch_assoc($q_header);

$q_detail = mysqli_query($conn, "SELECT detail_retur.*, barang.nama AS nama_barang, detail_penerimaan.idpenerimaan 
                                   FROM detail_retur 
                                   JOIN detail_penerimaan ON detail_retur.iddetail_penerimaan = detail_penerimaan.iddetail_penerimaan
                                   JOIN barang ON detail_penerimaan.barang_idbarang = barang.idbarang
                                   WHERE detail_retur.idretur = $idretur");
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Rincian Detail Retur <?= $idretur; ?></h3>
                <p class="text-muted mb-0">Daftar barang yang dikembalikan pada transaksi retur ini.</p>
            </div>
            <a href="index.php?page=retur" class="btn btn-light fw-semibold px-4 py-2 rounded-3 shadow-sm">&larr; Kembali</a>
        </div>

        <?php if ($header): ?>
            <div class="row mb-4 bg-light p-3 rounded-3">
                <div class="col-md-4"><strong>Waktu / Tanggal:</strong><br><?= htmlspecialchars($header['created_at']); ?></div>
                <div class="col-md-4"><strong>Petugas:</strong><br><?= htmlspecialchars($header['username'] ?? '-'); ?></div>
            </div>

            <h5 class="fw-bold text-dark mb-3">Daftar Barang Diretur:</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th>No</th>
                            <th>ID Penerimaan</th>
                            <th>Nama Barang</th>
                            <th>Jumlah Retur</th>
                            <th>Alasan Retur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($q_detail && mysqli_num_rows($q_detail) > 0):
                            while($item = mysqli_fetch_assoc($q_detail)): 
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>Penerimaan <?= $item['idpenerimaan']; ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($item['nama_barang']); ?></td>
                                <td><?= number_format($item['jumlah'], 0, ',', '.'); ?></td>
                                <td><?= htmlspecialchars($item['alasan']); ?></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Tidak ada rincian barang pada retur ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Data retur tidak ditemukan.</div>
        <?php endif; ?>
    </div>
</div>