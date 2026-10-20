<?php
require_once 'dbconnection.php';

try {
    $db = new DBconnection();
    $conn = $db->get_connection();

    if ($conn) {
        echo "<h3 style='color: green;'>Koneksi ke database HeidiSQL (TugasPrak) Berhasil!</h3>";
    }
} catch (DatabaseException $e) {
    echo "<h3 style='color: red;'>Koneksi Gagal: " . $e->getMessage() . "</h3>";
}
?>