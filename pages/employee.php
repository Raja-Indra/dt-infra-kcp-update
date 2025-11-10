<?php
include '../db.php';
include '../includes/session.php';

$edit_data = null;
$success = '';
$error = '';

// --- UBAH: Logika Pagination ---
$limit = 50; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Halaman saat ini, default 1
$offset = ($page - 1) * $limit; // Offset untuk SQL

// Proses Edit (UPDATE)
if (isset($_POST['edit_id'])) {
    $edit_id = intval($_POST['edit_id']);
    $name = trim($_POST['name']);
    $jde = trim($_POST['jde']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $status = trim($_POST['status']);
    if ($name && $jde && $department && $position) {
        $stmt = $conn->prepare("UPDATE employee SET name=?, jde=?, department=?, position=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $jde, $department, $position, $status, $edit_id);
        $stmt->execute();
        header("Location: employee.php?updated=1&page=" . $page); // UBAH: Pertahankan page
        exit;
    }
} 
// Proses Add (INSERT)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Logika Add tidak berubah) ...
    $name = trim($_POST['name']);
    $jde = trim($_POST['jde']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $status = trim($_POST['status']);
    if ($name && $jde && $department && $position) {
        $stmt = $conn->prepare("INSERT INTO employee (name, jde, department, position, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $jde, $department, $position, $status);
        $stmt->execute();
        header("Location: employee.php?added=1");
        exit;
    }
}

// Proses Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM employee WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: employee.php?deleted=1&page=" . $page); // UBAH: Pertahankan page
    exit;
}

// Proses ambil data untuk Edit
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM employee WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

// Tangkap Notifikasi
if (isset($_GET['added'])) $success = "Employee successfully added.";
if (isset($_GET['updated'])) $success = "Employee successfully updated.";
if (isset($_GET['deleted'])) $success = "Employee successfully deleted.";

// Logika Search
// Logika Search
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
$types = '';

if ($search !== '') {
    $esc = "%" . $search . "%";
    
    // UBAH: Tambahkan semua kolom yang ingin dicari
    $where = "WHERE name LIKE ? OR jde LIKE ? OR department LIKE ? OR position LIKE ? OR status LIKE ?";
    
    // UBAH: Pastikan jumlah parameter sesuai (ada 5 'LIKE')
    $params[] = $esc; // for name
    $params[] = $esc; // for jde
    $params[] = $esc; // for department
    $params[] = $esc; // for position
    $params[] = $esc; // for status
    
    // UBAH: Tipe parameter sekarang ada 5 string
    $types = "sssss";
}
// --- UBAH: Query Total Data (untuk Pagination) ---
$sql_total = "SELECT COUNT(*) as total FROM employee $where";
$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);


// --- UBAH: Query Ambil Data (dengan LIMIT dan OFFSET) ---
$sql = "SELECT * FROM employee $where ORDER BY name ASC LIMIT ? OFFSET ?";
$stmt_list = $conn->prepare($sql);

// Tambahkan param pagination ke param search
$params[] = $limit;
$params[] = $offset;
$types .= "ii"; // Tambah 2 integer (LIMIT, OFFSET)

$stmt_list->bind_param($types, ...$params);
$stmt_list->execute();
$data = $stmt_list->get_result();

// Ambil data departments
$dept_result = mysqli_query($conn, "SELECT DISTINCT department_name FROM departments ORDER BY department_name");

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee</title>
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
            justify-content: start;
            overflow-x: auto;  /* ✅ bikin bisa digeser horizontal */
            white-space: nowrap;
            flex-wrap: nowrap;
            padding-bottom: 5px;
            scrollbar-width: thin; /* agar scrollbar kecil */
        }

        .pagination::-webkit-scrollbar {
            height: 6px;
        }

        .pagination::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 3px;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #90ca4b; /* Ganti warna active */
            border-color: #90ca4b;
        }
        .page-link {
            color: #90ca4b; /* Ganti warna link */
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
        .table-wrapper {
            max-height: calc(100vh - 380px); 
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="h5 mb-0">EMPLOYEE</h2>
        </div>
        <div class="card-body">

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Name or JDE" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </div>
                <div class="col-auto">
                    <a href="employee.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>

            <form class="row g-2 mb-4 align-items-end pb-3 border-bottom" method="POST">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>
                
                <div class="col-md-2">
                    <label class="form-label small">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">JDE</label>
                    <input type="text" name="jde" class="form-control form-control-sm" value="<?= htmlspecialchars($edit_data['jde'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Department</label>
                    <select name="department" class="form-select form-select-sm" required>
                        <option value="">Select Department</option>
                        <?php
                        mysqli_data_seek($dept_result, 0); 
                        while($dept = mysqli_fetch_assoc($dept_result)):
                        ?>
                            <option value="<?= htmlspecialchars($dept['department_name']) ?>" <?= isset($edit_data) && $edit_data['department'] === $dept['department_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Position</label>
                    <input type="text" name="position" class="form-control form-control-sm" value="<?= htmlspecialchars($edit_data['position'] ?? '') ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="Staff" <?= (isset($edit_data) && $edit_data['status'] === 'Staff') ? 'selected' : '' ?>>Staff</option>
                        <option value="Non Staff" <?= (isset($edit_data) && $edit_data['status'] === 'Non Staff') ? 'selected' : '' ?>>Non Staff</option>
                        <option value="Labour Supply" <?= (isset($edit_data) && $edit_data['status'] === 'Labour Supply') ? 'selected' : '' ?>>Labour Supply</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success btn-sm"><?= $edit_data ? 'Update' : 'Add' ?></button>
                </div>
                <?php if ($edit_data): ?>
                <div class="col-auto">
                    <a href="employee.php?page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                </div>
                <?php endif; ?>
            </form>

        <div class="table-wrapper">
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead class="table-light sticky-top">                        <tr>
                            <th class="text-center">No</th>
                            <th>Name</th>
                            <th>JDE</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // UBAH: Penomoran dimulai dari offset
                        $no = $offset + 1; 
                        while($row = mysqli_fetch_assoc($data)): 
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['jde']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['position']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td class="text-center">
                                <a href="?edit=<?= $row['id'] ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
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
                    // ### PERBAIKAN DI SINI ###
                    $query_params = $_GET;
                    unset($query_params['page']);
                    unset($query_params['added']);   // Hapus notif
                    unset($query_params['updated']); // Hapus notif
                    unset($query_params['deleted']); // Hapus notif
                    
                    $base_url = http_build_query($query_params);
                    $base_url = $base_url ? $base_url . '&' : '';
                    // ### AKHIR PERBAIKAN ###
                    
                    // Tombol Previous
                    $prev_page = $page - 1;
                    echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">';
                    echo '<a class="page-link" href="?' . $base_url . 'page=' . $prev_page . '">Previous</a>';
                    echo '</li>';

                    // Nomor Halaman 
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                        echo '<a class="page-link" href="?' . $base_url . 'page=' . $i . '">' . $i . '</a>';
                        echo '</li>';
                    }

                    // Tombol Next
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
</body>
</html>