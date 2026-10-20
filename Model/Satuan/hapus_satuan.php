<?php
require_once __DIR__ . '/../dbconnection.php';
$db = new DBconnection();
$conn = $db->get_connection();
if (isset($_GET['id'])) {
    mysqli_query($conn, "DELETE FROM satuan WHERE idsatuan = '{$_GET['id']}'");
    header("Location: ../index.php?page=satuan");
    exit;
}
?>