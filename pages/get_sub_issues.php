<?php
include '../db.php';
if (isset($_GET['issue_id'])) {
    $issue_id = intval($_GET['issue_id']);
    $result = mysqli_query($conn, "SELECT * FROM sub_issues WHERE issue_id = $issue_id ORDER BY sub_issue_name");
    echo '<option value="">Select</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['sub_issue_name'] . '">' . $row['sub_issue_name'] . '</option>';
    }
}
?>