<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();

if (isset($_GET['id'])) {
    $idpenjualan = intval($_GET['id']);

    // Ambil data utama penjualan
    $q_transaksi = mysqli_query($conn, "SELECT p.*, u.username, m.persen AS margin_persen 
                                        FROM penjualan p 
                                        JOIN user u ON p.iduser = u.iduser 
                                        JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan 
                                        WHERE p.idpenjualan = $idpenjualan");
    $data_transaksi = mysqli_fetch_assoc($q_transaksi);

    // Ambil rincian detail barang
    $q_detail = mysqli_query($conn, "SELECT dp.*, b.nama AS nama_barangs 
                                       FROM detail_penjualan dp 
                                       JOIN barang b ON dp.idbarang = b.idbarang 
                                       WHERE dp.penjualan_idpenjualan = $idpenjualan");

    if ($data_transaksi) {
        echo "<div class='mb-3 p-3 bg-light rounded text-dark'>";
        echo "<div class='row'>";
        echo "<div class='col-md-6'><p class='mb-1'><strong>ID Transaksi:</strong> #" . $data_transaksi['idpenjualan'] . "</p>";
        echo "<p class='mb-1'><strong>Waktu:</strong> " . $data_transaksi['created_at'] . "</p>";
        echo "<p class='mb-1'><strong>Kasir:</strong> " . htmlspecialchars($data_transaksi['username']) . "</p></div>";
        echo "<div class='col-md-6'><p class='mb-1'><strong>Margin:</strong> " . $data_transaksi['margin_persen'] . "%</p>";
        echo "<p class='mb-1'><strong>Subtotal:</strong> Rp " . number_format($data_transaksi['subtotal_nilai'], 0, ',', '.') . "</p>";
        echo "<p class='mb-1'><strong>PPN:</strong> Rp " . number_format($data_transaksi['ppn'], 0, ',', '.') . "</p></div>";
        echo "</div></div>";

        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-sm align-middle'>";
        echo "<thead class='table-light'><tr><th>Nama Barang</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead><tbody>";

        if ($q_detail && mysqli_num_rows($q_detail) > 0) {
            while ($item = mysqli_fetch_assoc($q_detail)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['nama_barangs']) . "</td>";
                echo "<td>" . $item['jumlah'] . "</td>";
                echo "<td>Rp " . number_format($item['harga_satuan'], 0, ',', '.') . "</td>";
                echo "<td>Rp " . number_format($item['subtotal'], 0, ',', '.') . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center text-muted'>Belum ada detail barang untuk transaksi ini.</td></tr>";
        }

        echo "</tbody></table></div>";
    } else {
        echo "<p class='text-danger text-center'>Data transaksi tidak ditemukan.</p>";
    }
}
?>