<?php
include '../db.php';
include '../includes/session.php';
// nav.php dipindah ke bawah setelah logika PHP selesai

// UBAH: Notifikasi
$error = '';
$success = '';
if(isset($_GET['added'])) $success = "Printer asset berhasil ditambahkan.";
if(isset($_GET['updated'])) $success = "Printer asset berhasil diperbarui.";
if(isset($_GET['deleted'])) $success = "Printer asset berhasil dihapus.";

// Filter
$model = $_GET['model'] ?? '';
$serial = $_GET['serial'] ?? '';
$merk = $_GET['merk'] ?? '';

// --- UBAH: Logika Pagination ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- UBAH: Prepared Statement untuk Keamanan ---
$where = [];
$params = [];
$types = '';

if($model) {
    $where[] = "p.model = ?";
    $params[] = $model;
    $types .= "s";
}
if($serial) {
    $where[] = "p.serial_number LIKE ?";
    $params[] = "%" . $serial . "%";
    $types .= "s";
}
if($merk) {
    $where[] = "m.id = ?";
    $params[] = $merk;
    $types .= "i";
}
$where_sql = $where ? "WHERE ".implode(" AND ", $where) : "";

// --- UBAH: Query Total Data (untuk Pagination) ---
$sql_total = "
    SELECT COUNT(*) as total
    FROM printer p
    LEFT JOIN merk_asset m ON p.merk_id = m.id
    LEFT JOIN type_asset t ON p.type_id = t.id
    $where_sql
";
$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// --- UBAH: Query Ambil Data (dengan LIMIT dan OFFSET) ---
$sql = "
    SELECT p.*, m.merk_name, t.type_name
    FROM printer p
    LEFT JOIN merk_asset m ON p.merk_id = m.id
    LEFT JOIN type_asset t ON p.type_id = t.id
    $where_sql
    ORDER BY p.id DESC
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$types .= "ii"; 

$stmt_data = $conn->prepare($sql);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$data = $stmt_data->get_result();

$merk_list = mysqli_query($conn,"SELECT id, merk_name FROM merk_asset WHERE category='Printer' ORDER BY merk_name");

// UBAH: Pindah nav.php ke sini
include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Printer Assets</title>
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
            <h2 class="h5 mb-0">Printer Assets</h2>
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

            <form method="GET" class="mb-3 border-bottom pb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label for="model" class="form-label">Model</label>
                        <select name="model" id="model" class="form-select form-select-sm">
                            <option value="">All Models</option>
                            <?php foreach(['InkJet','LaserJet','Photocopy','Ribbon'] as $c): ?>
                                <option value="<?= $c ?>" <?= $model==$c?'selected':''?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="serial" class="form-label">Serial Number</label>
                        <input type="text" name="serial" id="serial" class="form-control form-control-sm" value="<?= htmlspecialchars($serial) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="merk" class="form-label">Merk</label>
                        <select name="merk" id="merk" class="form-select form-select-sm">
                            <option value="">All Merks</option>
                            <?php mysqli_data_seek($merk_list, 0); /* Reset pointer */ ?>
                            <?php while($m=mysqli_fetch_assoc($merk_list)): ?>
                                <option value="<?= $m['id'] ?>" <?= $merk==$m['id']?'selected':''?>><?= htmlspecialchars($m['merk_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </div>
                    <div class="col-auto">
                        <a href="printer.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                    <div class="col-auto">
                        <a href="printer_input.php" class="btn btn-success btn-sm">+ Input Asset</a>

                    </div>


                </div>
            </form>

            <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                <table class="table table-sm table-bordered table-striped table-hover">
                    
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Model</th>
                            <th>Merk</th>
                            <th>Type</th>
                            <th>Serial Number</th>
                            <th>Port Connection</th>
                            <th>MAC Address</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Warranty</th>
                            <th>Remarks</th>
                            <th>PO</th>
                            <th>Date Received</th>
                            <th class="text-center">Action</th>
                        </tr>
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
                            <td><?= htmlspecialchars($row['port_connection']) ?></td>
                            <td><?= htmlspecialchars($row['mac_address']) ?></td>
                            <td><?= htmlspecialchars($row['condition']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['warranty_expiration']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['remarks'])) ?></td>
                            <td><?= htmlspecialchars($row['po']) ?></td>
                            <td><?= htmlspecialchars($row['date_received']) ?></td>
                            <td class="text-center text-nowrap">
                                <?php
                                $link_params = http_build_query([
                                    'id' => $row['id'],
                                    'page' => $page,
                                    'model' => $model,
                                    'serial' => $serial,
                                    'merk' => $merk
                                ]);
                                $delete_params = http_build_query([
                                    'id' => $row['id']
                                ]);
                                ?>
                                <a href="printer_input.php?<?= $link_params ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="printer_delete.php?<?= $delete_params ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this asset?')">Delete</a>
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