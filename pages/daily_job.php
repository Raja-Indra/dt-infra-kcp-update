<?php
session_start();
include '../db.php';
include '../nav.php';

// Notifikasi
$error = '';
$success = '';
if (isset($_GET['added'])) $success = "Daily job berhasil ditambahkan.";
if (isset($_GET['updated'])) $success = "Daily job berhasil diperbarui.";
if (isset($_GET['deleted'])) $success = "Daily job berhasil dihapus.";

// --- UBAH: Logika Pagination ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- UBAH: Ambil filter dari URL ---
$filter_date = $_GET['filter_date'] ?? '';
$report_by_jde = $_GET['report_by_jde'] ?? '';
$ticket = $_GET['ticket'] ?? '';
$status = $_GET['status'] ?? 'All';

// --- UBAH: Prepared Statement untuk Keamanan ---
$filters = [];
$params = [];
$types = '';

// Default filter hari ini jika tidak ada filter lain
if (empty($filter_date) && empty($report_by_jde) && empty($ticket) && $status === 'All') {
    $filters[] = "dj.date_report = ?";
    $params[] = date('Y-m-d');
    $types .= "s";
} else {
    if (!empty($filter_date)) {
        $filters[] = "dj.date_report = ?";
        $params[] = $filter_date;
        $types .= "s";
    }
    if (!empty($report_by_jde)) {
        $filters[] = "dj.report_by_jde LIKE ?";
        $params[] = "%" . $report_by_jde . "%";
        $types .= "s";
    }
    if (!empty($ticket)) {
        $filters[] = "dj.no_ticket LIKE ?";
        $params[] = "%" . $ticket . "%";
        $types .= "s";
    }
    if (!empty($status) && $status !== 'All') {
        $filters[] = "dj.status = ?";
        $params[] = $status;
        $types .= "s";
    }
}
$where = $filters ? "WHERE " . implode(" AND ", $filters) : "";


// --- UBAH: Query Total Data (untuk Pagination) ---
$sql_total = "
    SELECT COUNT(dj.id) as total 
    FROM daily_job dj
    LEFT JOIN issues i ON dj.issues = i.id
    $where
";
$stmt_total = $conn->prepare($sql_total);
if ($filters) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);


// --- UBAH: Query Ambil Data (dengan LIMIT dan OFFSET) ---
// Menggunakan GROUP_CONCAT untuk menggabungkan parts (menghindari N+1 query)
$sql = "
    SELECT 
        dj.*, 
        i.issue_name,
        GROUP_CONCAT(djp.device ORDER BY djp.id SEPARATOR '||') AS devices,
        GROUP_CONCAT(djp.part_name ORDER BY djp.id SEPARATOR '||') AS parts,
        GROUP_CONCAT(djp.qty ORDER BY djp.id SEPARATOR '||') AS qtys,
        GROUP_CONCAT(si.sub_issue_name ORDER BY si.id SEPARATOR ', ') AS sub_issue_names
    FROM daily_job dj
    LEFT JOIN issues i ON dj.issues = i.id
    LEFT JOIN daily_job_parts djp ON dj.id = djp.daily_job_id
    LEFT JOIN sub_issues si ON FIND_IN_SET(si.id, dj.sub_issues)
    $where
    GROUP BY dj.id
    ORDER BY dj.id DESC
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$types .= "ii"; 

