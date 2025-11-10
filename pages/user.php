<?php
include '../db.php';
include '../includes/session.php';

$error = '';
$success = '';
$edit_mode = false;
$edit_user = [];
$menus = ['daily_job','detail_job','departments','employee','assets','warehouse','inventory','purchasing','data_account','user'];

// --- UBAH: Logika Pagination ---
$limit = 10; // Jumlah user per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Offset untuk SQL

// Menangani form submit (add atau update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $username = $_POST['username'];
    $jde = $_POST['jde'];
    $password = $_POST['password'];

    $access = [];
    foreach ($menus as $menu) {
        $access[$menu] = isset($_POST[$menu]) ? 'Yes' : 'No';
    }

    if ($id) {
        // --- UPDATE ---
        $query = "UPDATE users SET username=?, jde=?";
        $types = "ss";
        $params = [$username, $jde];

        if (!empty($password)) {
            $query .= ", password=?";
            $types .= "s";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        foreach ($menus as $menu) {
            $query .= ", $menu=?";
            $types .= "s";
            $params[] = $access[$menu];
        }

        $query .= " WHERE id=?";
        $types .= "i";
        $params[] = $id;

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        header("Location: user.php?updated=1&page=" . $page); // UBAH: Notifikasi
        exit;
    } else {
        // --- INSERT ---
        $query = "INSERT INTO users (username, jde, password, " . implode(',', $menus) . ")
                  VALUES (?, ?, ?, " . rtrim(str_repeat('?,', count($menus)), ',') . ")";
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $types = "sss" . str_repeat('s', count($menus));
        $params = array_merge([$username, $jde, $hash], array_values($access));
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        header("Location: user.php?added=1"); // UBAH: Notifikasi
        exit;
    }
}

// Menangani delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // UBAH: Prepared statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: user.php?deleted=1&page=" . $page); // UBAH: Notifikasi
    exit;
}

// Menangani edit
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = intval($_GET['edit']);
    // UBAH: Prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
}

// UBAH: Tangkap notifikasi
if (isset($_GET['added'])) $success = "User added successfully.";
if (isset($_GET['updated'])) $success = "User updated successfully.";
if (isset($_GET['deleted'])) $success = "User deleted successfully.";

// Ambil total user untuk pagination
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$total_users = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_users / $limit);

// Ambil user untuk halaman ini
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC LIMIT $limit OFFSET $offset");
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
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
            text-align: center; /* Default align center untuk tabel ini */
            vertical-align: middle;
        }
        .table-sm th:first-child, /* Luruskan kolom nama & JDE ke kiri */
        .table-sm td:first-child,
        .table-sm th:nth-child(2),
        .table-sm td:nth-child(2) {
            text-align: left;
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
        .check-icon {
            color: green;
            font-weight: bold;
            font-size: 1.1em;
        }
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 10px;
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

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0"><?= $edit_mode ? 'Edit' : 'Add' ?> User</h2>
                </div>
                <div class="card-body">
                    <form method="post" id="userForm">
                        <input type="hidden" name="id" value="<?= $edit_mode ? $edit_user['id'] : '' ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control form-control-sm" required value="<?= $edit_mode ? htmlspecialchars($edit_user['username']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">JDE</label>
                            <input type="text" name="jde" class="form-control form-control-sm" required value="<?= $edit_mode ? htmlspecialchars($edit_user['jde']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control form-control-sm" <?= $edit_mode ? '' : 'required' ?>>
                            <small class="form-text text-muted">(Leave blank if not changing)</small>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">Menu Access</label>
                            <div class="form-check bg-light border rounded p-2 mb-2">
                                <input class="form-check-input" type="checkbox" id="select_all_checkbox">
                                <label class="form-check-label fw-bold" for="select_all_checkbox">
                                    SELECT ALL / DESELECT ALL
                                </label>
                            </div>
                            
                            <div class="checkbox-grid border rounded p-3">
                            <?php
                            foreach ($menus as $menu) {
                                $checked = $edit_mode && $edit_user[$menu] === 'Yes' ? 'checked' : '';
                                $label = strtoupper(str_replace('_',' ', $menu));
                                echo '<div class="form-check">';
                                echo "<input class='form-check-input' type='checkbox' name='$menu' value='Yes' id='cb_$menu' $checked>";
                                echo "<label class='form-check-label' for='cb_$menu'>$label</label>";
                                echo '</div>';
                            }
                            ?>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <?php if ($edit_mode): ?>
                                <a href="user.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-success btn-sm"><?= $edit_mode ? 'Update User' : 'Save User' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">User List</h2>
                </div>
                <div class="card-body">
                    
                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Username</th>
                                    <th>JDE</th>
                                    <?php foreach ($menus as $menu): ?>
                                        <th><?= htmlspecialchars(strtoupper(str_replace('_',' ', $menu))) ?></th>
                                    <?php endforeach; ?>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['jde']) ?></td>
                                    <?php foreach ($menus as $menu): ?>
                                        <td class="<?= $user[$menu] === 'Yes' ? 'check-icon' : '' ?>">
                                            <?= $user[$menu] === 'Yes' ? '✔' : '–' ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-nowrap">
                                        <a href="?edit=<?= $user['id'] ?>&page=<?= $page ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="?delete=<?= $user['id'] ?>&page=<?= $page ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
    </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Hapus parameter notifikasi dari URL
    if (window.location.search.includes("updated=1") || window.location.search.includes("added=1") || window.location.search.includes("deleted=1")) {
        const url = new URL(window.location);
        url.searchParams.delete('updated');
        url.searchParams.delete('added');
        url.searchParams.delete('deleted');
        // Ganti URL tanpa reload, kecuali jika sedang mode edit
        if (!url.searchParams.has('edit')) {
             window.history.replaceState({}, document.title, url);
        }
    }

    // --- FUNGSI "SELECT ALL" ---
    const selectAllBox = document.getElementById('select_all_checkbox');
    const menuCheckboxes = document.querySelectorAll('.checkbox-grid .form-check-input');

    function checkAllStatus() {
        let allChecked = true;
        if (menuCheckboxes.length === 0) {
            allChecked = false; 
        } else {
            menuCheckboxes.forEach(function(box) {
                if (box.checked === false) {
                    allChecked = false;
                }
            });
        }
        if (selectAllBox) {
           selectAllBox.checked = allChecked;
        }
    }

    if (selectAllBox) {
        selectAllBox.addEventListener('change', function() {
            menuCheckboxes.forEach(function(box) {
                box.checked = selectAllBox.checked;
            });
        });
    }

    menuCheckboxes.forEach(function(box) {
        box.addEventListener('change', function() {
            checkAllStatus(); 
        });
    });

    // Cek status saat halaman dimuat (untuk mode edit)
    checkAllStatus();
});
</script>
</body>
</html>