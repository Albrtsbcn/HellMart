<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$idpenerimaan = intval($_GET['id'] ?? 0);

$query_sql = "
    SELECT 
        dp.iddetail_penerimaan,
        dp.idpenerimaan,
        dp.barang_idbarang,
        b.nama AS nama_barang,
        dp.jumlah_terima,
        dp.harga_satuan_terima,
        dp.sub_total_terima,
        p.created_at,
        p.idpengadaan,
        u.username
    FROM detail_penerimaan dp
    JOIN barang b ON dp.barang_idbarang = b.idbarang
    JOIN penerimaan p ON dp.idpenerimaan = p.idpenerimaan
    JOIN user u ON p.iduser = u.iduser
    WHERE dp.idpenerimaan = $idpenerimaan
";

$result_detail = mysqli_query($conn, $query_sql);

$items = [];
$header = null;
if ($result_detail && mysqli_num_rows($result_detail) > 0) {
    while ($row = mysqli_fetch_assoc($result_detail)) {
        if (!$header) {
            $header = $row;
        }
        $items[] = $row;
    }
}

if (!$header) {
    echo "<script>alert('Detail penerimaan tidak ditemukan!'); window.location='index.php?page=penerimaan';</script>";
    exit();
}
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Rincian Detail Penerimaan #<?= $idpenerimaan; ?></h3>
                <p class="text-muted mb-0">Informasi lengkap barang yang diterima dari PO #<?= $header['idpengadaan']; ?>.</p>
            </div>
            <a href="index.php?page=penerimaan" class="btn btn-dark fw-bold px-4 py-2 rounded-3 shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-bold">Waktu Diterima</small>
                <h6 class="fw-bold text-dark mt-1 mb-0"><?= $header['created_at']; ?></h6>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-bold">Referensi PO</small>
                <h6 class="fw-bold text-dark mt-1 mb-0">#<?= $header['idpengadaan']; ?></h6>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-bold">Petugas Penerima</small>
                <h6 class="fw-bold text-dark mt-1 mb-0"><?= htmlspecialchars($header['username']); ?></h6>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-dark text-white fw-bold py-3 px-4">
            Daftar Barang Diterima
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">No</th>
                        <th class="py-3">Nama Barang</th>
                        <th class="py-3 text-center">Jumlah Diterima</th>
                        <th class="py-3">Harga Satuan</th>
                        <th class="py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $grand_total = 0;
                    foreach ($items as $item): 
                        $subtotal = $item['jumlah_terima'] * $item['harga_satuan_terima'];
                        $grand_total += $subtotal;
                    ?>
                        <tr>
                            <td class="px-4 text-muted"><?= $no++; ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($item['nama_barang']); ?></td>
                            <td class="text-center fw-semibold text-success"><?= number_format($item['jumlah_terima'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($item['harga_satuan_terima'], 0, ',', '.'); ?></td>
                            <td class="fw-bold text-dark">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light p-4 text-end">
            <h5 class="fw-bold text-dark mb-0">Total Nilai Diterima: <span class="text-success">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span></h5>
        </div>
    </div>
</div>