<?php
include '../db.php';
include '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $option = $_POST['option'];
    if (!in_array($option, ['RIG','HT'])) {
        die("Invalid Option");
    }

    $is_edit = isset($_POST['id']) && intval($_POST['id']) > 0;
    $id = $is_edit ? intval($_POST['id']) : 0;

    // Common fields
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $merk_id = intval($_POST['merk_id']);
    $type_id = intval($_POST['type_id']);
    $serial_number = mysqli_real_escape_string($conn, $_POST['serial_number']);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $warranty = mysqli_real_escape_string($conn, $_POST['warranty_expiration']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $po = mysqli_real_escape_string($conn, $_POST['po']);
    $date_received = mysqli_real_escape_string($conn, $_POST['date_received']);

    $table = $option === 'RIG' ? 'radio_rig' : 'radio_ht';

    // Prepare fields


$fields = [
    "`model`='$model'",
    "`merk_id`=$merk_id",
    "`type_id`=$type_id",
    "`serial_number`='$serial_number'",
    "`condition`='$condition'",
    "`status`='$status'",
    "`warranty_expiration`='$warranty'",
    "`remarks`='$remarks'",
    "`po`='$po'",
    "`date_received`='$date_received'"
];

    if ($option === 'RIG') {
        $unit_number = mysqli_real_escape_string($conn, $_POST['unit_number']);
        $unit_type = mysqli_real_escape_string($conn, $_POST['unit_type']);
        $installed_jde = mysqli_real_escape_string($conn, $_POST['installed_by_jde']);
        $installed_name = mysqli_real_escape_string($conn, $_POST['installed_by_name']);
        $date_installed = mysqli_real_escape_string($conn, $_POST['date_installed']);
        $removed_jde = mysqli_real_escape_string($conn, $_POST['removed_by_jde']);
        $removed_name = mysqli_real_escape_string($conn, $_POST['removed_by_name']);
        $date_removed = mysqli_real_escape_string($conn, $_POST['date_removed']);

        $fields = array_merge($fields, [
            "unit_number='$unit_number'",
            "unit_type='$unit_type'",
            "installed_by_jde='$installed_jde'",
            "installed_by_name='$installed_name'",
            "date_installed='$date_installed'",
            "removed_by_jde='$removed_jde'",
            "removed_by_name='$removed_name'",
            "date_removed='$date_removed'"
        ]);
    } else {
        $assign_to_jde = mysqli_real_escape_string($conn, $_POST['assign_to_jde']);
        $assign_to_name = mysqli_real_escape_string($conn, $_POST['assign_to_name']);
        $assign_to_dept = mysqli_real_escape_string($conn, $_POST['assign_to_dept']);
        $assign_by_jde = mysqli_real_escape_string($conn, $_POST['assign_by_jde']);
        $assign_by_name = mysqli_real_escape_string($conn, $_POST['assign_by_name']);
        $date_assign = mysqli_real_escape_string($conn, $_POST['date_assign']);
        $return_to_jde = mysqli_real_escape_string($conn, $_POST['return_to_jde']);
        $return_to_name = mysqli_real_escape_string($conn, $_POST['return_to_name']);
        $return_by_jde = mysqli_real_escape_string($conn, $_POST['return_by_jde']);
        $return_by_name = mysqli_real_escape_string($conn, $_POST['return_by_name']);
        $date_return = mysqli_real_escape_string($conn, $_POST['date_return']);

        $fields = array_merge($fields, [
            "assign_to_jde='$assign_to_jde'",
            "assign_to_name='$assign_to_name'",
            "assign_to_dept='$assign_to_dept'",
            "assign_by_jde='$assign_by_jde'",
            "assign_by_name='$assign_by_name'",
            "date_assign='$date_assign'",
            "return_to_jde='$return_to_jde'",
            "return_to_name='$return_to_name'",
            "return_by_jde='$return_by_jde'",
            "return_by_name='$return_by_name'",
            "date_return='$date_return'"
        ]);

        // BAST File Upload
        if (!empty($_FILES['bast_file']['name'])) {
            $bast_name = time() . '_' . basename($_FILES['bast_file']['name']);
            $target = "../uploads/" . $bast_name;
            move_uploaded_file($_FILES['bast_file']['tmp_name'], $target);
            $fields[] = "bast_file='$bast_name'";
        }
    }

    if ($is_edit) {
        // UPDATE
        $sql = "UPDATE $table SET ".implode(',', $fields)." WHERE id=$id";
    } else {
        // INSERT
        $sql = "INSERT INTO $table SET ".implode(',', $fields);
    }

    mysqli_query($conn, $sql) or die(mysqli_error($conn));

    header("Location: radio.php?option=$option");
    exit();
}
?>
