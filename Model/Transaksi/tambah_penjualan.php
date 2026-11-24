<?php
require_once __DIR__ . '/../../dbconnection.php';
$conn = (new DBconnection())->get_connection();

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
$is_kasir = ($role_clean === 'kasir');
$can_tambah_penjualan = ($is_admin || $is_kasir);

if (!$can_tambah_penjualan) {
    echo "<script>alert('Akses ditolak! Hanya Kasir dan Administrator yang dapat menambah transaksi penjualan.'); window.location='index.php?page=penjualan';</script>";
    exit();
}

$sql_barang = "
    SELECT 
        barang.*, 
        COALESCE((
            SELECT stock 
            FROM kartu_stok 
            WHERE idbarang = barang.idbarang 
            ORDER BY idkartu_stok DESC 
            LIMIT 1
        ), 0) AS stok 
    FROM barang
    HAVING stok > 0
    ORDER BY barang.nama ASC
";
$barang_result = mysqli_query($conn, $sql_barang);

$stok_map = [];
while ($row_b = mysqli_fetch_assoc($barang_result)) {
    $stok_map[intval($row_b['idbarang'])] = intval($row_b['stok']);
}

$q_margin = mysqli_query($conn, "SELECT idmargin_penjualan, persen FROM margin_penjualan WHERE status = 1 LIMIT 1");
$active_margin = mysqli_fetch_assoc($q_margin);

