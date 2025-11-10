<?php
include '../db.php';
include '../includes/session.php';

// Ambil ID
if (!isset($_GET['id'])) {
    header("Location: ip_address.php");
    exit;
}
$id = intval($_GET['id']);

// UBAH: Ambil parameter filter/halaman untuk redirect kembali
$page = $_GET['page'] ?? 1;
$device_filter = $_GET['device'] ?? '';
$labeling_filter = $_GET['labeling'] ?? '';
$status_filter = $_GET['status'] ?? '';

// UBAH: Buat query string untuk link "Cancel" dan "Update"
$return_query = http_build_query([
    'page' => $page,
    'device' => $device_filter,
    'labeling' => $labeling_filter,
    'status' => $status_filter
]);


// UBAH: Gunakan prepared statement untuk mengambil data
$stmt_get = $conn->prepare("SELECT * FROM ip_address WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$data_result = $stmt_get->get_result();

if($data_result->num_rows === 0) {
    header("Location: ip_address.php");
    exit;
}
$data = $data_result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device = $_POST['device'];
    $labeling = $_POST['labeling'];
    $mac_address = $_POST['mac_address'];
    $ip_address = $_POST['ip_address'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE ip_address SET device=?, labeling=?, mac_address=?, ip_address=?, status=? WHERE id=?");
    $stmt->bind_param("sssssi", $device, $labeling, $mac_address, $ip_address, $status, $id);
    $stmt->execute();
    
    // UBAH: Redirect kembali ke list DENGAN filter
    header("Location: ip_address.php?updated=1&" . $return_query);
    exit;
}

include '../nav.php';

// Data untuk dropdown
$devices = [
    "Access Point", "Attendance Machine", "CCTV",
    "Printer", "Router", "Server", "Switch", "Wireless Backbone"
];
$statuses = ['Plan', 'Spare', 'Used'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit IP Address</title>
    <!-- UBAH: CDN Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- UBAH: Hapus semua <style> kustom dan ganti dengan ini -->
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

<!-- UBAH: Layout Bootstrap -->
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- UBAH: Ganti .panel menjadi .card -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Edit IP Address</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Device</label>
                                <!-- UBAH: Class form-select-sm -->
                                <select name="device" class="form-select form-select-sm" required>
                                    <?php
                                    foreach ($devices as $opt) {
                                        $selected = ($data['device'] === $opt) ? 'selected' : '';
                                        echo "<option value='$opt' $selected>$opt</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Labeling</label>
                                <!-- UBAH: Class form-control-sm -->
                                <input type="text" name="labeling" class="form-control form-control-sm" value="<?= htmlspecialchars($data['labeling']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">MAC Address</label>
                                <input type="text" name="mac_address" class="form-control form-control-sm" value="<?= htmlspecialchars($data['mac_address']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IP Address</label>
                                <input type="text" name="ip_address" class="form-control form-control-sm" value="<?= htmlspecialchars($data['ip_address']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <?php
                                    foreach ($statuses as $status) {
                                        $selected = ($data['status'] === $status) ? 'selected' : '';
                                        echo "<option value='$status' $selected>$status</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- UBAH: Button styling -->
                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <!-- UBAH: Link Cancel kembali ke list dengan filter -->
                            <a href="ip_address.php?<?= $return_query ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- UBAH: CDN Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>