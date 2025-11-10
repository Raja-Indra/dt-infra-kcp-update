<?php
include '../db.php';
include '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: computer.php');
    exit();
}

$option = $_POST['option'] ?? 'Desktop';
if (!in_array($option, ['Desktop','Laptop'])) {
    die("Invalid option");
}

$table = strtolower($option);

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$model = $option;
$merk_id = intval($_POST['merk_id']);
$type_id = intval($_POST['type_id']);
$hostname = mysqli_real_escape_string($conn, $_POST['hostname']);
$serial_number_mobo = mysqli_real_escape_string($conn, $_POST['serial_number_mobo']);
$processor = mysqli_real_escape_string($conn, $_POST['processor']);
$memory = mysqli_real_escape_string($conn, $_POST['memory']);
$storage = mysqli_real_escape_string($conn, $_POST['storage']);
$mac_wifi = mysqli_real_escape_string($conn, $_POST['mac_wifi']);
$mac_lan = mysqli_real_escape_string($conn, $_POST['mac_lan']);
$monitor = mysqli_real_escape_string($conn, $_POST['monitor']);
$serial_number_monitor = mysqli_real_escape_string($conn, $_POST['serial_number_monitor']);

$condition = mysqli_real_escape_string($conn, $_POST['condition']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$warranty_expiration = $_POST['warranty_expiration'] ?: null;
$remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
$po = mysqli_real_escape_string($conn, $_POST['po']);
$date_received = $_POST['date_received'] ?: null;

// Laptop BAST file
$bast_file = null;
if ($option === 'Laptop' && isset($_FILES['bast_file']) && $_FILES['bast_file']['error'] == 0) {
    $bast_file = time().'_'.basename($_FILES['bast_file']['name']);
    move_uploaded_file($_FILES['bast_file']['tmp_name'], "../uploads/".$bast_file);
}

if ($option === 'Desktop') {
    $user_by_jde = mysqli_real_escape_string($conn, $_POST['user_by_jde']);
    $user_by_name = mysqli_real_escape_string($conn, $_POST['user_by_name']);
    $user_by_dept = mysqli_real_escape_string($conn, $_POST['user_by_dept']);
    $installed_by_jde = mysqli_real_escape_string($conn, $_POST['installed_by_jde']);
    $installed_by_name = mysqli_real_escape_string($conn, $_POST['installed_by_name']);
    $date_installed = $_POST['date_installed'] ?: null;
    $removed_by_jde = mysqli_real_escape_string($conn, $_POST['removed_by_jde']);
    $removed_by_name = mysqli_real_escape_string($conn, $_POST['removed_by_name']);
    $date_removed = $_POST['date_removed'] ?: null;

    if ($id) {
        // Update Desktop
        mysqli_query($conn, "
            UPDATE $table SET
                model='$model',
                merk_id='$merk_id',
                type_id='$type_id',
                hostname='$hostname',
                serial_number_mobo='$serial_number_mobo',
                processor='$processor',
                memory='$memory',
                storage='$storage',
                mac_wifi='$mac_wifi',
                mac_lan='$mac_lan',
                monitor='$monitor',
                serial_number_monitor='$serial_number_monitor',
                user_by_jde='$user_by_jde',
                user_by_name='$user_by_name',
                user_by_dept='$user_by_dept',
                installed_by_jde='$installed_by_jde',
                installed_by_name='$installed_by_name',
                date_installed=" . ($date_installed ? "'$date_installed'" : "NULL") . ",
                removed_by_jde='$removed_by_jde',
                removed_by_name='$removed_by_name',
                date_removed=" . ($date_removed ? "'$date_removed'" : "NULL") . ",
                `condition`='$condition',
                status='$status',
                warranty_expiration=" . ($warranty_expiration ? "'$warranty_expiration'" : "NULL") . ",
                remarks='$remarks',
                po='$po',
                date_received=" . ($date_received ? "'$date_received'" : "NULL") . "
            WHERE id=$id
        ");
    } else {
        // Insert Desktop
        mysqli_query($conn, "
            INSERT INTO $table (
                model, merk_id, type_id, hostname, serial_number_mobo, processor, memory, storage,
                mac_wifi, mac_lan, monitor, serial_number_monitor,
                user_by_jde, user_by_name, user_by_dept,
                installed_by_jde, installed_by_name, date_installed,
                removed_by_jde, removed_by_name, date_removed,
                `condition`, status, warranty_expiration, remarks, po, date_received
            ) VALUES (
                '$model', '$merk_id', '$type_id', '$hostname', '$serial_number_mobo', '$processor', '$memory', '$storage',
                '$mac_wifi', '$mac_lan', '$monitor', '$serial_number_monitor',
                '$user_by_jde', '$user_by_name', '$user_by_dept',
                '$installed_by_jde', '$installed_by_name', " . ($date_installed ? "'$date_installed'" : "NULL") . ",
                '$removed_by_jde', '$removed_by_name', " . ($date_removed ? "'$date_removed'" : "NULL") . ",
                '$condition', '$status', " . ($warranty_expiration ? "'$warranty_expiration'" : "NULL") . ",
                '$remarks', '$po', " . ($date_received ? "'$date_received'" : "NULL") . "
            )
        ");
    }

} else {
    // Laptop fields
    $assign_to_jde = mysqli_real_escape_string($conn, $_POST['assign_to_jde']);
    $assign_to_name = mysqli_real_escape_string($conn, $_POST['assign_to_name']);
    $assign_to_dept = mysqli_real_escape_string($conn, $_POST['assign_to_dept']);
    $assign_by_jde = mysqli_real_escape_string($conn, $_POST['assign_by_jde']);
    $assign_by_name = mysqli_real_escape_string($conn, $_POST['assign_by_name']);
    $date_assign = $_POST['date_assign'] ?: null;
    $return_to_jde = mysqli_real_escape_string($conn, $_POST['return_to_jde']);
    $return_to_name = mysqli_real_escape_string($conn, $_POST['return_to_name']);
    $return_by_jde = mysqli_real_escape_string($conn, $_POST['return_by_jde']);
    $return_by_name = mysqli_real_escape_string($conn, $_POST['return_by_name']);
    $date_return = $_POST['date_return'] ?: null;

    if ($id) {
        // Update Laptop
        $bast_sql = $bast_file ? ", bast_file='$bast_file'" : "";
        mysqli_query($conn, "
            UPDATE $table SET
                model='$model',
                merk_id='$merk_id',
                type_id='$type_id',
                hostname='$hostname',
                serial_number_mobo='$serial_number_mobo',
                processor='$processor',
                memory='$memory',
                storage='$storage',
                mac_wifi='$mac_wifi',
                mac_lan='$mac_lan',
                monitor='$monitor',
                serial_number_monitor='$serial_number_monitor',
                assign_to_jde='$assign_to_jde',
                assign_to_name='$assign_to_name',
                assign_to_dept='$assign_to_dept',
                assign_by_jde='$assign_by_jde',
                assign_by_name='$assign_by_name',
                date_assign=" . ($date_assign ? "'$date_assign'" : "NULL") . ",
                return_to_jde='$return_to_jde',
                return_to_name='$return_to_name',
                return_by_jde='$return_by_jde',
                return_by_name='$return_by_name',
                date_return=" . ($date_return ? "'$date_return'" : "NULL") . ",
                `condition`='$condition',
                status='$status',
                warranty_expiration=" . ($warranty_expiration ? "'$warranty_expiration'" : "NULL") . ",
                remarks='$remarks',
                po='$po',
                date_received=" . ($date_received ? "'$date_received'" : "NULL") . "
                $bast_sql
            WHERE id=$id
        ");
    } else {
        // Insert Laptop
        mysqli_query($conn, "
            INSERT INTO $table (
                model, merk_id, type_id, hostname, serial_number_mobo, processor, memory, storage,
                mac_wifi, mac_lan, monitor, serial_number_monitor,
                assign_to_jde, assign_to_name, assign_to_dept,
                assign_by_jde, assign_by_name, date_assign,
                return_to_jde, return_to_name, return_by_jde, return_by_name, date_return,
                `condition`, status, warranty_expiration, remarks, po, date_received, bast_file
            ) VALUES (
                '$model', '$merk_id', '$type_id', '$hostname', '$serial_number_mobo', '$processor', '$memory', '$storage',
                '$mac_wifi', '$mac_lan', '$monitor', '$serial_number_monitor',
                '$assign_to_jde', '$assign_to_name', '$assign_to_dept',
                '$assign_by_jde', '$assign_by_name', " . ($date_assign ? "'$date_assign'" : "NULL") . ",
                '$return_to_jde', '$return_to_name', '$return_by_jde', '$return_by_name', " . ($date_return ? "'$date_return'" : "NULL") . ",
                '$condition', '$status', " . ($warranty_expiration ? "'$warranty_expiration'" : "NULL") . ",
                '$remarks', '$po', " . ($date_received ? "'$date_received'" : "NULL") . ",
                " . ($bast_file ? "'$bast_file'" : "NULL") . "
            )
        ");
    }
}

header('Location: computer.php?option='.$option);
exit();
?>
