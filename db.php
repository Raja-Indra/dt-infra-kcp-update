<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "dt-infra-kcp";

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Koneksi ke MySQL gagal: " . mysqli_connect_error());
}

mysqli_select_db($conn, $database) or die("Database $database tidak ditemukan.");
?>
