<?php
include '../db.php'; // Pastikan path ini benar

$device = isset($_GET['device']) ? trim($_GET['device']) : '';
$options = '<option value="">-- Select Device First --</option>';

if ($device !== '') {
    // 1. Ambil part_id dari device (Gunakan prepared statement)
    $stmt_part = $conn->prepare("SELECT id FROM parts WHERE device = ? LIMIT 1");
    $stmt_part->bind_param("s", $device);
    $stmt_part->execute();
    $result_part = $stmt_part->get_result();

    if ($row = $result_part->fetch_assoc()) {
        $part_id = intval($row['id']);
        
        // 2. Ambil part_name dari sub_parts
        $stmt_sub = $conn->prepare("SELECT part_name FROM sub_parts WHERE part_id = ? ORDER BY part_name");
        $stmt_sub->bind_param("i", $part_id);
        $stmt_sub->execute();
        $sub = $stmt_sub->get_result();

        $options = '<option value="">-- Select Part Name --</option>';
        if ($sub->num_rows > 0) {
            while ($s = $sub->fetch_assoc()) {
                $name_esc = htmlspecialchars($s['part_name']);
                
                // HANYA MENGIRIM VALUE (TIDAK ADA data-desc)
                $options .= "<option value=\"$name_esc\">$name_esc</option>";
            }
        } else {
             $options = '<option value="">-- No parts found --</option>';
        }
    } else {
        $options = '<option value="">-- Invalid device --</option>';
    }
}

echo $options;
?>