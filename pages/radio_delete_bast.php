<?php
include '../db.php';
include '../includes/session.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid ID");
}

// Cari data lama
$q = mysqli_query($conn, "SELECT bast_file FROM radio_ht WHERE id=$id");
$data = mysqli_fetch_assoc($q);
if (!$data || empty($data['bast_file'])) {
    die("File not found");
}

// Hapus file fisik
$file_path = "../uploads/" . $data['bast_file'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// Update kolom bast_file jadi kosong
mysqli_query($conn, "UPDATE radio_ht SET bast_file=NULL WHERE id=$id");

// Redirect kembali ke form edit
header("Location: radio_input.php?option=HT&id=$id");
exit();
?>
