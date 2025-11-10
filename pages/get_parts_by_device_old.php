<?php
include '../db.php';

if (!isset($_GET['device'])) {
    http_response_code(400);
    echo "Missing device parameter.";
    exit;
}

$device = mysqli_real_escape_string($conn, $_GET['device']);
$query = mysqli_query($conn, "SELECT part_name FROM sub_parts 
    JOIN parts ON sub_parts.part_id = parts.id 
    WHERE parts.device = '$device' 
    ORDER BY part_name");

while ($row = mysqli_fetch_assoc($query)) {
    echo "<option value='" . htmlspecialchars($row['part_name']) . "'>" . htmlspecialchars($row['part_name']) . "</option>";
}
?>