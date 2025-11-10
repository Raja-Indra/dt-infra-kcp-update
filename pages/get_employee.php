
<?php
include '../db.php';

$jde = $_GET['jde'] ?? '';
$response = ['name' => '', 'department' => '','position' => ''];

if ($jde) {
    $query = mysqli_query($conn, "SELECT name, department, position FROM employee WHERE jde = '$jde' LIMIT 1");
    if ($row = mysqli_fetch_assoc($query)) {
        $response['name'] = $row['name'];
        $response['department'] = $row['department'];
        $response['position'] = $row['position'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
