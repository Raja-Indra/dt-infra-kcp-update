<?php
include '../db.php';
$issue_id = isset($_GET['issue_id']) ? intval($_GET['issue_id']) : 0;
$result = mysqli_query($conn, "SELECT id, sub_issue_name FROM sub_issues WHERE issue_id = $issue_id ORDER BY sub_issue_name");
$list = [];
while ($r = mysqli_fetch_assoc($result)) {
    $list[] = ['id' => $r['id'], 'name' => $r['sub_issue_name']];
}
header('Content-Type: application/json');
echo json_encode($list);
