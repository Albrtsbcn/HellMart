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
$is_supervisor = ($role_clean === 'supervisor');
$can_tambah_pengadaan = ($is_admin || $is_supervisor);

if (!$can_tambah_pengadaan) {
    echo "<script>alert('Akses ditolak! Hanya Administrator dan Supervisor yang dapat menambah pengadaan.'); window.location='index.php?page=pengadaan';</script>";
    exit();
}

if (isset($_POST['simpan_pengadaan'])) {
    $vendor_idvendor = intval($_POST['vendor_idvendor']);
    $user_iduser     = intval($_SESSION['iduser'] ?? 1);
    
    $arr_idbarang    = $_POST['idbarang'] ?? [];
    $arr_jumlah      = $_POST['jumlah'] ?? [];
    $arr_harga       = $_POST['harga_satuan'] ?? [];

    mysqli_begin_transaction($conn);

    try {
        $total_nilai = 0;
        $detail_items = [];

        foreach ($arr_idbarang as $i => $idbarang) {
            $id_b = intval($idbarang);
            $jml  = intval($arr_jumlah[$i]);
            $hrg  = intval($arr_harga[$i]);

            if ($id_b <= 0 || $jml <= 0) continue;

            $total_nilai += ($jml * $hrg);
            $detail_items[] = [
                'idbarang' => $id_b,
                'jumlah'   => $jml,
                'harga'    => $hrg
            ];
        }

        if (empty($detail_items)) {
            throw new Exception("Minimal harus ada 1 barang valid dalam pengadaan.");
        }

        $q_pengadaan = "INSERT INTO pengadaan (timestamp, status, total_nilai, vendor_idvendor, user_iduser) 
                        VALUES (NOW(), '0', $total_nilai, $vendor_idvendor, $user_iduser)";
        if (!mysqli_query($conn, $q_pengadaan)) {
            throw new Exception("Gagal menyimpan header pengadaan: " . mysqli_error($conn));
        }
        $idpengadaan_baru = intval(mysqli_insert_id($conn));

        foreach ($detail_items as $item) {
            $q_sp = "CALL sp_tambah_detail_pengadaan($idpengadaan_baru, {$item['idbarang']}, {$item['jumlah']}, {$item['harga']})";
            if (!mysqli_query($conn, $q_sp)) {
                throw new Exception("Gagal mengeksekusi SP detail pengadaan: " . mysqli_error($conn));
            }
            while (mysqli_more_results($conn)) {
                mysqli_next_result($conn);
            }
        }

        mysqli_commit($conn);
        echo "<script>alert('Pengadaan berhasil disimpan!'); window.location='index.php?page=pengadaan';</script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location='index.php?page=tambah_pengadaan';</script>";
        exit();
    }
}

$vendors = mysqli_query($conn, "SELECT * FROM vendor");
$barangs = mysqli_query($conn, "SELECT idbarang, nama, harga FROM barang");
$barang_options = "";
while ($b = mysqli_fetch_assoc($barangs)) {
    $barang_options .= "<option value=\"" . intval($b['idbarang']) . "\" data-harga=\"" . intval($b['harga']) . "\">" . htmlspecialchars($b['nama']) . "</option>";
}
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-1">Tambah Pengadaan Barang (PO)</h3>
                <p class="text-muted mb-0">Buat pesanan pengadaan baru ke vendor dengan harga otomatis.</p>
            </div>
            <a href="index.php?page=pengadaan" class="btn btn-dark fw-bold px-4 py-2 rounded-3 shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <form action="" method="POST">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Vendor</label>
                <select name="vendor_idvendor" class="form-select" required>
                    <option value="" disabled selected>-- Pilih Vendor --</option>
                    <?php while ($v = mysqli_fetch_assoc($vendors)): ?>
                        <option value="<?= intval($v['idvendor']); ?>"><?= htmlspecialchars($v['nama_vendor']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white text-dark fw-bold py-3 px-4 d-flex justify-content-between align-items-center">
                <span>Daftar Barang Pengadaan</span>
                <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="tambahBaris()">+ Tambah Baris</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabel-item">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Nama Barang</th>
                            <th class="py-3">Harga Satuan</th>
                            <th class="py-3">Jumlah</th>
                            <th class="py-3">Subtotal</th>
                            <th class="py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="body-item">
                        <tr>
                            <td class="px-4">
                                <select name="idbarang[]" class="form-select pilih-barang" required onchange="updateHarga(this)">
                                    <option value="" disabled selected>-- Pilih Barang --</option>
                                    <?= $barang_options; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="harga_satuan[]" class="form-control input-harga" readonly required>
                            </td>
                            <td>
                                <input type="number" name="jumlah[]" class="form-control input-jumlah" min="1" value="1" required oninput="hitungSubtotal(this)">
                            </td>
                            <td>
                                <input type="text" class="form-control input-subtotal" readonly value="0">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm fw-bold px-2 py-1" onclick="hapusBaris(this)">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light p-4 text-end">
                <h5 class="fw-bold text-dark mb-0">Total Keseluruhan: <span class="text-success" id="grand-total">Rp 0</span></h5>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" name="simpan_pengadaan" class="btn btn-success fw-bold px-5 py-3 rounded-3 shadow-sm fs-6">
                Simpan Pengadaan
            </button>
        </div>
    </form>
</div>

<script>
var templateOpsiBarang = '<?= addslashes($barang_options); ?>';

function tambahBaris() {
    var tbody = document.getElementById('body-item');
    var tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="px-4">
            <select name="idbarang[]" class="form-select pilih-barang" required onchange="updateHarga(this)">
                <option value="" disabled selected>-- Pilih Barang --</option>
                ${templateOpsiBarang}
            </select>
        </td>
        <td>
            <input type="number" name="harga_satuan[]" class="form-control input-harga" readonly required>
        </td>
        <td>
            <input type="number" name="jumlah[]" class="form-control input-jumlah" min="1" value="1" required oninput="hitungSubtotal(this)">
        </td>
        <td>
            <input type="text" class="form-control input-subtotal" readonly value="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm fw-bold px-2 py-1" onclick="hapusBaris(this)">Hapus</button>
        </td>
    `;
    tbody.appendChild(tr);
}

function hapusBaris(btn) {
    var tbody = document.getElementById('body-item');
    if (tbody.rows.length > 1) {
        btn.closest('tr').remove();
        hitungGrandTotal();
    } else {
        alert('Minimal harus ada 1 barang dalam pengadaan.');
    }
}

function updateHarga(selectObj) {
    var row = selectObj.closest('tr');
    var selectedOption = selectObj.options[selectObj.selectedIndex];
    var harga = selectedOption.getAttribute('data-harga') || 0;
    
    var inputHarga = row.querySelector('.input-harga');
    inputHarga.value = harga;
    
    hitungSubtotal(inputHarga);
}

function hitungSubtotal(element) {
    var row = element.closest('tr');
    var harga = parseFloat(row.querySelector('.input-harga').value) || 0;
    var jumlah = parseFloat(row.querySelector('.input-jumlah').value) || 0;
    var subtotal = harga * jumlah;
    
    row.querySelector('.input-subtotal').value = subtotal.toLocaleString('id-ID');
    hitungGrandTotal();
}

function hitungGrandTotal() {
    var subtotalInputs = document.querySelectorAll('.input-subtotal');
    var grandTotal = 0;
    
    subtotalInputs.forEach(function(input) {
        var val = input.value.replace(/\./g, '');
        grandTotal += parseFloat(val) || 0;
    });
    
    document.getElementById('grand-total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
}
</script>