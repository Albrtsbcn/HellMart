<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$query = "SELECT p.idpenjualan, p.created_at, p.subtotal_nilai, p.ppn, p.total_nilai, 
                 u.username, m.persen AS margin_persen 
          FROM penjualan p 
          JOIN user u ON p.iduser = u.iduser 
          JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan
          ORDER BY p.idpenjualan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header text-dark d-flex justify-content-between align-items-center py-3 px-4 rounded-top-4">
        <h5 class="mb-0 fw-bold">Data Transaksi Penjualan</h5>
        <a href="/HellMart/includes/index.php?page=tambah_penjualan" class="btn btn-success fw-bold px-3 py-2 rounded-3 shadow-sm">+ Tambah Penjualan</a>
    </div>
    <div class="card-body table-responsive p-4">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7 text-secondary">
                <tr>
                    <th class="py-3 px-3">ID</th>
                    <th class="py-3">Waktu</th>
                    <th class="py-3">User</th>
                    <th class="py-3">Margin (%)</th>
                    <th class="py-3">Subtotal Nilai</th>
                    <th class="py-3">PPN</th>
                    <th class="py-3">Total Nilai</th>
                    <th class="py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="px-3 fw-bold text-muted"><?= $row['idpenjualan']; ?></td>
                        <td><?= $row['created_at']; ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= $row['margin_persen']; ?>%</td>
                        <td>Rp <?= number_format($row['subtotal_nilai'], 0, ',', '.'); ?></td>
                        <td>Rp <?= number_format($row['ppn'], 0, ',', '.'); ?></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <a href="index.php?page=detail_penjualan&id=<?= $row['idpenjualan']; ?>" class="btn btn-success fw-bold px-3 py-1 rounded-2 shadow-sm">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada data transaksi penjualan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalDetailTransaksi" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header bg-dark text-white border-0 px-4 pt-4">
        <h5 class="modal-title fw-bold" id="modalDetailLabel">Rincian Detail Transaksi Penjualan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 py-3" id="isiDetailTransaksi">
        <p class="text-center text-muted my-3">Memuat data detail...</p>
      </div>
      <div class="modal-footer border-0 px-4 pb-4">
        <button type="button" class="btn btn-light rounded-3 px-4 py-2 fw-semibold" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $(document).on('click', '.btn-detail', function() {
        var idPenjualan = $(this).data('id');
        
        if (typeof $.fn.modal === 'function') {
            $('#modalDetailTransaksi').modal('show');
        } else {
            var myModal = new bootstrap.Modal(document.getElementById('modalDetailTransaksi'));
            myModal.show();
        }
        
        $('#isiDetailTransaksi').html('<p class="text-center text-muted my-3">Memuat data detail...</p>');
        
        $.ajax({
            url: '/HellMart/includes/get_detail_penjualan.php',
            type: 'GET',
            data: { id: idPenjualan },
            success: function(response) {
                $('#isiDetailTransaksi').html(response);
            },
            error: function() {
                $('#isiDetailTransaksi').html('<p class="text-danger text-center">Gagal memuat rincian transaksi dari server.</p>');
            }
        });
    });
});
</script>