<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_issue_name = trim($_POST['sub_issue_name']);
    $issue_id = intval($_POST['issue_id']);
    if ($sub_issue_name !== '' && $issue_id > 0) {
        $stmt = $conn->prepare("INSERT INTO sub_issues (sub_issue_name, issue_id) VALUES (?, ?)");
        $stmt->bind_param("si", $sub_issue_name, $issue_id);
        $stmt->execute();
        $stmt->close();
    }
}
header("Location: detail_job.php");
exit;
?>
