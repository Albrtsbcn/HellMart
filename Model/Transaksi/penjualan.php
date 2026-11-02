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

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>Data Transaksi Penjualan</span>
        <a href="/HellMart/includes/index.php?page=tambah_penjualan" class="btn btn-sm btn-light fw-bold">+ Tambah Penjualan</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Margin (%)</th>
                    <th>Subtotal Nilai</th>
                    <th>PPN</th>
                    <th>Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <!-- Tambahkan class 'btn-detail' dan atribut 'data-id' agar bisa diklik -->
                    <tr class="btn-detail" data-id="<?= $row['idpenjualan']; ?>" style="cursor: pointer;" title="Klik untuk lihat detail">
                        <td><?= $row['idpenjualan']; ?></td>
                        <td><?= $row['created_at']; ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= $row['margin_persen']; ?>%</td>
                        <td>Rp <?= number_format($row['subtotal_nilai'], 0, ',', '.'); ?></td>
                        <td>Rp <?= number_format($row['ppn'], 0, ',', '.'); ?></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data transaksi penjualan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalDetailTransaksi" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalDetailLabel">Rincian Detail Transaksi Penjualan</h5>
        <button type="button" class="close text-white border-0 bg-transparent" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="isiDetailTransaksi">
        <p class="text-center text-muted my-3">Memuat data detail...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
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