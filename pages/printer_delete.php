<?php
include '../db.php';
include '../includes/session.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM printer WHERE id = $id");
}

header("Location: printer.php");
exit();
?>
