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

$idpengadaan = intval($_GET['idpengadaan'] ?? 0);

$q_po = mysqli_query($conn, "SELECT p.*, v.nama_vendor FROM pengadaan p JOIN vendor v ON p.vendor_idvendor = v.idvendor WHERE p.idpengadaan = $idpengadaan");
$data_po = mysqli_fetch_assoc($q_po);

if (!$data_po) {
    echo "<script>alert('Data pengadaan tidak ditemukan!'); window.location='index.php?page=penerimaan';</script>";
    exit();
}

$q_detail = mysqli_query($conn, "
    SELECT dp.*, b.nama AS nama_barang, 
    COALESCE((
        SELECT SUM(dt.jumlah_terima) 
        FROM detail_penerimaan dt 
        JOIN penerimaan pener ON dt.idpenerimaan = pener.idpenerimaan 
        WHERE pener.idpengadaan = dp.idpengadaan AND dt.barang_idbarang = dp.idbarang
    ), 0) AS total_sudah_terima
    FROM detail_pengadaan dp
    JOIN barang b ON dp.idbarang = b.idbarang
    WHERE dp.idpengadaan = $idpengadaan
");

$rows_tersedia = [];
while ($item = mysqli_fetch_assoc($q_detail)) {
    $sisa_belum = $item['jumlah'] - $item['total_sudah_terima'];
    if ($sisa_belum > 0) {
        $item['sisa_belum'] = $sisa_belum;
        $rows_tersedia[] = $item;
    }
}
?>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-white text-dark fw-bold py-3 px-4 rounded-top-4">
        Konfirmasi Penerimaan Barang
    </div>
    <div class="card-body p-4">
        <form action="index.php?page=proses_konfirmasi_penerimaan" method="POST">
            <input type="hidden" name="idpengadaan" value="<?= $idpengadaan; ?>">

            <h5 class="fw-bold mb-3">Rincian Kuantitas & Harga Barang Diterima</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle" id="tabel-konfirmasi-penerimaan">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th>Nama Barang</th>
                            <th class="text-center">Jumlah Dipesan</th>
                            <th class="text-center">Sudah Diterima</th>
                            <th class="text-center">Sisa Belum Diterima</th>
                            <th width="18%">Jumlah Diterima Sekarang</th>
                            <th width="22%">Harga Satuan Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows_tersedia)): ?>
                            <?php foreach ($rows_tersedia as $item): ?>
                                <tr class="row-penerimaan">
                                    <td class="fw-bold"><?= htmlspecialchars($item['nama_barang']); ?></td>
                                    <td class="text-center"><?= number_format($item['jumlah'], 0, ',', '.'); ?></td>
                                    <td class="text-center text-success fw-bold"><?= number_format($item['total_sudah_terima'], 0, ',', '.'); ?></td>
                                    <td class="text-center text-danger fw-bold"><?= number_format($item['sisa_belum'], 0, ',', '.'); ?></td>
                                    <td>
                                        <input type="hidden" name="idbarang[]" value="<?= $item['idbarang']; ?>">
                                        <input type="number" name="jumlah_terima[]" class="form-control rounded-3 input-qty-terima" 
                                               min="0" max="<?= $item['sisa_belum']; ?>" value="0" required>
                                        <small class="text-muted">Maksimal: <?= $item['sisa_belum']; ?></small>
                                    </td>
                                    <td>
                                        <input type="number" name="harga_satuan_terima[]" class="form-control rounded-3 input-harga-terima" 
                                               min="0" step="any" value="" placeholder="0"
                                               data-harga-default="<?= floatval($item['harga_satuan']); ?>">
                                        <small class="text-muted">Harga Sebelumnya: Rp <?= number_format($item['harga_satuan'], 0, ',', '.'); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Semua barang pada pengadaan ini sudah diterima penuh.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($rows_tersedia)): ?>
                <div class="d-flex justify-content-end align-items-center bg-light border rounded-3 px-4 py-3 mb-4">
                    <div class="text-end">
                        <div class="text-muted small fw-semibold">Total Harga Barang Diterima</div>
                        <div class="fs-4 fw-bold text-success" id="total-harga-penerimaan">Rp 0</div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between">
                <a href="index.php?page=penerimaan" class="btn btn-dark rounded-3 px-4 py-2 fw-semibold">Kembali</a>
                <?php if (!empty($rows_tersedia)): ?>
                    <button type="submit" name="simpan_penerimaan" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">Simpan Penerimaan</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const rows = document.querySelectorAll('#tabel-konfirmasi-penerimaan tbody tr.row-penerimaan');
    const totalEl = document.getElementById('total-harga-penerimaan');

    function formatRupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function hitungTotalPenerimaan() {
        let grandTotal = 0;

        rows.forEach(function(row) {
            const qtyInput = row.querySelector('.input-qty-terima');
            const hargaInput = row.querySelector('.input-harga-terima');

            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const hargaDiisi = hargaInput && hargaInput.value !== '' ? parseFloat(hargaInput.value) : NaN;
            const hargaDefault = hargaInput ? parseFloat(hargaInput.getAttribute('data-harga-default') || 0) : 0;

            const hargaEfektif = !isNaN(hargaDiisi) && hargaDiisi > 0 ? hargaDiisi : hargaDefault;
            if (qty > 0) {
                grandTotal += qty * hargaEfektif;
            }
        });

        if (totalEl) {
            totalEl.textContent = formatRupiah(grandTotal);
        }
    }

    rows.forEach(function(row) {
        const qtyInput = row.querySelector('.input-qty-terima');
        const hargaInput = row.querySelector('.input-harga-terima');

        if (qtyInput) {
            qtyInput.addEventListener('input', hitungTotalPenerimaan);
            qtyInput.addEventListener('change', hitungTotalPenerimaan);
        }
        if (hargaInput) {
            hargaInput.addEventListener('input', hitungTotalPenerimaan);
            hargaInput.addEventListener('change', hitungTotalPenerimaan);
        }
    });

    hitungTotalPenerimaan();
})();
</script>