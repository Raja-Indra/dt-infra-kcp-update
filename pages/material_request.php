<?php
include '../db.php';
include '../includes/session.php';

// Notifikasi
$error = '';
$success = '';

// --- UBAH: Logika Pagination ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- UBAH: Ambil filter dari URL ---
$filter_pr = isset($_GET['filter_pr']) ? trim($_GET['filter_pr']) : '';
$filter_po = isset($_GET['filter_po']) ? trim($_GET['filter_po']) : '';

// handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // UBAH: Prepared statement
    $stmt = $conn->prepare("DELETE FROM material_request WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // UBAH: Pertahankan filter dan page saat redirect
    $query_string = http_build_query([
        'deleted' => 1,
        'page' => $page,
        'filter_pr' => $filter_pr,
        'filter_po' => $filter_po
    ]);
    header("Location: material_request.php?" . $query_string);
    exit;
}

// Tangkap notifikasi
if(isset($_GET['added'])) $success = "Material request berhasil ditambahkan.";
if(isset($_GET['updated'])) $success = "Material request berhasil diperbarui.";
if(isset($_GET['deleted'])) $success = "Material request berhasil dihapus.";


// UBAH: build filter query dengan Prepared Statement
$where = [];
$params = [];
$types = '';
if (!empty($filter_pr)) {
    $where[] = "pr LIKE ?";
    $params[] = "%" . $filter_pr . "%";
    $types .= "s";
}
if (!empty($filter_po)) {
    $where[] = "po LIKE ?";
    $params[] = "%" . $filter_po . "%";
    $types .= "s";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";


// --- UBAH: Query Total Data (untuk Pagination) ---
$sql_total = "SELECT COUNT(*) as total FROM material_request $where_sql";
$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);


// --- UBAH: Query Ambil Data (dengan LIMIT dan OFFSET) ---
$sql = "SELECT * FROM material_request $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
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
    <title>Material Request List</title>
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
                <h2 class="h5 mb-0">Material Request List</h2>
            </div>
            <div class="card-body">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="GET" class="row g-2 align-items-end mb-3 border-bottom pb-3">
                    <div class="col-md-3">
                        <label for="filter_pr" class="form-label">Filter PR</label>
                        <input type="text" name="filter_pr" id="filter_pr" class="form-control form-control-sm" placeholder="Filter PR" value="<?= htmlspecialchars($filter_pr) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filter_po" class="form-label">Filter PO</label>
                        <input type="text" name="filter_po" id="filter_po" class="form-control form-control-sm" placeholder="Filter PO" value="<?= htmlspecialchars($filter_po) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
                    </div>
                    <div class="col-auto">
                        <a href="material_request.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                    <div class="col-auto">
                        <a href="material_request_add.php" class="btn btn-success btn-sm">+ Add Material Request</a>

                    </div>


                </form>
                
                <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                    <table class="table table-sm table-striped table-bordered table-hover">
                        
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-nowrap text-center">No</th>
                                <th class="text-nowrap">Device</th>
                                <th class="text-nowrap">Part Name</th>
                                <th class="text-nowrap">Description</th>
                                <th class="text-nowrap">Qty</th>
                                <th class="text-nowrap">UoM</th>
                                <th class="text-nowrap">Est. Cost</th>
                                <th class="text-nowrap">Total Est. Cost</th>
                                <th class="text-nowrap">Item Type</th>
                                <th class="text-nowrap">Date Created</th>
                                <th class="text-nowrap">PR</th>
                                <th class="text-nowrap">PO</th>
                                <th class="text-nowrap">Qty Received</th>
                                <th class="text-nowrap">Qty Deviation</th>
                                <th class="text-nowrap">Date Received</th>
                                <th class="text-nowrap">Cost Actual</th>
                                <th class="text-nowrap">Order Process</th>
                                <th class="text-nowrap">Remarks</th>
                                <th class="text-nowrap text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1; 
                            while($r = $data->fetch_assoc()): 
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($r['device']) ?></td>
                                <td><?= htmlspecialchars($r['part_name']) ?></td>
                                <td><?= htmlspecialchars($r['description']) ?></td>
                                <td><?= number_format($r['qty'],0,',','.') ?></td>
                                <td><?= htmlspecialchars($r['uom']) ?></td>
                                <td><?= number_format($r['est_cost'],2,',','.') ?></td>
                                <td><?= number_format($r['total_est_cost'],2,',','.') ?></td>
                                <td><?= htmlspecialchars($r['item_type']) ?></td>
                                <td><?= $r['date_created'] ?></td>
                                <td><?= htmlspecialchars($r['pr']) ?></td>
                                <td><?= htmlspecialchars($r['po']) ?></td>
                                <td><?= number_format($r['qty_received'],0,',','.') ?></td>
                                <td><?= number_format($r['qty_deviation'],0,',','.') ?></td>
                                <td><?= $r['date_received'] ?></td>
                                <td><?= number_format($r['cost_actual'],2,',','.') ?></td>
                                <td><?= htmlspecialchars($r['order_process']) ?></td>
                                <td><?= htmlspecialchars($r['remarks']) ?></td>
                                <td class="text-center text-nowrap">
                                    <?php
                                    $link_params = http_build_query([
                                        'id' => $r['id'],
                                        'page' => $page,
                                        'filter_pr' => $filter_pr,
                                        'filter_po' => $filter_po
                                    ]);
                                    $delete_params = http_build_query([
                                        'delete' => $r['id'],
                                        'page' => $page,
                                        'filter_pr' => $filter_pr,
                                        'filter_po' => $filter_po
                                    ]);
                                    ?>
                                    <a href="material_request_edit.php?<?= $link_params ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="?<?= $delete_params ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data?')">Delete</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>