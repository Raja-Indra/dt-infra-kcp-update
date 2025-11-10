<?php
// File: search_employee.php
include '../db.php'; 

// Asumsi Anda punya tabel 'employees' dengan kolom 'jde', 'name', dan 'department'
// SESUAIKAN NAMA TABEL DAN KOLOM JIKA PERLU
$table_name = "employee";
$col_name = "name";
$col_jde = "jde";
$col_dept = "department";


$term = $_GET['term'] ?? '';
$results = [];

if (strlen($term) > 1) {
    $search_term = "%" . $term . "%";
    
    // Gunakan prepared statement
    $stmt = $conn->prepare(
        "SELECT $col_jde, $col_name, $col_dept 
         FROM $table_name 
         WHERE $col_name LIKE ? OR $col_jde LIKE ? 
         LIMIT 10"
    );
    
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = [
            'jde' => $row[$col_jde],
            'name' => $row[$col_name],
            'department' => $row[$col_dept]
        ];
    }
    
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($results);
?>