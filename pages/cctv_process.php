<?php
include '../db.php';
include '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid Request");
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Data
$model = $_POST['model'] ?? '';
$merk_id = (int)($_POST['merk_id'] ?? 0);
$type_id = (int)($_POST['type_id'] ?? 0);
$serial_number = $_POST['serial_number'] ?? '';
$mac_address = $_POST['mac_address'] ?? '';
$condition = $_POST['condition'] ?? '';
$status = $_POST['status'] ?? '';
$warranty_expiration = $_POST['warranty_expiration'] ?: null;
$remarks = $_POST['remarks'] ?? '';
$po = $_POST['po'] ?? '';
$date_received = $_POST['date_received'] ?: null;

if ($id) {
    // Update
    $query = "
        UPDATE cctv SET
            model='$model',
            merk_id='$merk_id',
            type_id='$type_id',
            serial_number='$serial_number',
            mac_address='$mac_address',
            `condition`='$condition',
            status='$status',
            warranty_expiration=" . ($warranty_expiration ? "'$warranty_expiration'" : "NULL") . ",
            remarks='$remarks',
            po='$po',
            date_received=" . ($date_received ? "'$date_received'" : "NULL") . "
        WHERE id=$id
    ";
    mysqli_query($conn, $query);
} else {
    // Insert
    $query = "
        INSERT INTO cctv (
            model, merk_id, type_id, serial_number, mac_address, `condition`,
            status, warranty_expiration, remarks, po, date_received
        ) VALUES (
            '$model', '$merk_id', '$type_id', '$serial_number', '$mac_address', '$condition',
            '$status', " . ($warranty_expiration ? "'$warranty_expiration'" : "NULL") . ",
            '$remarks', '$po', " . ($date_received ? "'$date_received'" : "NULL") . "
        )
    ";
    mysqli_query($conn, $query);
}

header("Location: cctv.php");
exit();
?>
