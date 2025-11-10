<?php
include '../db.php';
include '../includes/session.php';

$option = $_GET['option'] ?? 'Desktop';
if (!in_array($option, ['Desktop', 'Laptop'])) {
    die("Invalid option.");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid ID.");
}

$table = strtolower($option);

if ($option === 'Laptop') {
    // Cek apakah ada file BAST
    $q = mysqli_query($conn, "SELECT bast_file FROM laptop WHERE id=$id");
    $data = mysqli_fetch_assoc($q);
    if ($data && !empty($data['bast_file'])) {
        $file = "../uploads/" . $data['bast_file'];
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

// Hapus data
mysqli_query($conn, "DELETE FROM `$table` WHERE id=$id");

header("Location: computer.php?option=$option");
exit();
?>
