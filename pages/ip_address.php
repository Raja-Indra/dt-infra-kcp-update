<?php
include '../db.php';
include '../includes/session.php';

// Notifikasi
$error = '';
$success = '';

// --- Logika Pagination ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- Ambil filter dari URL ---
$device_filter = isset($_GET['device']) ? $_GET['device'] : '';
$labeling_filter = isset($_GET['labeling']) ? $_GET['labeling'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Prepared statement untuk DELETE
    $stmt_del = $conn->prepare("DELETE FROM ip_address WHERE id=?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    
    // Pertahankan filter saat redirect
    $query_string = http_build_query([
        'deleted' => 1,
        'page' => $page,
        'device' => $device_filter,
        'labeling' => $labeling_filter,
        'status' => $status_filter
    ]);
    header("Location: ip_address.php?" . $query_string);
    exit;
}

// HAPUS: Logika Handle Insert (dipindah ke ip_address_add.php)

// Tangkap notifikasi
if (isset($_GET['added'])) $success = "IP Address berhasil ditambahkan.";
if (isset($_GET['deleted'])) $success = "IP Address berhasil dihapus.";
if (isset($_GET['updated'])) $success = "IP Address berhasil diperbarui.";

include '../nav.php';

// Handle filter dengan Prepared Statement
$filter = [];
$params = [];
$types = '';

if (!empty($device_filter)) {
    $filter[] = "device LIKE ?";
    $params[] = "%" . $device_filter . "%";
    $types .= "s";
}
if (!empty($labeling_filter)) {
    $filter[] = "labeling LIKE ?";
    $params[] = "%" . $labeling_filter . "%";
    $types .= "s";
}
if (!empty($status_filter)) {
    $filter[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
$where = $filter ? "WHERE " . implode(" AND ", $filter) : "";

// Query Total Data (untuk Pagination)
$sql_total = "SELECT COUNT(*) as total FROM ip_address $where";
$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Query Ambil Data (dengan LIMIT dan OFFSET)
$sql = "SELECT * FROM ip_address $where ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii"; 

$stmt_data = $conn->prepare($sql);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$data = $stmt_data->get_result();

$devices = [
    "Access Point", "Attendance Machine", "CCTV",
    "Printer", "Router", "Server", "Switch", "Wireless Backbone"
];
$statuses = ['Plan', 'Spare', 'Used'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>IP Address Management</title>
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
    <div class="row">
        
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h2 class="h5 mb-0">List IP Address</h2>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="GET" class="row g-2 align-items-end mb-3 border-bottom pb-3">
                        <div class="col-md-3">
                            <label class="form-label">Device</label>
                            <select name="device" class="form-select form-select-sm">
                                <option value="">-- All Devices --</option>
                                <?php foreach ($devices as $d): ?>
                                    <option value="<?= $d ?>" <?= $device_filter === $d ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Labeling</label>
                            <input type="text" name="labeling" class="form-control form-control-sm" value="<?= htmlspecialchars($labeling_filter) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- All Status --</option>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        </div>
                        <div class="col-auto">
                            <a href="ip_address.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                        <div class="col-auto">
                            <a href="ip_address_add.php" class="btn btn-success btn-sm">Add New IP</a>
                        </div>


                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Device</th>
                                    <th>Labeling</th>
                                    <th>MAC Address</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; while ($row = $data->fetch_assoc()) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['device']) ?></td>
                                    <td><?= htmlspecialchars($row['labeling']) ?></td>
                                    <td><?= htmlspecialchars($row['mac_address']) ?></td>
                                    <td><?= htmlspecialchars($row['ip_address']) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                    <td class="text-center text-nowrap">
                                        <?php
                                        $link_params = http_build_query([
                                            'id' => $row['id'],
                                            'page' => $page,
                                            'device' => $device_filter,
                                            'labeling' => $labeling_filter,
                                            'status' => $status_filter
                                        ]);
                                        $delete_params = http_build_query([
                                            'delete' => $row['id'],
                                            'page' => $page,
                                            'device' => $device_filter,
                                            'labeling' => $labeling_filter,
                                            'status' => $status_filter
                                        ]);
                                        ?>
                                        <a href="ip_address_edit.php?<?= $link_params ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="?<?= $delete_params ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
                                    </td>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Script untuk membersihkan notifikasi dari URL
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