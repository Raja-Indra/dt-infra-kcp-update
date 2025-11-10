<?php
include '../db.php';
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM sub_issues WHERE id = $id");
}
header("Location: detail_job.php");
exit;
?>
