<?php
include '../db.php';
include '../includes/session.php';

// UBAH: Logika Notifikasi
$success = '';
$error = '';
if (isset($_GET['dept_added'])) $success = "Department berhasil ditambahkan.";
if (isset($_GET['dept_deleted'])) $success = "Department (dan section terkait) berhasil dihapus.";
if (isset($_GET['sect_added'])) $success = "Section berhasil ditambahkan.";
if (isset($_GET['sect_deleted'])) $success = "Section berhasil dihapus.";
if (isset($_GET['delete_error'])) $error = "Gagal menghapus: Error database.";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_department'])) {
        $name = trim($_POST['department_name']);
        if ($name !== '') {
            $stmt = $conn->prepare("INSERT INTO departments (department_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            header("Location: departments.php?dept_added=1"); // UBAH: Tambah notifikasi
            exit;
        }
    }
    if (isset($_POST['add_section'])) {
        $section = trim($_POST['section_name']);
        $dept_id = intval($_POST['department_id']);
        if ($section !== '' && $dept_id > 0) {
            $stmt = $conn->prepare("INSERT INTO sections (section_name, department_id) VALUES (?, ?)");
            $stmt->bind_param("si", $section, $dept_id);
            $stmt->execute();
            header("Location: departments.php?sect_added=1"); // UBAH: Tambah notifikasi
            exit;
        }
    }
}

// UBAH: Delete Department (Dibuat lebih aman + hapus child)
if (isset($_GET['delete_department'])) {
    $id = intval($_GET['delete_department']);
    
    // PERBAIKAN: Hapus 'sections' terkait terlebih dahulu untuk menghindari error Foreign Key
    $stmt_sect = $conn->prepare("DELETE FROM sections WHERE department_id = ?");
    $stmt_sect->bind_param("i", $id);
    $stmt_sect->execute();

    // Baru hapus department
    $stmt_dept = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt_dept->bind_param("i", $id);
    
    if ($stmt_dept->execute()) {
        header("Location: departments.php?dept_deleted=1");
    } else {
        header("Location: departments.php?delete_error=1");
    }
    exit;
}

// UBAH: Delete Section (Dibuat lebih aman)
if (isset($_GET['delete_section'])) {
    $id = intval($_GET['delete_section']);
    $stmt = $conn->prepare("DELETE FROM sections WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: departments.php?sect_deleted=1");
    } else {
        header("Location: departments.php?delete_error=1");
    }
    exit;
}

$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY department_name");
$sections = mysqli_query($conn, "SELECT sections.id, sections.section_name, departments.department_name FROM sections JOIN departments ON sections.department_id = departments.id ORDER BY departments.department_name, sections.section_name");

include '../nav.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Departments</title>
    
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
                    <h2 class="h5 mb-0">DEPARTMENT</h2>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-3" method="POST" autocomplete="off">
                        <div class="col">
                            <input type="text" name="department_name" class="form-control form-control-sm" placeholder="Department Name" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_department" class="btn btn-primary btn-sm">Add Department</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Department Name</th>
                                    <th style="width: 100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($departments, 0); while($d = mysqli_fetch_assoc($departments)) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['department_name']) ?></td>
                                    <td class="text-center">
                                        <a href="?delete_department=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this department? This will also delete all sections under it.')">Delete</a>
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
                    <h2 class="h5 mb-0">SECTION</h2>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-3" method="POST" autocomplete="off">
                        <div class="col-md-5">
                            <input type="text" name="section_name" class="form-control form-control-sm" placeholder="Section Name" required>
                        </div>
                        <div class="col-md-4">
                            <select name="department_id" class="form-select form-select-sm" required>
                                <option value="">Select Department</option>
                                <?php mysqli_data_seek($departments, 0); while($d = mysqli_fetch_assoc($departments)) : ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="add_section" class="btn btn-primary btn-sm w-100">Add Section</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Section Name</th>
                                    <th>Department</th>
                                    <th style="width: 100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    // Reset pointer jika variabel $sections sudah dipakai di tempat lain
                                    mysqli_data_seek($sections, 0); 
                                    while($s = mysqli_fetch_assoc($sections)) : 
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['section_name']) ?></td>
                                    <td><?= htmlspecialchars($s['department_name']) ?></td>
                                    <td class="text-center">
                                        <a href="?delete_section=<?= $s['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this section?')">Delete</a>
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