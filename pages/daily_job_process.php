<?php
include '../db.php';
include '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_ticket = $_POST['no_ticket'];
    $issues = $_POST['issues'];
    $sub_issues = $_POST['sub_issues'];
    $description = $_POST['description'];
    $report_by_jde = $_POST['report_by_jde'];
    $report_by_name = $_POST['report_by_name'];
    $report_by_department = $_POST['report_by_department'];
    $time_report = $_POST['time_report'];
    $date_report = $_POST['date_report'];
    $action_by_jde = $_POST['action_by_jde'];
    $action_by_name = $_POST['action_by_name'];
    $time_action = $_POST['time_action'];
    $date_action = $_POST['date_action'];
    $status = $_POST['status'];
    $time_close = $_POST['time_close'] ?? null;
    $date_close = $_POST['date_close'] ?? null;
    $use_part = isset($_POST['use_part']) ? 1 : 0;
    $resolution = $_POST['resolution'] ?? '';

    // Kalkulasi Duration of Action
    $duration_of_action = null;
    if ($date_report && $time_report && $date_close && $time_close) {
        $start = strtotime("$date_report $time_report");
        $end = strtotime("$date_close $time_close");
        if ($start !== false && $end !== false && $end >= $start) {
            $seconds = $end - $start;
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $duration_of_action = "{$hours}h {$minutes}m";
        }
    }

    // Simpan data utama ke daily_job (TANPA device, part_name, qty)
    $query = "
        INSERT INTO daily_job (
            no_ticket, issues, sub_issues, description, 
            report_by_jde, report_by_name, report_by_department,
            time_report, date_report, 
            action_by_jde, action_by_name, 
            time_action, date_action, 
            status, time_close, date_close, duration_of_action,
            use_part, resolution
        ) VALUES (
            '$no_ticket', '$issues', '$sub_issues', '$description',
            '$report_by_jde', '$report_by_name', '$report_by_department',
            '$time_report', '$date_report',
            '$action_by_jde', '$action_by_name',
            '$time_action', '$date_action',
            '$status', " . ($time_close ? "'$time_close'" : "NULL") . ", " . ($date_close ? "'$date_close'" : "NULL") . ",
            " . ($duration_of_action ? "'$duration_of_action'" : "NULL") . ",
            '$use_part', '$resolution'
        )";

    $insert_main = mysqli_query($conn, $query);

    if ($insert_main) {
        $daily_job_id = mysqli_insert_id($conn);

        // Simpan part hanya jika use_part dicentang
        if ($use_part && isset($_POST['device']) && is_array($_POST['device'])) {
            foreach ($_POST['device'] as $i => $dev) {
                $dev = mysqli_real_escape_string($conn, $dev);
                $part = mysqli_real_escape_string($conn, $_POST['part_name'][$i]);
                $qty = intval($_POST['qty'][$i]);

                if (!empty($dev) && !empty($part) && $qty > 0) {
                    mysqli_query($conn, "
                        INSERT INTO daily_job_parts (daily_job_id, device, part_name, qty) 
                        VALUES ('$daily_job_id', '$dev', '$part', '$qty')
                    ");
                }
            }
        }

        header('Location: daily_job.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header('Location: daily_job.php');
    exit();
}
?>
