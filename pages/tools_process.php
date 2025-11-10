<?php
include '../db.php';
include '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid Request");
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Data
$description = $_POST['description'] ?? '';
$merk = $_POST['merk'] ?? '';
$qty = intval($_POST['qty'] ?? 0);
$uom = $_POST['uom'] ?? '';
$condition = $_POST['condition'] ?? '';
$status = $_POST['status'] ?? '';
$remarks = $_POST['remarks'] ?? '';
$po = $_POST['po'] ?? '';
$date_received = $_POST['date_received'] ?: null;

if ($id) {
    // UPDATE
    $query = "
        UPDATE tools SET
            description='$description',
            merk='$merk',
            qty='$qty',
            uom='$uom',
            `condition`='$condition',
            status='$status',
            remarks='$remarks',
            po='$po',
            date_received=" . ($date_received ? "'$date_received'" : "NULL") . "
        WHERE id=$id
    ";
    mysqli_query($conn, $query);
} else {
    // INSERT
    $query = "
        INSERT INTO tools (
            description, merk, qty, uom, `condition`, status, remarks, po, date_received
        ) VALUES (
            '$description', '$merk', '$qty', '$uom', '$condition', '$status',
            '$remarks', '$po', " . ($date_received ? "'$date_received'" : "NULL") . "
        )
    ";
    mysqli_query($conn, $query);
}

header("Location: tools.php");
exit();
?>