$stmt_data = $conn->prepare($sql);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$data = $stmt_data->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Daily Job</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: linear-gradient(to bottom, #FFE0C0, #FF6600);
        min-height: 100vh;
        font-size: 13px;
    }
    .table-sm th, .table-sm td {
        padding: 0.4rem;
        white-space: nowrap; 
        vertical-align: middle;
    }
    .btn-outline-secondary {
        color: #333;
        border-color: #aaa;
    }
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .pagination {
        justify-content: center;
        margin-top: 15px;
    }
    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #90ca4b; 
        border-color: #90ca4b;
    }
    .page-link {
        color: #90ca4b; 
    }
    .page-link:hover {
        color: #5a8e1d;
    }
    /* Untuk kolom part yang di-join */
    .part-cell {
        white-space: normal; /* Izinkan wrap */
        min-width: 150px;
    }
    .card-header {
        /* Gradien oranye (dari oranye cerah ke oranye gelap) */
        background: linear-gradient(to right, #ff8800, #ff6b15ff);
        color: white; /* Ganti warna teks di dalam header menjadi putih */
    }

    .card-header h2 {
        color: white; /* Pastikan teks h2 juga putih */
    }
</style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h2 class="h5 mb-0">Daily Job</h2>
        </div>
        <div class="card-body">

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="GET" class="mb-3 border-bottom pb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label for="filter_date" class="form-label">Date</label>
                        <input type="date" name="filter_date" id="filter_date" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="report_by_jde" class="form-label">Reporter (JDE)</label>
                        <input type="text" name="report_by_jde" id="report_by_jde" class="form-control form-control-sm" placeholder="Enter JDE" value="<?= htmlspecialchars($_GET['report_by_jde'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="ticket" class="form-label">No Ticket</label>
                        <input type="text" name="ticket" id="ticket" class="form-control form-control-sm" placeholder="Enter Ticket No" value="<?= htmlspecialchars($_GET['ticket'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm">
                            <option value="All" <?= $status === 'All' ? 'selected' : '' ?>>All</option>
                            <option value="Open" <?= $status === 'Open' ? 'selected' : '' ?>>Open</option>
                            <option value="Progress" <?= $status === 'Progress' ? 'selected' : '' ?>>Progress</option>
                            <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Close" <?= $status === 'Close' ? 'selected' : '' ?>>Close</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    </div>
                    <div class="col-auto">
                        <a href="daily_job.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                    <div class="col-auto">
                        <a href="daily_job_input.php" class="btn btn-success btn-sm">New Input</a>
                    </div>

                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>No Ticket</th>
                            <th>Issues</th>
                            <th>Sub Issues</th>
                            <th>Description</th>
                            <th>Report By JDE</th>
                            <th>Report By Name</th>
                            <th>Report By Dept</th>
                            <th>Time Report</th>
                            <th>Date Report</th>
                            <th>Action By JDE</th>
                            <th>Action By Name</th>
                            <th>Time Action</th>
                            <th>Date Action</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Time Close</th>
                            <th>Date Close</th>
                            <th>Use Part</th>
                            <th class="part-cell">Device</th>
                            <th class="part-cell">Part Name</th>
                            <th class="part-cell">Qty</th>
                            <th>Resolution</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1; 
                        while ($row = $data->fetch_assoc()) : ?>
                            <?php
                            // Kalkulasi Durasi
                            $duration = '-';
                            if (!empty($row['date_report']) && !empty($row['time_report']) && !empty($row['date_close']) && !empty($row['time_close'])) {
                                $start = strtotime($row['date_report'] . ' ' . $row['time_report']);
                                $end = strtotime($row['date_close'] . ' ' . $row['time_close']);
                                if ($start && $end && $end > $start) {
                                    $diff = $end - $start;
                                    $hours = floor($diff / 3600);
                                    $minutes = floor(($diff % 3600) / 60);
                                    $duration = "{$hours}h {$minutes}m";
                                }
                            }
                            
                            // Ubah hasil GROUP_CONCAT menjadi array
                            $device_list = !empty($row['devices']) ? explode('||', $row['devices']) : [];
                            $part_list = !empty($row['parts']) ? explode('||', $row['parts']) : [];
                            $qty_list = !empty($row['qtys']) ? explode('||', $row['qtys']) : [];
                            
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['no_ticket']) ?></td>
                                <td><?= htmlspecialchars($row['issue_name']) ?></td>
                                <td class="part-cell"><?= htmlspecialchars($row['sub_issue_names'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['report_by_jde']) ?></td>
                                <td><?= htmlspecialchars($row['report_by_name']) ?></td>
                                <td><?= htmlspecialchars($row['report_by_department']) ?></td>
                                <td><?= htmlspecialchars($row['time_report']) ?></td>
                                <td><?= htmlspecialchars($row['date_report']) ?></td>
                                <td><?= htmlspecialchars($row['action_by_jde']) ?></td>
                                <td><?= htmlspecialchars($row['action_by_name']) ?></td>
                                <td><?= htmlspecialchars($row['time_action']) ?></td>
                                <td><?= htmlspecialchars($row['date_action']) ?></td>
                                <td><?= $duration ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td><?= htmlspecialchars($row['time_close']) ?></td>
                                <td><?= htmlspecialchars($row['date_close']) ?></td>
                                <td class="text-center"><?= $row['use_part'] ? '✔' : '' ?></td>
                                <td class="part-cell"><?= implode('<br>', array_map('htmlspecialchars', $device_list)) ?></td>
                                <td class="part-cell"><?= implode('<br>', array_map('htmlspecialchars', $part_list)) ?></td>
                                <td class="part-cell"><?= implode('<br>', array_map('htmlspecialchars', $qty_list)) ?></td>
                                <td><?= htmlspecialchars($row['resolution']) ?></td>
                                </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination pagination-sm">
                    <?php
                    $query_params = $_GET;
                    unset($query_params['page'], $query_params['added'], $query_params['updated'], $query_params['deleted']); 
                    $base_url = http_build_query($query_params);
                    $base_url = $base_url ? $base_url . '&' : '';
                    
                    $prev_page = $page - 1;
                    echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">';
                    echo '<a class="page-link" href="?' . $base_url . 'page=' . $prev_page . '">Previous</a>';
                    echo '</li>';

                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                        echo '<a class="page-link" href="?' . $base_url . 'page=' . $i . '">' . $i . '</a>';
                        echo '</li>';
                    }

                    $next_page = $page + 1;
                    echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '">';
                    echo '<a class="page-link" href="?' . $base_url . 'page=' . $next_page . '">Next</a>';
                    echo '</li>';
                    ?>
                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Hapus JS kustom, karena tombol Search kini ada di dalam form filternya.

// UBAH: Script untuk membersihkan notifikasi dari URL
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.search.includes("updated=1") || window.location.search.includes("added=1") || window.location.search.includes("deleted=1")) {
        const url = new URL(window.location);
        url.searchParams.delete('updated');
        url.searchParams.delete('added');
        url.searchParams.delete('deleted');
        window.history.replaceState({}, document.title, url);
    }
});
</script>
</body>
</html>