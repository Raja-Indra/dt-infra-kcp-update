<?php
include '../db.php';
include '../includes/session.php';

$categories = ['Radio','Computer','Network','Attendance','CCTV','Server','Printer','Tools'];

// UBAH: Logika Notifikasi
$success = '';
$error = '';
if (isset($_GET['merk_added'])) $success = "Merk berhasil ditambahkan.";
if (isset($_GET['merk_deleted'])) $success = "Merk (dan type terkait) berhasil dihapus.";
if (isset($_GET['type_added'])) $success = "Type berhasil ditambahkan.";
if (isset($_GET['type_deleted'])) $success = "Type berhasil dihapus.";
if (isset($_GET['delete_error'])) $error = "Gagal menghapus: Error database.";

// Handle Add Merk
if (isset($_POST['add_merk'])) {
    $merk_name = trim($_POST['merk_name']);
    $category = trim($_POST['category']);
    if (!empty($merk_name) && !empty($category)) {
        // UBAH: Prepared statement
        $stmt = $conn->prepare("INSERT INTO merk_asset (merk_name, category) VALUES (?, ?)");
        $stmt->bind_param("ss", $merk_name, $category);
        $stmt->execute();
        header("Location: merk_type_asset.php?merk_added=1");
        exit;
    }
}

// Handle Delete Merk
if (isset($_GET['delete_merk'])) {
    $id = intval($_GET['delete_merk']);
    
    // PERBAIKAN: Hapus 'type' (child) terlebih dahulu untuk menghindari error Foreign Key
    $stmt_type = $conn->prepare("DELETE FROM type_asset WHERE merk_id = ?");
    $stmt_type->bind_param("i", $id);
    $stmt_type->execute();

    // UBAH: Prepared statement untuk parent
    $stmt_merk = $conn->prepare("DELETE FROM merk_asset WHERE id = ?");
    $stmt_merk->bind_param("i", $id);
    if ($stmt_merk->execute()) {
        header("Location: merk_type_asset.php?merk_deleted=1");
    } else {
        header("Location: merk_type_asset.php?delete_error=1");
    }
    exit;
}

// Handle Add Type
if (isset($_POST['add_type'])) {
    $type_name = trim($_POST['type_name']);
    $merk_id = intval($_POST['merk_id']);
    if (!empty($type_name) && $merk_id > 0) {
        // UBAH: Prepared statement
        $stmt = $conn->prepare("INSERT INTO type_asset (type_name, merk_id) VALUES (?, ?)");
        $stmt->bind_param("si", $type_name, $merk_id);
        $stmt->execute();
        header("Location: merk_type_asset.php?type_added=1");
        exit;
    }
}

// Handle Delete Type
if (isset($_GET['delete_type'])) {
    $id = intval($_GET['delete_type']);
    // UBAH: Prepared statement
    $stmt = $conn->prepare("DELETE FROM type_asset WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: merk_type_asset.php?type_deleted=1");
    } else {
        header("Location: merk_type_asset.php?delete_error=1");
    }
    exit;
}

// Fetch data
$merk_data = mysqli_query($conn, "SELECT * FROM merk_asset ORDER BY category, merk_name");
$type_data = mysqli_query($conn, "
    SELECT t.*, m.merk_name, m.category
    FROM type_asset t
    LEFT JOIN merk_asset m ON t.merk_id = m.id
    ORDER BY m.category, m.merk_name, t.type_name
");

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Merk & Type Asset</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            /* Latar belakang gradient dari style lama Anda */
            background: linear-gradient(to bottom, #FFE0C0, #FF6600);
            min-height: 100vh;
            font-size: 13px; /* Ukuran font dasar */
        }
        /* Style untuk membuat tabel sedikit lebih kecil */
        .table-sm th, .table-sm td {
            padding: 0.4rem;
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
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">MERK</h2>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-3" method="POST" autocomplete="off">
                        <div class="col-md-4">
                            <input type="text" name="merk_name" class="form-control form-control-sm" placeholder="Merk Name" required>
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select form-select-sm" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c ?>"><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="add_merk" class="btn btn-primary btn-sm w-100">Add Merk</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Merk</th>
                                    <th>Category</th>
                                    <th style="width: 100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($merk_data, 0); while($row = mysqli_fetch_assoc($merk_data)) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['merk_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td class="text-center">
                                        <a href="?delete_merk=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this Merk? This will also delete all types associated with it.')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">TYPE</h2>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-3" method="POST" autocomplete="off">
                        <div class="col-md-4">
                            <input type="text" name="type_name" class="form-control form-control-sm" placeholder="Type Name" required>
                        </div>
                        <div class="col-md-4">
                            <select name="merk_id" class="form-select form-select-sm" required>
                                <option value="">Select Merk</option>
                                <?php
                                // Query ulang untuk form, karena $merk_data mungkin sudah terpakai
                                $merk_options = mysqli_query($conn, "SELECT * FROM merk_asset ORDER BY category, merk_name");
                                while($m = mysqli_fetch_assoc($merk_options)) {
                                    echo "<option value='{$m['id']}'>".htmlspecialchars($m['category'].' - '.$m['merk_name'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="add_type" class="btn btn-primary btn-sm w-100">Add Type</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Type</th>
                                    <th>Merk</th>
                                    <th>Category</th>
                                    <th style="width: 100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    // Reset pointer jika variabel $type_data sudah dipakai di tempat lain
                                    mysqli_data_seek($type_data, 0); 
                                    while($row = mysqli_fetch_assoc($type_data)) : 
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['type_name']) ?></td>
                                    <td><?= htmlspecialchars($row['merk_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td class="text-center">
                                        <a href="?delete_type=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this Type?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>