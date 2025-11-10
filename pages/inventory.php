<?php
include '../db.php';
include '../includes/session.php';

// UBAH: Logika Notifikasi
$success = '';
$error = '';
if (isset($_GET['part_added'])) $success = "Part (Device) berhasil ditambahkan.";
if (isset($_GET['part_deleted'])) $success = "Part (dan sub-part terkait) berhasil dihapus.";
if (isset($_GET['sub_added'])) $success = "Sub-part berhasil ditambahkan.";
if (isset($_GET['sub_deleted'])) $success = "Sub-part berhasil dihapus.";
if (isset($_GET['delete_error'])) $error = "Gagal menghapus: Error database.";

// Proses tambah/hapus
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Add Part (Device)
    if (isset($_POST['device']) && !empty(trim($_POST['device']))) {
        $name = trim($_POST['device']);
        // UBAH: Prepared statement
        $stmt = $conn->prepare("INSERT INTO parts (device) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        header("Location: inventory.php?part_added=1");
        exit;
    }
    // Handle Add Sub-Part
    if (isset($_POST['part_name']) && isset($_POST['part_id'])) {
        $sub = trim($_POST['part_name']);
        $pid = intval($_POST['part_id']);
        // UBAH: Prepared statement
        $stmt = $conn->prepare("INSERT INTO sub_parts (part_id, part_name) VALUES (?, ?)");
        $stmt->bind_param("is", $pid, $sub); // Tipe data (i, s)
        $stmt->execute();
        header("Location: inventory.php?sub_added=1");
        exit;
    }
}

// Handle Delete Part (Device)
if (isset($_GET['delete_part'])) {
    $id = intval($_GET['delete_part']);
    
    // PERBAIKAN: Hapus 'sub_parts' (child) terlebih dahulu
    $stmt_sub = $conn->prepare("DELETE FROM sub_parts WHERE part_id = ?");
    $stmt_sub->bind_param("i", $id);
    $stmt_sub->execute();

    // UBAH: Prepared statement untuk parent
    $stmt_part = $conn->prepare("DELETE FROM parts WHERE id = ?");
    $stmt_part->bind_param("i", $id);
    if ($stmt_part->execute()) {
        header("Location: inventory.php?part_deleted=1");
    } else {
        header("Location: inventory.php?delete_error=1");
    }
    exit;
}

// Handle Delete Sub-Part
if (isset($_GET['delete_sub'])) {
    $id = intval($_GET['delete_sub']);
    // UBAH: Prepared statement
    $stmt = $conn->prepare("DELETE FROM sub_parts WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: inventory.php?sub_deleted=1");
    } else {
        header("Location: inventory.php?delete_error=1");
    }
    exit;
}

// Data
$parts = mysqli_query($conn, "SELECT * FROM parts ORDER BY device");
$sub_parts = mysqli_query($conn, "
    SELECT sub_parts.id, sub_parts.part_name, parts.device 
    FROM sub_parts 
    JOIN parts ON sub_parts.part_id = parts.id 
    ORDER BY parts.device, sub_parts.part_name
");

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory - Parts</title>
    
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

        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">PART (DEVICE)</h2>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-3" method="POST" autocomplete="off">
                        <div class="col">
                            <input type="text" name="device" class="form-control form-control-sm" placeholder="Device Name" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_part" class="btn btn-primary btn-sm">Add Part</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Device</th>
                                    <th style="width: 100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($parts, 0); while ($row = mysqli_fetch_assoc($parts)) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['device']) ?></td>
                                    <td class="text-center">
                                        <a href="?delete_part=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this part? This will also delete all associated sub-parts.')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">SUB PART (PART NAME)</h2>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-3" method="POST" autocomplete="off">
                        <div class="col-md-5">
                            <select name="part_id" class="form-select form-select-sm" required>
                                <option value="">Select Device</option>
                                <?php
                                // Query ulang untuk form, karena $merk_data mungkin sudah terpakai
                                $partOptions = mysqli_query($conn, "SELECT * FROM parts ORDER BY device");
                                while ($p = mysqli_fetch_assoc($partOptions)) :
                                ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['device']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="part_name" class="form-control form-control-sm" placeholder="Part Name" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="add_sub_part" class="btn btn-primary btn-sm w-100">Add Sub Part</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Part Name</th>
                                    <th>Device</th>
                                    <th style="width: 100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    // Reset pointer jika variabel $sub_parts sudah dipakai di tempat lain
                                    mysqli_data_seek($sub_parts, 0);
                                    while ($row = mysqli_fetch_assoc($sub_parts)) : 
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['part_name']) ?></td>
                                    <td><?= htmlspecialchars($row['device']) ?></td>
                                    <td class="text-center">
                                        <a href="?delete_sub=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete sub part?')">Delete</a>
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