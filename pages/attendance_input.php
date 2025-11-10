<?php
include '../db.php';
include '../includes/session.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = [];

// UBAH: Ambil parameter filter/halaman untuk redirect kembali
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$model_filter = isset($_GET['model']) ? $_GET['model'] : '';
$serial_filter = isset($_GET['serial']) ? $_GET['serial'] : '';
$merk_filter = isset($_GET['merk']) ? $_GET['merk'] : '';


if($id){
    // Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    // Tambah validasi jika data tidak ditemukan
    if (!$data) {
        header('Location: attendance.php'); 
        exit;
    }
}

// Merk list kategori Attendance
$merk_list = mysqli_query($conn, "SELECT * FROM merk_asset WHERE category='Attendance' ORDER BY merk_name");

// Type options kalau edit dan sudah ada merk_id
$type_options = [];
// UBAH: Kompatibilitas PHP 5.x
$merk_id_exists = isset($data['merk_id']) && !empty($data['merk_id']);
if($merk_id_exists){
    // Gunakan prepared statement
    $type_stmt = $conn->prepare("SELECT * FROM type_asset WHERE merk_id = ? ORDER BY type_name");
    $type_stmt->bind_param('i', $data['merk_id']);
    $type_stmt->execute();
    $type_q = $type_stmt->get_result();
    while($t = $type_q->fetch_assoc()){
        $type_options[] = $t;
    }
}

// Pindah nav.php ke sini
include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $id ? 'Edit' : 'Input' ?> Attendance Asset</title>
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
        .form-label {
            font-weight: 500;
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
                    <h2 class="h5 mb-0"><?= $id ? 'Edit' : 'Input' ?> Attendance Asset</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="attendance_process.php">
                        <?php if($id): ?>
                            <input type="hidden" name="id" value="<?= $id ?>">
                        <?php endif; ?>

                        <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                        <input type="hidden" name="model" value="<?= htmlspecialchars($model_filter) ?>">
                        <input type="hidden" name="serial" value="<?= htmlspecialchars($serial_filter) ?>">
                        <input type="hidden" name="merk" value="<?= htmlspecialchars($merk_filter) ?>">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="model" class="form-label">Model</label>
                                <select name="model" id="model" class="form-select form-select-sm" required>
                                    <option value="">Select</option>
                                    <?php 
                                    $model_val = isset($data['model']) ? $data['model'] : '';
                                    foreach(['Finger Print','Face Capture'] as $c): ?>
                                        <option value="<?= $c ?>" <?= $model_val == $c ? 'selected' : ''?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="merk_select" class="form-label">Merk</label>
                                <select name="merk_id" id="merk_select" class="form-select form-select-sm" required>
                                    <option value="">Select Merk</option>
                                    <?php 
                                    $merk_id_val = isset($data['merk_id']) ? $data['merk_id'] : '';
                                    mysqli_data_seek($merk_list, 0); 
                                    while($m=mysqli_fetch_assoc($merk_list)): ?>
                                        <option value="<?= $m['id'] ?>" <?= $merk_id_val == $m['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['merk_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="type_select" class="form-label">Type</label>
                                <select name="type_id" id="type_select" class="form-select form-select-sm" required>
                                    <option value="">Select Type</option>
                                    <?php 
                                    $type_id_val = isset($data['type_id']) ? $data['type_id'] : '';
                                    foreach($type_options as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= $type_id_val == $t['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['serial_number']) ? $data['serial_number'] : '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="mac_address" class="form-label">MAC Address</label>
                                <input type="text" name="mac_address" id="mac_address" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['mac_address']) ? $data['mac_address'] : '') ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="condition" class="form-label">Condition</label>
                                <select name="condition" id="condition" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php $condition_val = isset($data['condition']) ? $data['condition'] : 'Good'; ?>
                                    <option value="Broken" <?= $condition_val == 'Broken' ? 'selected' : '' ?>>Broken</option>
                                    <option value="Good" <?= $condition_val == 'Good' ? 'selected' : '' ?>>Good</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php 
                                    $status_val = isset($data['status']) ? $data['status'] : 'Installed';
                                    foreach(['Disposal','Installed','Spare','Transfer'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $status_val == $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="warranty_expiration" class="form-label">Warranty Expiration</label>
                                <input type="date" name="warranty_expiration" id="warranty_expiration" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['warranty_expiration']) ? $data['warranty_expiration'] : '') ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="po" class="form-label">PO</label>
                                <input type="text" name="po" id="po" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['po']) ? $data['po'] : '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="date_received" class="form-label">Date Received</label>
                                <input type="date" name="date_received" id="date_received" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['date_received']) ? $data['date_received'] : '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="3"><?= htmlspecialchars(isset($data['remarks']) ? $data['remarks'] : '') ?></textarea>
                        </div>

                        <div class="mt-4 border-top pt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= $id ? 'Update' : 'Save' ?></button>
                            <?php
                                // UBAH: Link Cancel kembali ke list dengan filter
                                $cancel_query = http_build_query([
                                    'page' => $page,
                                    'model' => $model_filter,
                                    'serial' => $serial_filter,
                                    'merk' => $merk_filter
                                ]);
                            ?>
                            <a href="attendance.php?<?= $cancel_query ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// UBAH: Dibungkus IIFE dan tambah UX
(function() {
    document.getElementById('merk_select').addEventListener('change', function(){
        var merkId = this.value;
        var typeSelect = document.getElementById('type_select');
        typeSelect.innerHTML = '<option value="">Loading...</option>'; // Kasih placeholder
        
        // UBAH: Pastikan path ini benar
        fetch('get_types_by_merk.php?merk_id='+merkId) 
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            var options='<option value="">Select Type</option>';
            data.forEach(function(t){
                options+=`<option value="${t.id}">${t.type_name}</option>`;
            });
            typeSelect.innerHTML = options;
        })
        .catch(err => {
            console.error('Error fetching types:', err);
            typeSelect.innerHTML = '<option value="">Error loading types</option>';
        });
    });
})();
</script>

</body>
</html>