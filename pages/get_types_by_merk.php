<?php
include '../db.php';

$merk_id = intval($_GET['merk_id'] ?? 0);
if ($merk_id <= 0) {
    echo json_encode([]);
    exit;
}

$result = mysqli_query($conn, "SELECT id, type_name FROM type_asset WHERE merk_id = $merk_id ORDER BY type_name");
$types = [];
while($row = mysqli_fetch_assoc($result)){
    $types[] = $row;
}

header('Content-Type: application/json');
echo json_encode($types);
