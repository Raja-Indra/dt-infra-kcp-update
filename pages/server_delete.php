<?php
include '../db.php';
include '../includes/session.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM server WHERE id = $id");
}

header("Location: server.php");
exit();
?>
