<?php
include '../db.php';
include '../includes/session.php';

// Notifikasi
$error = '';
$success = '';

// Ambil search query dan edit ID dari URL
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$edit_id_query = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

// Hapus data
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // ... (Logika unlink file jika ada) ...
    
    // UBAH: Prepared statement
    $stmt = $conn->prepare("DELETE FROM hotspot WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    header("Location: hotspot.php?deleted=1&search=" . urlencode($search_query));
    exit;
}

// Tambah data baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $jde = trim($_POST['jde']);
    $employee_name = trim($_POST['employee_name']);
    $search_form = trim($_POST['search']); // Ambil search query dari form

    // UBAH: Prepared statement untuk Cek duplikat
    $stmt_check = $conn->prepare("SELECT id FROM hotspot WHERE username = ? OR jde = ? LIMIT 1");
    $stmt_check->bind_param("ss", $username, $jde);
    $stmt_check->execute();
    $check = $stmt_check->get_result();
    
    if ($check->num_rows > 0) {
        $error = "Username atau JDE sudah terdaftar.";
    } else {
        $stmt = $conn->prepare("INSERT INTO hotspot (username, password, jde, employee_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $jde, $employee_name);
        $stmt->execute();
        header("Location: hotspot.php?added=1&search=" . urlencode($search_form));
        exit;
    }
}
// Update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $jde = trim($_POST['jde']);
    $employee_name = trim($_POST['employee_name']);
    $search_form = trim($_POST['search']); // Ambil search query dari form

    // UBAH: Prepared statement untuk Cek duplikat
    $stmt_check = $conn->prepare("SELECT id FROM hotspot WHERE (username = ? OR jde = ?) AND id != ?");
    $stmt_check->bind_param("ssi", $username, $jde, $id);
    $stmt_check->execute();
    $check = $stmt_check->get_result();

    if ($check->num_rows > 0) {
        $error = "Username atau JDE sudah digunakan oleh data lain.";
    } else {
        $stmt = $conn->prepare("UPDATE hotspot SET username=?, password=?, jde=?, employee_name=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $password, $jde, $employee_name, $id);
        $stmt->execute();
        header("Location: hotspot.php?updated=1&search=" . urlencode($search_form));
        exit;
    }
}

// Notifikasi
if(isset($_GET['added'])) $success = "Akun hotspot berhasil ditambahkan.";
if(isset($_GET['updated'])) $success = "Akun hotspot berhasil diperbarui.";
if(isset($_GET['deleted'])) $success = "Akun hotspot berhasil dihapus.";

include '../nav.php';

// Ambil data edit
$edit_data = null;
if ($edit_id_query > 0) {
    // UBAH: Prepared statement
    $stmt_edit = $conn->prepare("SELECT * FROM hotspot WHERE id = ?");
    $stmt_edit->bind_param("i", $edit_id_query);
    $stmt_edit->execute();
    $edit_data = $stmt_edit->get_result()->fetch_assoc();
}

// Pencarian
// UBAH: Prepared statement untuk Search
$where = '';
$params = [];
$types = '';
if (!empty($search_query)) {
    $q = "%" . $search_query . "%";
    $where = "WHERE jde LIKE ? OR employee_name LIKE ? OR username LIKE ?";
    $params = [$q, $q, $q];
    $types = "sss";
}

$sql = "SELECT * FROM hotspot $where ORDER BY id DESC";
$stmt_data = $conn->prepare($sql);
if ($where) {
    $stmt_data->bind_param($types, ...$params);
}
$stmt_data->execute();
$data = $stmt_data->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Hotspot Account Management</title>
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
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Hotspot Account Management</h2>
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

            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-5">
                    <label for="search" class="visually-hidden">Search</label>
                    <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="Search JDE, Name, or Username" value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </div>
                <div class="col-auto">
                    <a href="hotspot.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>

            <hr>

            <h4 class="h6"><?= $edit_data ? 'Edit' : 'Add New' ?> Account</h4>
            <form method="POST">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                
                <?php if ($edit_data): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Username" value="<?= isset($edit_data['username']) ? htmlspecialchars($edit_data['username']) : '' ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="text" name="password" class="form-control form-control-sm" placeholder="Password" value="<?= isset($edit_data['password']) ? htmlspecialchars($edit_data['password']) : '' ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">JDE</label>
                        <input type="text" name="jde" class="form-control form-control-sm" placeholder="JDE" value="<?= isset($edit_data['jde']) ? htmlspecialchars($edit_data['jde']) : '' ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employee Name</label>
                        <input type="text" name="employee_name" class="form-control form-control-sm" placeholder="Employee Name" value="<?= isset($edit_data['employee_name']) ? htmlspecialchars($edit_data['employee_name']) : '' ?>" required>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <?php if ($edit_data): ?>
                        <a href="hotspot.php?search=<?= htmlspecialchars($search_query) ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" name="<?= $edit_data ? 'update' : 'add' ?>" class="btn btn-primary btn-sm"><?= $edit_data ? 'Update Account' : 'Add Account' ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($search_query)): ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0">Search Result</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>JDE</th>
                                <th>Employee Name</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = $data->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['password']) ?></td>
                                <td><?= htmlspecialchars($row['jde']) ?></td>
                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="?edit=<?= $row['id'] ?>&search=<?= htmlspecialchars($search_query) ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="?delete=<?= $row['id'] ?>&search=<?= htmlspecialchars($search_query) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            Data is hidden for security. Please use the search bar above to find an account.
        </div>
    <?php endif; ?>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_GET['edit'])): ?>
    document.querySelector('.card-body').scrollIntoView({ behavior: 'smooth' });
    <?php endif; ?>

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