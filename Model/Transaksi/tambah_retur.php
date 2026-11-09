<?php
require_once __DIR__ . '/../../dbconnection.php';
$conn = (new DBconnection())->get_connection();

$user_result = mysqli_query($conn, "SELECT * FROM user");
$data_detail_penerimaan = mysqli_fetch_all(mysqli_query($conn, "
    SELECT dp.*, b.nama AS nama_barang, p.idpenerimaan 
    FROM detail_penerimaan dp 
    JOIN barang b ON dp.barang_idbarang = b.idbarang 
    JOIN penerimaan p ON dp.idpenerimaan = p.idpenerimaan
"), MYSQLI_ASSOC);

if (isset($_POST['simpan_retur'])) {
    $iduser                  = intval($_POST['iduser']);
    $arr_iddetail_penerimaan = $_POST['iddetail_penerimaan'] ?? [];
    $arr_jumlah              = $_POST['jumlah'] ?? [];
    $arr_alasan              = $_POST['alasan'] ?? [];

    mysqli_begin_transaction($conn);

    try {
        if (empty($arr_iddetail_penerimaan)) throw new Exception("Tidak ada item barang yang dipilih untuk diretur.");

        $iddetail_pertama = intval($arr_iddetail_penerimaan[0]);
        $d_penerimaan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT idpenerimaan FROM detail_penerimaan WHERE iddetail_penerimaan = $iddetail_pertama"));
        
        if (!$d_penerimaan) throw new Exception("Referensi penerimaan untuk barang yang dipilih tidak valid.");
        $idpenerimaan = intval($d_penerimaan['idpenerimaan']);

        if (!mysqli_query($conn, "INSERT INTO retur (created_at, idpenerimaan, iduser) VALUES (NOW(), $idpenerimaan, $iduser)")) {
            throw new Exception("Gagal menyimpan header retur: " . mysqli_error($conn));
        }

        $idretur_baru = mysqli_insert_id($conn);

        foreach ($arr_iddetail_penerimaan as $index => $iddetail_penerimaan) {
            $jumlah = intval($arr_jumlah[$index]);
            $alasan = mysqli_real_escape_string($conn, $arr_alasan[$index]);

            if ($jumlah <= 0 || empty($iddetail_penerimaan)) continue;

            if (!mysqli_query($conn, "CALL sp_tambah_detail_retur($idretur_baru, $iddetail_penerimaan, $jumlah, '$alasan')")) {
                throw new Exception("Gagal menyimpan detail retur: " . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        echo "<script>alert('Data retur berhasil disimpan! Stok otomatis berkurang.'); window.location='index.php?page=retur';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location='index.php?page=tambah_retur';</script>";
    }
}
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h3 class="fw-bold text-dark mb-1">Form Tambah Retur Barang</h3>
        <p class="text-muted mb-4">Pilih petugas pelapor serta daftar barang yang ingin diretur dari riwayat penerimaan.</p>

        <form action="" method="POST">
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label class="form-label fw-semibold text-secondary small">Petugas / User Pelapor</label>
                    <select name="iduser" class="form-select rounded-3 py-2" required>
                        <option value="">-- Pilih User --</option>
                        <?php while($u = mysqli_fetch_assoc($user_result)): ?>
                            <option value="<?= $u['iduser']; ?>"><?= $u['username']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <hr class="text-muted opacity-25 my-3">

            <h5 class="fw-bold text-dark mb-3">Daftar Barang yang Diretur</h5>
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th>Pilih Item Penerimaan </th>
                            <th width="20%">Jumlah Retur</th>
                            <th width="30%">Alasan Retur</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="row-container-retur">
                        <tr>
                            <td>
                                <select name="iddetail_penerimaan[]" class="form-select rounded-3" required>
                                    <option value=""></option>
                                    <?php foreach($data_detail_penerimaan as $dp): ?>
                                        <option value="<?= $dp['iddetail_penerimaan']; ?>">
                                            [Penerimaan <?= $dp['idpenerimaan']; ?>] - <?= $dp['nama_barang']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="jumlah[]" class="form-control rounded-3" min="1" placeholder="Qty Retur" required>
                            </td>
                            <td>
                                <input type="text" name="alasan[]" class="form-control rounded-3" placeholder="Contoh: Barang Rusak / Cacat" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm rounded-2 w-100" disabled>Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" id="tambah-baris-retur" class="btn btn-secondary btn-sm fw-semibold rounded-3 mb-4 px-3 py-2">
                + Tambah Baris Barang Lain
            </button>

            <div class="d-flex justify-content-between">
                <a href="index.php?page=retur" class="btn btn-light rounded-3 px-4 py-2 fw-semibold">Kembali</a>
                <button type="submit" name="simpan_retur" class="btn btn-dark rounded-3 px-4 py-2 fw-bold shadow-sm">Simpan Transaksi Retur</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('tambah-baris-retur').addEventListener('click', function() {
    let container = document.getElementById('row-container-retur');
    let firstRow = container.querySelector('tr');
    let newRow = firstRow.cloneNode(true);
    
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    
    let deleteBtn = newRow.querySelector('button');
    deleteBtn.removeAttribute('disabled');
    deleteBtn.classList.replace('btn-danger', 'btn-outline-danger');
    deleteBtn.addEventListener('click', function() {
        newRow.remove();
    });

    container.appendChild(newRow);
});
</script>