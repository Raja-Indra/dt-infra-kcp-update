<?php
include '../db.php';
include '../includes/session.php';

// Notifikasi
$error = '';
$success = '';
if (isset($_GET['added'])) $success = "Radio asset berhasil ditambahkan.";
if (isset($_GET['updated'])) $success = "Radio asset berhasil diperbarui.";
if (isset($_GET['deleted'])) $success = "Radio asset berhasil dihapus.";

// Opsi & Filter
$option = $_GET['option'] ?? 'RIG';
if (!in_array($option, ['RIG', 'HT'])) $option = 'RIG';

$filter_serial = trim($_GET['filter_serial'] ?? '');
$filter_unit = trim($_GET['filter_unit'] ?? '');
$filter_jde = trim($_GET['filter_jde'] ?? '');

// --- Logika Pagination ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- Prepared Statement untuk Keamanan ---
$where = [];
$params = [];
$types = '';

if ($option === 'RIG') {
    $table = 'radio_rig';
    if ($filter_serial !== '') {
        $where[] = "r.serial_number LIKE ?";
        $params[] = "%" . $filter_serial . "%";
        $types .= "s";
    }
    if ($filter_unit !== '') {
        $where[] = "r.unit_number LIKE ?";
        $params[] = "%" . $filter_unit . "%";
        $types .= "s";
    }
} else {
    $table = 'radio_ht';
    if ($filter_serial !== '') {
        $where[] = "r.serial_number LIKE ?";
        $params[] = "%" . $filter_serial . "%";
        $types .= "s";
    }
    if ($filter_jde !== '') {
        $where[] = "r.assign_to_jde LIKE ?";
        $params[] = "%" . $filter_jde . "%";
        $types .= "s";
    }
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// --- Query Total Data (untuk Pagination) ---
$sql_total = "SELECT COUNT(*) as total FROM $table r $where_sql";
$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// --- Query Ambil Data (dengan LIMIT dan OFFSET) ---
$sql = "
    SELECT r.*, m.merk_name, t.type_name
    FROM $table r
    LEFT JOIN merk_asset m ON r.merk_id = m.id
    LEFT JOIN type_asset t ON r.type_id = t.id
    $where_sql
    ORDER BY r.id DESC
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$types .= "ii"; 

$stmt_data = $conn->prepare($sql);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$data = $stmt_data->get_result();

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Radio Assets - <?= htmlspecialchars($option) ?></title>
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
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="h5 mb-0">Radio Assets - <?= htmlspecialchars($option) ?></h2>
            </div>
            <div class="card-body">
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="get" action="" class="mb-3 border-bottom pb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="option" class="form-label">Radio Type</label>
                            <select name="option" id="option" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="RIG" <?= $option == 'RIG' ? 'selected' : '' ?>>RIG</option>
                                <option value="HT" <?= $option == 'HT' ? 'selected' : '' ?>>HT</option>
                            </select>
                        </div>
                        
                        <?php if ($option === 'RIG'): ?>
                            <div class="col-md-3">
                                <label for="filter_serial" class="form-label">Serial Number</label>
                                <input type="text" name="filter_serial" id="filter_serial" class="form-control form-control-sm" placeholder="Search Serial..." value="<?= htmlspecialchars($filter_serial) ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_unit" class="form-label">Unit Number</label>
                                <input type="text" name="filter_unit" id="filter_unit" class="form-control form-control-sm" placeholder="Search Unit..." value="<?= htmlspecialchars($filter_unit) ?>">
                            </div>
                        <?php else: ?>
                            <div class="col-md-3">
                                <label for="filter_serial" class="form-label">Serial Number</label>
                                <input type="text" name="filter_serial" id="filter_serial" class="form-control form-control-sm" placeholder="Search Serial..." value="<?= htmlspecialchars($filter_serial) ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_jde" class="form-label">Assign to JDE</label>
                                <input type="text" name="filter_jde" id="filter_jde" class="form-control form-control-sm" placeholder="Search JDE..." value="<?= htmlspecialchars($filter_jde) ?>">
                            </div>
                        <?php endif; ?>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        </div>
                        <div class="col-auto">
                            <a href="radio.php?option=<?= $option ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                        <div class="col-auto">
                            <a href="radio_input.php?option=<?= $option ?>" class="btn btn-success btn-sm">Input Data <?= $option ?></a>
                        </div>


                    </div>
                </form>

                <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                    <table class="table table-sm table-bordered table-striped table-hover">
                        
                        <thead class="table-light sticky-top">
                        <?php if ($option === 'RIG'): ?>
                            <tr>
                                <th>No</th>
                                <th>Model</th>
                                <th>Merk</th>
                                <th>Type</th>
                                <th>Serial Number</th>
                                <th>Unit Number</th>
                                <th>Unit Type</th>
                                <th>Installed By JDE</th>
                                <th>Installed By Name</th>
                                <th>Date Installed</th>
                                <th>Removed By JDE</th>
                                <th>Removed By Name</th>
                                <th>Date Removed</th>
                                <th>Condition</th>
                                <th>Status</th>
                                <th>Warranty</th>
                                <th>Remarks</th>
                                <th>PO</th>
                                <th>Date Received</th>
                                <th>Action</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>No</th>
                                <th>Model</th>
                                <th>Merk</th>
                                <th>Type</th>
                                <th>Serial Number</th>
                                <th>Assign To JDE</th>
                                <th>Assign To Name</th>
                                <th>Assign To Dept</th>
                                <th>Assign By JDE</th>
                                <th>Assign By Name</th>
                                <th>Date Assign</th>
                                <th>Return To JDE</th>
                                <th>Return To Name</th>
                                <th>Return By JDE</th>
                                <th>Return By Name</th>
                                <th>Date Return</th>
                                <th>Condition</th>
                                <th>Status</th>
                                <th>Warranty</th>
                                <th>Remarks</th>
                                <th>PO</th>
                                <th>Date Received</th>
                                <th>BAST</th>
                                <th>Action</th>
                            </tr>
                        <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1; 
                            while($row = $data->fetch_assoc()): 
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['model']) ?></td>
                                <td><?= htmlspecialchars($row['merk_name']) ?></td>
                                <td><?= htmlspecialchars($row['type_name']) ?></td>
                                <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                <?php if($option==='RIG'): ?>
                                    <td><?= htmlspecialchars($row['unit_number']) ?></td>
                                    <td><?= htmlspecialchars($row['unit_type']) ?></td>
                                    <td><?= htmlspecialchars($row['installed_by_jde']) ?></td>
                                    <td><?= htmlspecialchars($row['installed_by_name']) ?></td>
                                    <td><?= htmlspecialchars($row['date_installed']) ?></td>
                                    <td><?= htmlspecialchars($row['removed_by_jde']) ?></td>
                                    <td><?= htmlspecialchars($row['removed_by_name']) ?></td>
                                    <td><?= htmlspecialchars($row['date_removed']) ?></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($row['assign_to_jde']) ?></td>
                                    <td><?= htmlspecialchars($row['assign_to_name']) ?></td>
                                    <td><?= htmlspecialchars($row['assign_to_dept']) ?></td>
                                    <td><?= htmlspecialchars($row['assign_by_jde']) ?></td>
                                    <td><?= htmlspecialchars($row['assign_by_name']) ?></td>
                                    <td><?= htmlspecialchars($row['date_assign']) ?></td>
                                    <td><?= htmlspecialchars($row['return_to_jde']) ?></td>
                                    <td><?= htmlspecialchars($row['return_to_name']) ?></td>
                                    <td><?= htmlspecialchars($row['return_by_jde']) ?></td>
                                    <td><?= htmlspecialchars($row['return_by_name']) ?></td>
                                    <td><?= htmlspecialchars($row['date_return']) ?></td>
                                <?php endif; ?>
                                
                                <td><?= htmlspecialchars($row['condition']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td><?= htmlspecialchars($row['warranty_expiration']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['remarks'])) ?></td>
                                <td><?= htmlspecialchars($row['po']) ?></td>
                                <td><?= htmlspecialchars($row['date_received']) ?></td>

                                <?php if($option==='HT'): ?>
                                    <td class="text-center">
                                        <?php if(!empty($row['bast_file'])): ?>
                                            <a href="../uploads/<?= htmlspecialchars($row['bast_file']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">View</a>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <td class="text-center text-nowrap">
                                    <?php
                                    $link_params = http_build_query([
                                        'option' => $option,
                                        'id' => $row['id'],
                                        'page' => $page,
                                        'filter_serial' => $filter_serial,
                                        'filter_unit' => $filter_unit,
                                        'filter_jde' => $filter_jde
                                    ]);
                                    $delete_params = http_build_query([
                                        'option' => $option,
                                        'id' => $row['id']
                                    ]);
                                    ?>
                                    <a href="radio_input.php?<?= $link_params ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="radio_delete.php?<?= $delete_params ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
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