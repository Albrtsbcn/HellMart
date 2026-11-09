<?php
require_once __DIR__ . '/../../dbconnection.php';
$conn = (new DBconnection())->get_connection();

if (isset($_POST['simpan_pengadaan'])) {
    $idvendor = $_POST['idvendor'];
    $iduser   = $_POST['user_iduser'];
    $ppn      = floatval($_POST['ppn'] ?? 0);

    $arr_idbarang = $_POST['idbarang'] ?? [];
    $arr_jumlah   = $_POST['jumlah'] ?? [];
    $arr_harga    = $_POST['harga_satuan'] ?? [];

    mysqli_begin_transaction($conn);

    try {
        if (empty($arr_idbarang)) throw new Exception("Minimal harus ada 1 barang dalam pengadaan.");

        $subtotal_nilai = 0;
        $items = [];

        foreach ($arr_idbarang as $i => $idbarang) {
            $jumlah = intval($arr_jumlah[$i]);
            $harga  = floatval($arr_harga[$i]);

            if ($jumlah <= 0 || $harga <= 0 || empty($idbarang)) continue;

            $sub_total = $jumlah * $harga;
            $subtotal_nilai += $sub_total;
            $items[] = compact('idbarang', 'jumlah', 'harga_satuan', 'sub_total');
        }

        if (empty($items)) throw new Exception("Data detail barang pengadaan tidak valid.");

        $total_nilai = $subtotal_nilai + $ppn;

        if (!mysqli_query($conn, "INSERT INTO pengadaan (timestamp, subtotal_nilai, ppn, total_nilai, user_iduser, vendor_idvendor) VALUES (NOW(), '$subtotal_nilai', '$ppn', '$total_nilai', '$iduser', '$idvendor')")) {
            throw new Exception("Gagal menyimpan header pengadaan: " . mysqli_error($conn));
        }

        $idpengadaan_baru = mysqli_insert_id($conn);

        foreach ($items as $item) {
            if (!mysqli_query($conn, "INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan) VALUES ('{$item['harga_satuan']}', '{$item['jumlah']}', '{$item['sub_total']}', '{$item['idbarang']}', '$idpengadaan_baru')")) {
                throw new Exception("Gagal menyimpan detail pengadaan: " . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        echo "<script>alert('Data pengadaan berhasil disimpan!'); window.location='index.php?page=pengadaan';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}

$vendor_result = mysqli_query($conn, "SELECT * FROM vendor");
$user_result   = mysqli_query($conn, "SELECT * FROM user");
$barang_result = mysqli_query($conn, "SELECT * FROM barang");
?>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-dark text-white fw-bold py-3 px-4 rounded-top-4">
        Tambah Transaksi Pengadaan Barang Baru
    </div>
    <div class="card-body p-4">
        <!-- PERBAIKAN: action dikosongkan agar diproses di file ini sendiri -->
        <form action="" method="POST">
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Vendor</label>
                    <select name="idvendor" class="form-select rounded-3" required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php while($v = mysqli_fetch_assoc($vendor_result)): ?>
                            <option value="<?= $v['idvendor']; ?>"><?= $v['nama_vendor']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">PPN (%)</label>
                    <input type="number" step="0.01" name="ppn" class="form-control rounded-3" placeholder="Contoh: 11" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">User / Admin Pengelola</label>
                    <select name="user_iduser" class="form-select rounded-3" required>
                        <option value="">-- Pilih User --</option>
                        <?php while($u = mysqli_fetch_assoc($user_result)): ?>
                            <option value="<?= $u['iduser']; ?>"><?= $u['username']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="fw-bold mb-3">Daftar Barang yang Dibeli</h5>
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th>Pilih Barang</th>
                            <th width="20%">Jumlah (Qty)</th>
                            <th width="25%">Harga Beli Satuan (Rp)</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="row-container-pengadaan">
                        <tr>
                            <td>
                                <select name="idbarang[]" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php 
                                    mysqli_data_seek($barang_result, 0);
                                    while($b = mysqli_fetch_assoc($barang_result)): 
                                    ?>
                                        <option value="<?= $b['idbarang']; ?>"><?= $b['nama']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="jumlah[]" class="form-control rounded-3" placeholder="Qty" min="1" required>
                            </td>
                            <td>
                                <input type="number" name="harga_satuan[]" class="form-control rounded-3" placeholder="Harga Satuan" min="0" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm w-100" disabled>Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" id="tambah-baris-pengadaan" class="btn btn-secondary btn-sm rounded-3 mb-4">+ Tambah Baris Barang</button>

            <div class="d-flex justify-content-between">
                <a href="index.php?page=pengadaan" class="btn btn-dark rounded-3 px-4 py-2 fw-semibold">Kembali</a>
                <button type="submit" name="simpan_pengadaan" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm">Simpan Pengadaan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('tambah-baris-pengadaan').addEventListener('click', function() {
    let container = document.getElementById('row-container-pengadaan');
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