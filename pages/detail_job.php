<?php
include '../db.php';
include '../includes/session.php';

// UBAH: Logika untuk Notifikasi (Alerts)
$success = '';
$error = '';
if (isset($_GET['issue_added'])) $success = "Issue baru berhasil ditambahkan.";
if (isset($_GET['issue_deleted'])) $success = "Issue berhasil dihapus.";
if (isset($_GET['sub_added'])) $success = "Sub issue baru berhasil ditambahkan.";
if (isset($_GET['sub_deleted'])) $success = "Sub issue berhasil dihapus.";

// Ambil data
$issues = mysqli_query($conn, "SELECT * FROM issues ORDER BY issue_name");
$sub_issues = mysqli_query($conn, "SELECT sub_issues.id, sub_issues.sub_issue_name, issues.issue_name FROM sub_issues JOIN issues ON sub_issues.issue_id = issues.id ORDER BY issues.issue_name, sub_issues.sub_issue_name");

// Sertakan nav.php di sini
include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Job</title>
    
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
                    <h2 class="h5 mb-0">ISSUE</h2>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-3" action="issue_add.php" method="POST" autocomplete="off">
                        <div class="col">
                            <input type="text" name="issue_name" class="form-control form-control-sm" placeholder="Issue Name" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Add Issue</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Issue Name</th>
                                    <th style="width: 100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($issues, 0); while ($issue = mysqli_fetch_assoc($issues)) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($issue['issue_name']) ?></td>
                                        <td class="text-center">
                                            <a href="issue_delete.php?id=<?= $issue['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this issue?')">Delete</a>
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
            <h2 class="h5 mb-0">SUB ISSUE</h2>
        </div>
        <div class="card-body">
            <form class="row g-2 mb-3" action="sub_issue_add.php" method="POST" autocomplete="off">
                <div class="col-md-5">
                    <input type="text" name="sub_issue_name" class="form-control form-control-sm" placeholder="Sub Issue" required>
                </div>
                <div class="col-md-4">
                    <select name="issue_id" class="form-select form-select-sm" required>
                        <option value="">Select Issue</option>
                        <?php mysqli_data_seek($issues, 0); while ($i = mysqli_fetch_assoc($issues)) : ?>
                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['issue_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Add Sub Issue</button>
                </div>
            </form>

            <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                <table class="table table-bordered table-striped table-hover table-sm">
                    
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Sub Issue</th>
                            <th>Issue Name</th>
                            <th style="width: 100px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                              // Pastikan pointer di reset jika $sub_issues sudah dipakai
                              mysqli_data_seek($sub_issues, 0); 
                              while ($sub = mysqli_fetch_assoc($sub_issues)) : 
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($sub['sub_issue_name']) ?></td>
                                <td><?= htmlspecialchars($sub['issue_name']) ?></td>
                                <td class="text-center">
                                    <a href="sub_issue_delete.php?id=<?= $sub['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this sub issue?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

    </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>