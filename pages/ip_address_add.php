<?php
include '../db.php';
include '../includes/session.php';

$error = '';
$success = '';

// Handle Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device = $_POST['device'];
    $labeling = $_POST['labeling'];
    $mac_address = $_POST['mac_address'];
    $ip_address = $_POST['ip_address'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO ip_address (device, labeling, mac_address, ip_address, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $device, $labeling, $mac_address, $ip_address, $status);
    $stmt->execute();
    header("Location: ip_address.php?added=1");
    exit;
}

include '../nav.php';

$devices = [
    "Access Point", "Attendance Machine", "CCTV",
    "Printer", "Router", "Server", "Switch", "Wireless Backbone"
];
$statuses = ['Plan', 'Spare', 'Used'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add IP Address</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #FFE0C0, #FF6600);
            min-height: 100vh;
            font-size: 13px;
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

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Add IP Address</h2>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Device</label>
                                <select name="device" class="form-select form-select-sm" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($devices as $d): ?>
                                        <option value="<?= $d ?>"><?= $d ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Labeling</label>
                                <input type="text" name="labeling" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">MAC Address</label>
                                <input type="text" name="mac_address" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IP Address</label>
                                <input type="text" name="ip_address" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?= $s ?>"><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="ip_address.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>