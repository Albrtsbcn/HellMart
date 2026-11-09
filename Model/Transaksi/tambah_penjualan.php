<?php
require_once __DIR__ . '/../../dbconnection.php';
$conn = (new DBconnection())->get_connection();

$barang_result = mysqli_query($conn, "SELECT barang.*, COALESCE((SELECT stock FROM kartu_stok WHERE idbarang = barang.idbarang ORDER BY idkartu_stok DESC LIMIT 1), 0) AS stok FROM barang");
$margin_result = mysqli_query($conn, "SELECT * FROM margin_penjualan");
$user_result   = mysqli_query($conn, "SELECT * FROM user");

if (isset($_POST['simpan'])) {
    $idmargin   = $_POST['idmargin_penjualan'];
    $ppn_persen = floatval($_POST['ppn']);
    $iduser     = $_POST['iduser'];

    $arr_idbarang = $_POST['idbarang'] ?? [];
    $arr_jumlah   = $_POST['jumlah'] ?? [];

    mysqli_begin_transaction($conn);

    try {
        $subtotal_global = 0;
        $detail_items = [];

        foreach ($arr_idbarang as $index => $idbarang) {
            $jumlah = intval($arr_jumlah[$index]);
            if ($jumlah <= 0 || empty($idbarang)) continue;

            $harga_dasar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT harga FROM barang WHERE idbarang = '$idbarang'"))['harga'];
            $sub_item    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(hitung_subtotal_margin($harga_dasar, $jumlah, $idmargin), 2) AS subtotal"))['subtotal'];

            $subtotal_global += $sub_item;
            $detail_items[] = [
                'idbarang'     => $idbarang,
                'jumlah'       => $jumlah,
                'subtotal'     => $sub_item,
                'harga_satuan' => $sub_item / $jumlah
            ];
        }

        if (empty($detail_items)) throw new Exception("Minimal harus ada 1 barang yang valid dalam transaksi.");

        $nominal_ppn = $subtotal_global * ($ppn_persen / 100);
        $total_nilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(hitung_total_nilai($subtotal_global, $nominal_ppn), 2) AS total_nilai"))['total_nilai'];

        if (!mysqli_query($conn, "INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan) VALUES (NOW(), '$subtotal_global', '$nominal_ppn', '$total_nilai', '$iduser', '$idmargin')")) {
            throw new Exception("Gagal menyimpan ke tabel penjualan: " . mysqli_error($conn));
        }

        $idpenjualan_baru = mysqli_insert_id($conn);

        foreach ($detail_items as $item) {
            if (!mysqli_query($conn, "INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang) VALUES ('{$item['harga_satuan']}', '{$item['jumlah']}', '{$item['subtotal']}', '$idpenjualan_baru', '{$item['idbarang']}')")) {
                throw new Exception("Gagal menyimpan ke detail penjualan: " . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        echo "<script>alert('Transaksi penjualan multi-item berhasil disimpan!'); window.location='index.php?page=penjualan';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}
?>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-dark text-white fw-bold py-3 px-4 rounded-top-4">
        Tambah Transaksi Penjualan Baru (Multi-Item)
    </div>
    <div class="card-body p-4">
        <form action="" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Margin Penjualan (Keuntungan)</label>
                    <select name="idmargin_penjualan" class="form-select rounded-3" required>
                        <option value="">-- Pilih Margin Penjualan --</option>
                        <?php while($m = mysqli_fetch_assoc($margin_result)): ?>
                            <option value="<?= $m['idmargin_penjualan']; ?>">Margin: <?= $m['persen']; ?>%</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kasir / User</label>
                    <select name="iduser" class="form-select rounded-3" required>
                        <option value="">-- Pilih User / Kasir --</option>
                        <?php while($u = mysqli_fetch_assoc($user_result)): ?>
                            <option value="<?= $u['iduser']; ?>"><?= $u['username']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Persentase PPN (%)</label>
                <input type="number" step="0.01" name="ppn" class="form-control rounded-3" value="11" required placeholder="Contoh: 11">
            </div>

            <hr class="my-4">

            <h5 class="fw-bold mb-3">Daftar Barang Belanjaan</h5>
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th>Pilih Barang</th>
                            <th width="25%">Jumlah (Qty)</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="row-container-penjualan">
                        <tr>
                            <td>
                                <select name="idbarang[]" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php 
                                    mysqli_data_seek($barang_result, 0);
                                    while($b = mysqli_fetch_assoc($barang_result)): 
                                    ?>
                                        <option value="<?= $b['idbarang']; ?>"><?= $b['nama']; ?> (Stok: <?= $b['stok']; ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="jumlah[]" class="form-control rounded-3" min="1" required placeholder="Qty">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm w-100" disabled>Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" id="tambah-baris-penjualan" class="btn btn-secondary btn-sm rounded-3 mb-4">+ Tambah Baris Barang</button>

            <div class="d-flex justify-content-between">
                <a href="index.php?page=penjualan" class="btn btn-light rounded-3 px-4 py-2 fw-semibold">Kembali</a>
                <button type="submit" name="simpan" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('tambah-baris-penjualan').addEventListener('click', function() {
    let container = document.getElementById('row-container-penjualan');
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