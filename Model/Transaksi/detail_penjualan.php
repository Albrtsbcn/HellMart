<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$idpenjualan = isset($_GET['id']) ? intval($_GET['id']) : 0;

$q_penjualan = mysqli_query($conn, "SELECT p.*, u.username, m.persen AS margin_persen 
                                      FROM penjualan p 
                                      JOIN user u ON p.iduser = u.iduser 
                                      JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan 
                                      WHERE p.idpenjualan = $idpenjualan");
$data_p = mysqli_fetch_assoc($q_penjualan);

if (!$data_p) {
    echo "<script>alert('Data transaksi penjualan tidak ditemukan!'); window.location='index.php?page=penjualan';</script>";
    exit();
}

$q_detail = mysqli_query($conn, "SELECT dp.*, b.nama AS nama_barang 
                                   FROM detail_penjualan dp 
                                   JOIN barang b ON dp.idbarang = b.idbarang 
                                   WHERE dp.penjualan_idpenjualan = $idpenjualan");
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Detail Transaksi Penjualan <?= $data_p['idpenjualan']; ?></h3>
                <p class="text-muted mb-0">Rincian informasi produk dan kalkulasi pembayaran.</p>
            </div>
            <a href="index.php?page=penjualan" class="btn btn-secondary fw-semibold px-4 py-2 rounded-3 shadow-sm">
                ← Kembali
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="row g-3">
                    <div class="col-md-3">
                        <span class="text-muted small d-block">Waktu Transaksi</span>
                        <strong class="text-dark"><?= $data_p['created_at']; ?></strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small d-block">Kasir / User</span>
                        <strong class="text-dark"><?= htmlspecialchars($data_p['username']); ?></strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small d-block">Margin Keuntungan</span>
                        <strong class="text-success"><?= $data_p['margin_persen']; ?>%</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small d-block">Total Akhir Nilai</span>
                        <strong class="text-success fs-5">Rp <?= number_format($data_p['total_nilai'], 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light border-0 py-3 px-4">
            <h5 class="fw-bold text-dark mb-0">Daftar Barang Terjual</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">No</th>
                        <th class="py-3">Nama Barang</th>
                        <th class="py-3">Harga Satuan</th>
                        <th class="py-3">Jumlah</th>
                        <th class="py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($q_detail && mysqli_num_rows($q_detail) > 0):
                        while($item = mysqli_fetch_assoc($q_detail)): 
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $no++; ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($item['nama_barang']); ?></td>
                            <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?= number_format($item['jumlah'], 0, ',', '.'); ?></td>
                            <td class="fw-semibold">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada rincian item barang pada transaksi ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-white border-0 p-4 text-end">
            <p class="mb-1 text-muted">Subtotal Nilai: <strong class="text-dark">Rp <?= number_format($data_p['subtotal_nilai'], 0, ',', '.'); ?></strong></p>
            <p class="mb-2 text-muted">PPN: <strong class="text-dark">Rp <?= number_format($data_p['ppn'], 0, ',', '.'); ?></strong></p>
            <h4 class="fw-bold text-success mb-1">Total Keseluruhan: Rp <?= number_format($data_p['total_nilai'], 0, ',', '.'); ?></h4>
        </div>
    </div>
</div>