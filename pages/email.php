<?php
include '../db.php';
include '../includes/session.php';

$error = '';
$success = '';

// --- Logika Pagination ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Delete jika diminta
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Prepared statement untuk SELECT
    $stmt_file = $conn->prepare("SELECT request_form FROM email WHERE id=?");
    $stmt_file->bind_param("i", $id);
    $stmt_file->execute();
    $row = $stmt_file->get_result()->fetch_assoc();
    
    if ($row && !empty($row['request_form']) && file_exists("../uploads/" . $row['request_form'])) {
        unlink("../uploads/" . $row['request_form']);
    }

    // Prepared statement untuk DELETE
    $stmt_del = $conn->prepare("DELETE FROM email WHERE id=?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    
    header("Location: email.php?deleted=1&page=$page"); // Pertahankan page
    exit;
}

// HAPUS: Blok 'Insert baru' (dipindah ke email_add.php)

// Notifikasi
if(isset($_GET['added'])) $success = "Data email berhasil ditambahkan.";
if(isset($_GET['deleted'])) $success = "Data email berhasil dihapus.";
if(isset($_GET['updated'])) $success = "Data email berhasil diperbarui.";

// --- Logika Search ---
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
$types = '';

if ($search !== '') {
    $esc = "%" . $search . "%";
    $where = "WHERE (jde LIKE ? OR employee_name LIKE ? OR email_address LIKE ?)";
    $params = [$esc, $esc, $esc];
    $types = "sss";
}

// --- Query Total Data (untuk Pagination) ---
$sql_total = "SELECT COUNT(*) as total FROM email $where";
$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// --- Query Ambil Data (dengan LIMIT dan OFFSET) ---
$sql = "SELECT * FROM email $where ORDER BY id DESC LIMIT ? OFFSET ?";
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
    <title>Email Management</title>
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
                    <h2 class="h5 mb-0">List Email</h2>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="GET" class="row g-2 mb-3 border-bottom pb-3">
                        <div class="col-md-4">
                            <label for="search_input" class="form-label">Search</label>
                            <input type="text" name="search" id="search_input" class="form-control form-control-sm" placeholder="Search JDE, Name, or Email..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-auto align-self-end">
                            <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        </div>
                        <div class="col-auto align-self-end">
                            <a href="email.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                        <div class="col-auto align-self-end">
                            <a href="email_add.php" class="btn btn-success btn-sm">Add Email Entry</a>
                        </div>


                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>No</th>
                                    <th>JDE</th>
                                    <th>Employee Name</th>
                                    <th>User Login</th>
                                    <th>Email</th>
                                    <th>Departments</th>
                                    <th>Position</th>
                                    <th>Mail Type</th>
                                    <th>Status</th>
                                    <th class="text-center">Form</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; while($row = $data->fetch_assoc()) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['jde']) ?></td>
                                    <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                    <td><?= htmlspecialchars($row['user_login']) ?></td>
                                    <td><?= htmlspecialchars($row['email_address']) ?></td>
                                    <td><?= htmlspecialchars($row['departments']) ?></td>
                                    <td><?= htmlspecialchars($row['position']) ?></td>
                                    <td><?= htmlspecialchars($row['mail_type']) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                    <td class="text-center">
                                        <?php if ($row['request_form']): ?>
                                            <a href="../uploads/<?= $row['request_form'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm">View</a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="email_edit.php?id=<?= $row['id'] ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="?delete=<?= $row['id'] ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
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