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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// === Hapus file individual ===
if (isset($_GET['delete_file'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $col = $_GET['delete_file'];
    
    if (in_array($col, ['invoice','tax','bai'])) {
        // UBAH: Prepared statement
        $stmt_get = $conn->prepare("SELECT `$col` FROM payment_contract WHERE id=?");
        $stmt_get->bind_param("i", $id);
        $stmt_get->execute();
        $row = $stmt_get->get_result()->fetch_assoc();
        
        if (!empty($row[$col]) && file_exists(__DIR__."/../uploads/".$row[$col])) {
            unlink(__DIR__."/../uploads/".$row[$col]);
        }
        
        // UBAH: Prepared statement
        $stmt_update = $conn->prepare("UPDATE payment_contract SET `$col`=NULL WHERE id=?");
        $stmt_update->bind_param("i", $id);
        $stmt_update->execute();
    }
    
    // UBAH: Pertahankan search dan page
    $query_string = http_build_query(['file_deleted' => 1, 'page' => $page, 'search' => $search]);
    header("Location: payment_contract.php?" . $query_string);
    exit;
}

// === Hapus record lengkap ===
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // UBAH: Prepared statement
    $stmt_get = $conn->prepare("SELECT invoice,tax,bai FROM payment_contract WHERE id=?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $row = $stmt_get->get_result()->fetch_assoc();
    
    foreach (['invoice','tax','bai'] as $col) {
        if (!empty($row[$col]) && file_exists(__DIR__."/../uploads/".$row[$col])) {
            unlink(__DIR__."/../uploads/".$row[$col]);
        }
    }
    
    // UBAH: Prepared statement
    $stmt_del = $conn->prepare("DELETE FROM payment_contract WHERE id=?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    
    // UBAH: Pertahankan search dan page
    $query_string = http_build_query(['deleted' => 1, 'page' => $page, 'search' => $search]);
    header("Location: payment_contract.php?" . $query_string);
    exit;
}

// Tangkap notifikasi
if(isset($_GET['added'])) $success = "Payment contract berhasil ditambahkan.";
if(isset($_GET['updated'])) $success = "Payment contract berhasil diperbarui.";
if(isset($_GET['deleted'])) $success = "Payment contract berhasil dihapus.";
if(isset($_GET['file_deleted'])) $success = "File berhasil dihapus.";

// === UBAH: Search dengan Prepared Statement ===
$where = '';
$params = [];
$types = '';
if ($search !== '') {
    $where = "WHERE isp LIKE ? OR no_valuation LIKE ? OR no_pr LIKE ? OR no_po LIKE ?";
    $esc = "%" . $search . "%";
    $params = [$esc, $esc, $esc, $esc];
    $types = "ssss";
}

// --- UBAH: Query Total Data (untuk Pagination) ---
$sql_total = "SELECT COUNT(*) as total FROM payment_contract $where";
$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// --- UBAH: Query Ambil Data (dengan LIMIT dan OFFSET) ---
$sql = "SELECT * FROM payment_contract $where ORDER BY id DESC LIMIT ? OFFSET ?";
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
    <title>Payment Contract List</title>
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
        .action-links a {
            margin: 0 3px;
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
            <h2 class="h5 mb-0">Payment Contract</h2>
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
                <div class="col-md-4">
                    <label for="search" class="form-label">Search (ISP, Valuation, PR, PO)</label>
                    <input type="text" name="search" id="search" class="form-control form-control-sm" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </div>
                <div class="col-auto">
                    <a href="payment_contract.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
                <div class="col-auto">
                    <a href="payment_contract_add.php" class="btn btn-success btn-sm">+ Add Payment Contract</a>
                </div>


            </form>

            <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                <table class="table table-sm table-striped table-bordered table-hover">
                    
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="text-nowrap text-center">No</th>
                            <th class="text-nowrap">ISP</th>
                            <th class="text-nowrap">No Valuation</th>
                            <th class="text-nowrap">No PR</th>
                            <th class="text-nowrap">No PO</th>
                            <th class="text-nowrap">Periode</th>
                            <th class="text-nowrap">Process Payment</th>
                            <th class="text-nowrap">Reference IR</th>
                            <th class="text-nowrap">Date Receipt</th>
                            <th class="text-nowrap">Status Receipt</th>
                            <th class="text-nowrap text-center">Invoice</th>
                            <th class="text-nowrap text-center">Tax</th>
                            <th class="text-nowrap text-center">BAI</th>
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
                            <td><?= htmlspecialchars($r['isp']) ?></td>
                            <td><?= htmlspecialchars($r['no_valuation']) ?></td>
                            <td><?= htmlspecialchars($r['no_pr']) ?></td>
                            <td><?= htmlspecialchars($r['no_po']) ?></td>
                            <td><?= htmlspecialchars($r['periode']) ?></td>
                            <td><?= htmlspecialchars($r['process_payment']) ?></td>
                            <td><?= htmlspecialchars($r['reference_ir']) ?></td>
                            <td><?= htmlspecialchars($r['date_receipt']) ?></td>
                            <td><?= htmlspecialchars($r['status_receipt']) ?></td>
                            
                            <td class="text-center action-links">
                                <?php if($r['invoice']): ?>
                                    <a href="/dt-infra-kcp/uploads/<?= $r['invoice'] ?>" target="_blank">View</a> |
                                    <a href="?delete_file=invoice&id=<?= $r['id'] ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="text-danger" onclick="return confirm('Delete invoice file?')">Del</a>
                                <?php endif; ?>
                            </td>
                            <td class="text-center action-links">
                                <?php if($r['tax']): ?>
                                    <a href="/dt-infra-kcp/uploads/<?= $r['tax'] ?>" target="_blank">View</a> |
                                    <a href="?delete_file=tax&id=<?= $r['id'] ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="text-danger" onclick="return confirm('Delete tax file?')">Del</a>
                                <?php endif; ?>
                            </td>
                            <td class="text-center action-links">
                                <?php if($r['bai']): ?>
                                    <a href="/dt-infra-kcp/uploads/<?= $r['bai'] ?>" target="_blank">View</a> |
                                    <a href="?delete_file=bai&id=<?= $r['id'] ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="text-danger" onclick="return confirm('Delete BAI file?')">Del</a>
                                <?php endif; ?>
                            </td>
                            
                            <td class="text-center text-nowrap">
                                <?php
                                $link_params = http_build_query([
                                    'id' => $r['id'],
                                    'page' => $page,
                                    'search' => $search
                                ]);
                                $delete_params = http_build_query([
                                    'delete' => $r['id'],
                                    'page' => $page,
                                    'search' => $search
                                ]);
                                ?>
                                <a href="payment_contract_edit.php?<?= $link_params ?>" class="btn btn-primary btn-sm">Edit</a>
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
                    unset($query_params['page'], $query_params['added'], $query_params['updated'], $query_params['deleted'], $query_params['file_deleted']); 
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
    if (window.location.search.includes("updated=1") || window.location.search.includes("added=1") || window.location.search.includes("deleted=1") || window.location.search.includes("file_deleted=1")) {
        const url = new URL(window.location);
        url.searchParams.delete('updated');
        url.searchParams.delete('added');
        url.searchParams.delete('deleted');
        url.searchParams.delete('file_deleted');
        window.history.replaceState({}, document.title, url);
    }
});
</script>

</body>
</html>