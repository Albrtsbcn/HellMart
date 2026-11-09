<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$query = "SELECT retur.*, user.username 
          FROM retur 
          LEFT JOIN user ON retur.iduser = user.iduser 
          ORDER BY retur.idretur DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Data Retur Barang</h3>
                <p class="text-muted mb-0">Kelola catatan pengembalian barang dari proses penerimaan.</p>
            </div>
            <a href="index.php?page=tambah_retur" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm">
                + Tambah Retur
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="py-3 px-4">ID Retur</th>
                        <th class="py-3">Waktu / Tanggal</th>
                        <th class="py-3">User</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted"><?= $row['idretur']; ?></td>
                            <td><?= htmlspecialchars($row['created_at'] ?? '-'); ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                            <td class="text-center">
                                <a href="index.php?page=detail_retur&id=<?= $row['idretur']; ?>" class="btn btn-success fw-bold px-3 py-1 rounded-2 shadow-sm me-1">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data retur barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>