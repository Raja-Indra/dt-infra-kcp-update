<?php
include '../db.php';
include '../includes/session.php';

$option = $_GET['option'] ?? 'RIG';
if (!in_array($option, ['RIG','HT'])) {
    die("Invalid option.");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid ID.");
}

if ($option === 'HT') {
    // Cek dulu apakah ada file BAST
    $q = mysqli_query($conn, "SELECT bast_file FROM radio_ht WHERE id=$id");
    $data = mysqli_fetch_assoc($q);
    if ($data && !empty($data['bast_file'])) {
        $file = "../uploads/" . $data['bast_file'];
        if (file_exists($file)) {
            unlink($file);
        }
    }
    // Hapus data HT
    mysqli_query($conn, "DELETE FROM radio_ht WHERE id=$id");
} else {
    // Hapus data RIG
    mysqli_query($conn, "DELETE FROM radio_rig WHERE id=$id");
}

header("Location: radio.php?option=$option");
exit();
?>
