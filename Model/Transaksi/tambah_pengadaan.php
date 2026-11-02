<?php
// Koneksi dan ambil data master (Vendor, User, Barang)
require_once __DIR__ . '/../../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

$vendor_result = mysqli_query($conn, "SELECT * FROM vendor");
$user_result   = mysqli_query($conn, "SELECT * FROM user");
$barang_result = mysqli_query($conn, "SELECT * FROM barang");
?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">
        Tambah Transaksi Pengadaan Barang Baru (Multi-Item)
    </div>
    <div class="card-body">
        <form action="proses_tambah_pengadaan.php" method="POST">
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Vendor</label>
                    <select name="idvendor" class="form-select" required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php while($v = mysqli_fetch_assoc($vendor_result)): ?>
                            <option value="<?= $v['idvendor']; ?>"><?= $v['nama_vendor']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">PPN (%)</label>
                    <input type="number" step="0.01" name="ppn" class="form-control" placeholder="Contoh: 11" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">User / Admin Pengelola</label>
                    <select name="user_iduser" class="form-select" required>
                        <option value="">-- Pilih User --</option>
                        <?php while($u = mysqli_fetch_assoc($user_result)): ?>
                            <option value="<?= $u['iduser']; ?>"><?= $u['username']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <hr>

            <!-- TABEL ITEM BARANG -->
            <h5 class="fw-bold mb-3">Daftar Barang yang Dibeli</h5>
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Pilih Barang</th>
                        <th width="20%">Jumlah (Qty)</th>
                        <th width="25%">Harga Beli Satuan (Rp)</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <!-- PASTIKAN ID INI ADA: row-container-pengadaan -->
                <tbody id="row-container-pengadaan">
                    <tr>
                        <td>
                            <select name="idbarang[]" class="form-select" required>
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
                            <input type="number" name="jumlah[]" class="form-control" placeholder="Qty" min="1" required>
                        </td>
                        <td>
                            <input type="number" name="harga_satuan[]" class="form-control" placeholder="Harga Satuan" min="0" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm w-100" disabled>Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tombol Tambah Baris -->
            <button type="button" id="tambah-baris-pengadaan" class="btn btn-secondary btn-sm mb-4">+ Tambah Baris Barang</button>

            <div class="d-flex justify-content-between">
                <a href="/HellMart/includes/index.php?page=pengadaan" class="btn btn-dark">Kembali</a>
                <button type="submit" name="simpan_pengadaan" class="btn btn-dark fw-bold">Simpan Pengadaan</button>
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
</script>s