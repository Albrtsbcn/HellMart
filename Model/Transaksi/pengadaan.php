<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$query = "SELECT pengadaan.*, vendor.nama_vendor, user.username 
          FROM pengadaan 
          LEFT JOIN vendor ON pengadaan.vendor_idvendor = vendor.idvendor 
          LEFT JOIN user ON pengadaan.user_iduser = user.iduser 
          ORDER BY pengadaan.idpengadaan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid px-0">
    <!-- Header Halaman -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Pengadaan</h3>
                <p class="text-muted mb-0">Kelola catatan pengadaan barang dari vendor.</p>
            </div>
            <a href="index.php?page=tambah_pengadaan" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm">
                + Tambah Pengadaan
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID Pengadaan</th>
                        <th class="py-3">Waktu / Tanggal</th>
                        <th class="py-3">Vendor</th>
                        <th class="py-3">Petugas (User)</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $data_pengadaan = [];
                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $data_pengadaan[] = $row;
                        }
                    }

                    if (!empty($data_pengadaan)):
                        foreach($data_pengadaan as $row): 
                            $idp = $row['idpengadaan'];
                            
                            $q_detail = mysqli_query($conn, "SELECT detail_pengadaan.*, barang.nama AS nama_barang 
                                                             FROM detail_pengadaan 
                                                             JOIN barang ON detail_pengadaan.idbarang = barang.idbarang 
                                                             WHERE detail_pengadaan.idpengadaan = $idp");
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $idp; ?></td>
                            <td><?= htmlspecialchars($row['timestamp'] ?? '-'); ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_vendor'] ?? '-'); ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-success fw-bold px-3 py-1 rounded-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $idp; ?>">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data pengadaan barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($data_pengadaan)): ?>
    <?php foreach($data_pengadaan as $row): 
        $idp = $row['idpengadaan'];
        $q_detail = mysqli_query($conn, "SELECT detail_pengadaan.*, barang.nama AS nama_barang 
                                         FROM detail_pengadaan 
                                         JOIN barang ON detail_pengadaan.idbarang = barang.idbarang 
                                         WHERE detail_pengadaan.idpengadaan = $idp");
    ?>
        <div class="modal fade" id="modalDetail<?= $idp; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <h5 class="fw-bold text-dark">Rincian Detail Pengadaan <?= $idp; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="row mb-3 bg-light p-3 rounded-3 mx-0">
                            <div class="col-md-6 small"><strong>Vendor:</strong> <?= htmlspecialchars($row['nama_vendor'] ?? '-'); ?></div>
                            <div class="col-md-6 small"><strong>Tanggal:</strong> <?= htmlspecialchars($row['timestamp'] ?? '-'); ?></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light text-uppercase fs-7 text-secondary">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Harga Satuan</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    $grand_total = 0;
                                    if ($q_detail && mysqli_num_rows($q_detail) > 0):
                                        while($item = mysqli_fetch_assoc($q_detail)): 
                                            $subtotal = $item['harga_satuan'] * $item['jumlah'];
                                            $grand_total += $subtotal;
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($item['nama_barang']); ?></td>
                                            <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
                                            <td><?= number_format($item['jumlah'], 0, ',', '.'); ?></td>
                                            <td class="fw-semibold">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="5" class="text-center py-3 text-muted">Tidak ada rincian barang.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end fw-bold fs-6 mt-3">
                            <span class="text-success">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-3 px-4 py-2 fw-semibold" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>