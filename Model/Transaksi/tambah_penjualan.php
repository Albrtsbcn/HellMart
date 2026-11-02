<?php
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$barang_result = mysqli_query($conn, "SELECT * FROM barang");
$margin_result = mysqli_query($conn, "SELECT * FROM margin_penjualan");
$user_result = mysqli_query($conn, "SELECT * FROM user");

if (isset($_POST['simpan'])) {
    $idmargin   = $_POST['idmargin_penjualan'];
    $ppn_persen = floatval($_POST['ppn']);
    $iduser     = $_POST['iduser'];

    $arr_idbarang = $_POST['idbarang'];
    $arr_jumlah   = $_POST['jumlah'];

    mysqli_begin_transaction($conn);

    try {
        $subtotal_global = 0;
        $detail_items = [];

        foreach ($arr_idbarang as $index => $idbarang) {
            $jumlah = intval($arr_jumlah[$index]);
            if ($jumlah <= 0 || empty($idbarang)) continue;

            $q_barang = mysqli_query($conn, "SELECT harga FROM barang WHERE idbarang = '$idbarang'");
            $d_barang = mysqli_fetch_assoc($q_barang);
            $harga_dasar = $d_barang['harga']; 

            $q_subtotal = mysqli_query($conn, "SELECT ROUND(hitung_subtotal_margin($harga_dasar, $jumlah, $idmargin), 2) AS subtotal");
            $d_subtotal = mysqli_fetch_assoc($q_subtotal);
            $sub_item   = $d_subtotal['subtotal'];

            $subtotal_global += $sub_item;

            $detail_items[] = [
                'idbarang'     => $idbarang,
                'jumlah'       => $jumlah,
                'subtotal'     => $sub_item,
                'harga_satuan' => $sub_item / $jumlah
            ];
        }

        if (empty($detail_items)) {
            throw new Exception("Minimal harus ada 1 barang yang valid dalam transaksi.");
        }

        $nominal_ppn = $subtotal_global * ($ppn_persen / 100);


        $q_total = mysqli_query($conn, "SELECT ROUND(hitung_total_nilai($subtotal_global, $nominal_ppn), 2) AS total_nilai");
        $d_total = mysqli_fetch_assoc($q_total);
        $total_nilai = $d_total['total_nilai'];

        $query_insert_penjualan = "INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan) 
                                   VALUES (NOW(), '$subtotal_global', '$nominal_ppn', '$total_nilai', '$iduser', '$idmargin')";
        
        if (!mysqli_query($conn, $query_insert_penjualan)) {
            throw new Exception("Gagal menyimpan ke tabel penjualan: " . mysqli_error($conn));
        }

        $idpenjualan_baru = mysqli_insert_id($conn);

        foreach ($detail_items as $item) {
            $query_insert_detail = "INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang) 
                                    VALUES ('{$item['harga_satuan']}', '{$item['jumlah']}', '{$item['subtotal']}', '$idpenjualan_baru', '{$item['idbarang']}')";

            if (!mysqli_query($conn, $query_insert_detail)) {
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

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">
        Tambah Transaksi Penjualan Baru (Multi-Item)
    </div>
    <div class="card-body">
        <form action="" method="POST">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Margin Penjualan (Keuntungan)</label>
                    <select name="idmargin_penjualan" class="form-select" required>
                        <option value="">-- Pilih Margin Penjualan --</option>
                        <?php while($m = mysqli_fetch_assoc($margin_result)): ?>
                            <option value="<?= $m['idmargin_penjualan']; ?>">Margin: <?= $m['persen']; ?>%</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kasir / User</label>
                    <select name="iduser" class="form-select" required>
                        <option value="">-- Pilih User / Kasir --</option>
                        <?php while($u = mysqli_fetch_assoc($user_result)): ?>
                            <option value="<?= $u['iduser']; ?>"><?= $u['username']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Persentase PPN (%)</label>
                <input type="number" step="0.01" name="ppn" class="form-control" value="11" required placeholder="Contoh: 11">
            </div>

            <hr>
`
            <h5 class="fw-bold mb-3">Daftar Barang Belanjaan</h5>
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Pilih Barang</th>
                        <th width="25%">Jumlah (Qty)</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="row-container-penjualan">
                    <tr>
                        <td>
                            <select name="idbarang[]" class="form-select" required>
                                <option value="">-- Pilih Barang --</option>
                                <?php 
                                mysqli_data_seek($barang_result, 0);
                                while($b = mysqli_fetch_assoc($barang_result)): 
                                ?>
                                    <option value="<?= $b['idbarang']; ?>"><?= $b['nama']; ?> (Stok: <?= $b['stok'] ?? 0; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="jumlah[]" class="form-control" min="1" required placeholder="Qty">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm w-100" disabled>Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" id="tambah-baris-penjualan" class="btn btn-secondary btn-sm mb-4">+ Tambah Baris Barang</button>

            <div class="d-flex justify-content-between">
                <a href="index.php?page=penjualan" class="btn btn-secondary">Kembali</a>
                <button type="submit" name="simpan" class="btn btn-dark fw-bold">Simpan Transaksi</button>
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