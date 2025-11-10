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
$hostname = $_POST['hostname'] ?? '';
$serial_number = $_POST['serial_number'] ?? '';
$processor = $_POST['processor'] ?? '';
$memory = $_POST['memory'] ?? '';

// Storage Capacity
// Storage Capacity
$storage_capacity = '';
if(isset($_POST['storage_capacity']) && is_array($_POST['storage_capacity'])){
    $parts = [];
    foreach($_POST['storage_capacity'] as $idx => $val){
        $val = trim($val);
        if($val!==''){
            $parts[] = "Storage $idx: $val";
        }
    }
    $storage_capacity = implode(', ', $parts);
}

// Storage Type
$storage_type = $_POST['storage_type'] ?? '';

$mac_address = $_POST['mac_address'] ?? '';

// PSU
$psu = $_POST['psu'] ?? '';

// RAID System
$raid_system = '';
if (isset($_POST['raid_system']) && is_array($_POST['raid_system'])) {
    $raid_system = implode(', ', $_POST['raid_system']);
}

$virtualization = $_POST['virtualization'] ?? '';
$operating_system = $_POST['operating_system'] ?? '';
$condition = $_POST['condition'] ?? '';
$status = $_POST['status'] ?? '';
$warranty_expiration = $_POST['warranty_expiration'] ?: null;
$remarks = $_POST['remarks'] ?? '';
$po = $_POST['po'] ?? '';
$date_received = $_POST['date_received'] ?: null;

if ($id) {
    // UPDATE
    $query = "
        UPDATE server SET
            model='$model',
            merk_id='$merk_id',
            type_id='$type_id',
            hostname='$hostname',
            serial_number='$serial_number',
            processor='$processor',
            memory='$memory',
            storage_capacity='$storage_capacity',
            storage_type='$storage_type',
            mac_address='$mac_address',
            psu='$psu',
            raid_system='$raid_system',
            virtualization='$virtualization',
            operating_system='$operating_system',
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
    // INSERT
    $query = "
        INSERT INTO server (
            model, merk_id, type_id, hostname, serial_number, processor, memory,
            storage_capacity, storage_type,
            mac_address, psu, raid_system, virtualization, operating_system,
            `condition`, status, warranty_expiration, remarks, po, date_received
        ) VALUES (
            '$model', '$merk_id', '$type_id', '$hostname', '$serial_number', '$processor', '$memory',
            '$storage_capacity', '$storage_type',
            '$mac_address', '$psu', '$raid_system', '$virtualization', '$operating_system',
            '$condition', '$status', " . ($warranty_expiration ? "'$warranty_expiration'" : "NULL") . ",
            '$remarks', '$po', " . ($date_received ? "'$date_received'" : "NULL") . "
        )
    ";
    mysqli_query($conn, $query);
}

header("Location: server.php");
exit();
?>
