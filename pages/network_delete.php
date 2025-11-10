<?php
include '../db.php';
include '../includes/session.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid ID.");
}

// Hapus data
mysqli_query($conn, "DELETE FROM network WHERE id=$id");

header("Location: network.php");
exit();
?>