if (isset($_POST['simpan'])) {
    if (!$active_margin) {
        echo "<script>alert('Tidak ada margin penjualan yang aktif saat ini. Harap aktifkan salah satu margin terlebih dahulu!'); window.location='index.php?page=margin';</script>";
        exit();
    }

    $idmargin   = intval($active_margin['idmargin_penjualan']);
    $ppn_persen = floatval($_POST['ppn']);
    $iduser     = intval($_SESSION['iduser'] ?? 1);

    $arr_idbarang = $_POST['idbarang'] ?? [];
    $arr_jumlah   = $_POST['jumlah'] ?? [];

    mysqli_begin_transaction($conn);

    try {
        $subtotal_global = 0;
        $detail_items = [];

        foreach ($arr_idbarang as $index => $idbarang) {
            $idbarang_clean = intval($idbarang);
            $jumlah = intval($arr_jumlah[$index]);
            if ($jumlah <= 0 || $idbarang_clean <= 0) continue;

            $q_cek_stok = mysqli_query($conn, "
                SELECT 
                    b.harga,
                    b.nama,
                    COALESCE((
                        SELECT stock 
                        FROM kartu_stok 
                        WHERE idbarang = b.idbarang 
                        ORDER BY idkartu_stok DESC 
                        LIMIT 1
                    ), 0) AS stok_terkini
                FROM barang b
                WHERE b.idbarang = $idbarang_clean
                LIMIT 1
            ");
            $data_barang = mysqli_fetch_assoc($q_cek_stok);

            if (!$data_barang) {
                throw new Exception("Barang tidak ditemukan.");
            }

            $stok_terkini = intval($data_barang['stok_terkini']);
            if ($stok_terkini <= 0) {
                throw new Exception("Barang \"{$data_barang['nama']}\" stoknya kosong atau minus ({$stok_terkini}), transaksi tidak dapat dilakukan.");
            }

            if ($jumlah > $stok_terkini) {
                throw new Exception("Jumlah beli \"{$data_barang['nama']}\" ({$jumlah}) melebihi stok tersedia ({$stok_terkini}).");
            }

            $harga_dasar = floatval($data_barang['harga']);
            $sub_item    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(hitung_subtotal_margin($harga_dasar, $jumlah, $idmargin), 2) AS subtotal"))['subtotal'];

            $subtotal_global += floatval($sub_item);
            $detail_items[] = [
                'idbarang'     => $idbarang_clean,
                'jumlah'       => $jumlah,
                'subtotal'     => $sub_item,
                'harga_satuan' => round(floatval($sub_item) / $jumlah)
            ];
        }

        if (empty($detail_items)) {
            throw new Exception("Minimal harus ada 1 barang dengan stok tersedia dalam transaksi.");
        }

        $nominal_ppn = $subtotal_global * ($ppn_persen / 100);
        $total_nilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(hitung_total_nilai($subtotal_global, $nominal_ppn), 2) AS total_nilai"))['total_nilai'];

        if (!mysqli_query($conn, "INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan) VALUES (NOW(), '$subtotal_global', '$nominal_ppn', '$total_nilai', '$iduser', '$idmargin')")) {
            throw new Exception("Gagal menyimpan ke tabel penjualan: " . mysqli_error($conn));
        }

        $idpenjualan_baru = intval(mysqli_insert_id($conn));

        foreach ($detail_items as $item) {
            $sql_sp = "CALL sp_tambah_detail_penjualan({$idpenjualan_baru}, {$item['idbarang']}, {$item['jumlah']}, {$item['harga_satuan']})";
            if (!mysqli_query($conn, $sql_sp)) {
                throw new Exception("Gagal memanggil detail penjualan: " . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        echo "<script>alert('Transaksi penjualan multi-item berhasil disimpan!'); window.location='index.php?page=penjualan';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div class='alert alert-danger m-3'>" . htmlspecialchars($e->getMessage()) . "</div>";
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
                    <label class="form-label fw-bold">Margin Penjualan Aktif</label>
                    <input type="text" class="form-control rounded-3 bg-light text-success fw-bold" value="<?= $active_margin ? htmlspecialchars($active_margin['persen']) . '% ' : 'Tidak ada margin aktif!'; ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Persentase PPN (%)</label>
                    <input type="number" step="0.01" name="ppn" class="form-control rounded-3" value="11" required placeholder="">
                </div>
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
                                    while ($b = mysqli_fetch_assoc($barang_result)): 
                                    ?>
                                        <option value="<?= intval($b['idbarang']); ?>" data-stok="<?= intval($b['stok']); ?>">
                                            <?= htmlspecialchars($b['nama']); ?> (Stok: <?= intval($b['stok']); ?>)
                                        </option>
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
function bindRowEvents(row) {
    let selectEl = row.querySelector('select[name="idbarang[]"]');
    let qtyEl = row.querySelector('input[name="jumlah[]"]');

    function syncMaxQty() {
        let selectedOption = selectEl.options[selectEl.selectedIndex];
        let stok = selectedOption ? parseInt(selectedOption.getAttribute('data-stok') || '0', 10) : 0;

        if (stok > 0) {
            qtyEl.max = stok;
            if (parseInt(qtyEl.value || '0', 10) > stok) {
                qtyEl.value = stok;
            }
        } else {
            qtyEl.removeAttribute('max');
        }
    }

    selectEl.addEventListener('change', syncMaxQty);
    qtyEl.addEventListener('input', function() {
        let selectedOption = selectEl.options[selectEl.selectedIndex];
        let stok = selectedOption ? parseInt(selectedOption.getAttribute('data-stok') || '0', 10) : 0;
        let val = parseInt(qtyEl.value || '0', 10);

        if (stok > 0 && val > stok) {
            qtyEl.value = stok;
        }
    });
}

bindRowEvents(document.querySelector('#row-container-penjualan tr'));

document.getElementById('tambah-baris-penjualan').addEventListener('click', function() {
    let container = document.getElementById('row-container-penjualan');
    let firstRow = container.querySelector('tr');
    let newRow = firstRow.cloneNode(true);
    
    newRow.querySelectorAll('input').forEach(input => {
        input.value = '';
        input.removeAttribute('max');
    });
    newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    
    let deleteBtn = newRow.querySelector('button');
    deleteBtn.removeAttribute('disabled');
    deleteBtn.classList.replace('btn-danger', 'btn-outline-danger');
    deleteBtn.addEventListener('click', function() {
        newRow.remove();
    });

    bindRowEvents(newRow);
    container.appendChild(newRow);
});
</script>