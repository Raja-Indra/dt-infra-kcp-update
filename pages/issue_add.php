<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_name = trim($_POST['issue_name']);
    if ($issue_name !== '') {
        $stmt = $conn->prepare("INSERT INTO issues (issue_name) VALUES (?)");
        $stmt->bind_param("s", $issue_name);
        $stmt->execute();
        $stmt->close();
    }
}
// Redirect balik ke detail_job.php (atau nama halaman kamu)
header("Location: detail_job.php");
exit;
?>
