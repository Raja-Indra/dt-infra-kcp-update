<?php
include '../db.php';
include '../includes/session.php';

$option = $_GET['option'] ?? 'Desktop';
if (!in_array($option, ['Desktop', 'Laptop'])) {
    die("Invalid Option");
}

// UBAH: Ambil parameter filter/halaman untuk redirect kembali
$page = $_GET['page'] ?? 1;
$filter_serial = $_GET['filter_serial'] ?? '';
$filter_hostname = $_GET['filter_hostname'] ?? '';
$filter_jde = $_GET['filter_jde'] ?? '';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = [];
if ($id) {
    $table = strtolower($option);
    // UBAH: Prepared statement
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $q = $stmt->get_result();
    $data = $q->fetch_assoc();

    if (!$data) { // Jika ID tidak ditemukan, redirect
        header("Location: computer.php?option=$option");
        exit;
    }
}

// Data untuk select merk/type
$merk_q = mysqli_query($conn, "SELECT * FROM merk_asset WHERE category='Computer' ORDER BY merk_name");
$type_options = [];
if (!empty($data['merk_id'])) {
    // UBAH: Prepared statement
    $stmt_type = $conn->prepare("SELECT * FROM type_asset WHERE merk_id = ? ORDER BY type_name");
    $stmt_type->bind_param("i", $data['merk_id']);
    $stmt_type->execute();
    $type_q = $stmt_type->get_result();
    while($t = $type_q->fetch_assoc()){
        $type_options[] = $t;
    }
}

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $id ? 'Edit' : 'Input' ?> Computer <?= htmlspecialchars($option) ?></title>
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
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0"><?= $id ? 'Edit' : 'Input' ?> Computer <?= htmlspecialchars($option) ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="computer_process.php" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="option" value="<?= htmlspecialchars($option) ?>">
                        <?php if($id): ?>
                            <input type="hidden" name="id" value="<?= $id ?>">
                        <?php endif; ?>
                        
                        <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                        <input type="hidden" name="filter_serial" value="<?= htmlspecialchars($filter_serial) ?>">
                        <input type="hidden" name="filter_hostname" value="<?= htmlspecialchars($filter_hostname) ?>">
                        <input type="hidden" name="filter_jde" value="<?= htmlspecialchars($filter_jde) ?>">

                        <h5 class="h6 text-secondary">Basic Info</h5>
                        <hr class="mt-1">
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" name="model" id="model" class="form-control form-control-sm" value="<?= htmlspecialchars($option) ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="merk_select" class="form-label">Merk</label>
                                <select name="merk_id" id="merk_select" class="form-select form-select-sm" required>
                                    <option value="">Select Merk</option>
                                    <?php mysqli_data_seek($merk_q, 0); while($m=mysqli_fetch_assoc($merk_q)): ?>
                                        <option value="<?= $m['id'] ?>" <?= ($data['merk_id']??'')==$m['id']?'selected':'' ?>>
                                            <?= htmlspecialchars($m['merk_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="type_select" class="form-label">Type</label>
                                <select name="type_id" id="type_select" class="form-select form-select-sm" required>
                                    <option value="">Select Type</option>
                                    <?php foreach($type_options as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($data['type_id']??'')==$t['id']?'selected':'' ?>>
                                            <?= htmlspecialchars($t['type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="hostname" class="form-label">Hostname</label>
                                <input type="text" name="hostname" id="hostname" class="form-control form-control-sm" value="<?= htmlspecialchars($data['hostname']??'') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="serial_number_mobo" class="form-label">Serial Number Mobo</label>
                                <input type="text" name="serial_number_mobo" id="serial_number_mobo" class="form-control form-control-sm" value="<?= htmlspecialchars($data['serial_number_mobo']??'') ?>">
                            </div>
                        </div>

                        <h5 class="h6 text-secondary mt-4">Tech Specs</h5>
                        <hr class="mt-1">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="processor" class="form-label">Processor</label>
                                <input type="text" name="processor" id="processor" class="form-control form-control-sm" value="<?= htmlspecialchars($data['processor']??'') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="memory" class="form-label">Memory</label>
                                <input type="text" name="memory" id="memory" class="form-control form-control-sm" value="<?= htmlspecialchars($data['memory']??'') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="storage" class="form-label">Storage</label>
                                <input type="text" name="storage" id="storage" class="form-control form-control-sm" value="<?= htmlspecialchars($data['storage']??'') ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mac_wifi" class="form-label">MAC WIFI</label>
                                <input type="text" name="mac_wifi" id="mac_wifi" class="form-control form-control-sm" value="<?= htmlspecialchars($data['mac_wifi']??'') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="mac_lan" class="form-label">MAC LAN</label>
                                <input type="text" name="mac_lan" id="mac_lan" class="form-control form-control-sm" value="<?= htmlspecialchars($data['mac_lan']??'') ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="monitor" class="form-label">Monitor</label>
                                <input type="text" name="monitor" id="monitor" class="form-control form-control-sm" value="<?= htmlspecialchars($data['monitor']??'') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="serial_number_monitor" class="form-label">Serial Number Monitor</label>
                                <input type="text" name="serial_number_monitor" id="serial_number_monitor" class="form-control form-control-sm" value="<?= htmlspecialchars($data['serial_number_monitor']??'') ?>">
                            </div>
                        </div>

                        <?php if($option==='Desktop'): ?>
                            <h5 class="h6 text-secondary mt-4">User & Installation</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="user_jde" class="form-label">User By JDE</label>
                                    <input type="text" name="user_by_jde" id="user_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['user_by_jde']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="user_name" class="form-label">User By Name</label>
                                    <input type="text" name="user_by_name" id="user_name" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['user_by_name']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="user_dept" class="form-label">User By Dept</label>
                                    <input type="text" name="user_by_dept" id="user_dept" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['user_by_dept']??'') ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="installed_jde" class="form-label">Installed By JDE</label>
                                    <input type="text" name="installed_by_jde" id="installed_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['installed_by_jde']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="installed_name" class="form-label">Installed By Name</label>
                                    <input type="text" name="installed_by_name" id="installed_name" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['installed_by_name']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="date_installed" class="form-label">Date Installed</label>
                                    <input type="date" name="date_installed" id="date_installed" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_installed']??'') ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="removed_jde" class="form-label">Removed By JDE</label>
                                    <input type="text" name="removed_by_jde" id="removed_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['removed_by_jde']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="removed_name" class="form-label">Removed By Name</label>
                                    <input type="text" name="removed_by_name" id="removed_name" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['removed_by_name']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="date_removed" class="form-label">Date Removed</label>
                                    <input type="date" name="date_removed" id="date_removed" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_removed']??'') ?>">
                                </div>
                            </div>
                        
                        <?php else: ?>
                            <h5 class="h6 text-secondary mt-4">Assignment Info</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="assign_to_jde" class="form-label">Assign To JDE</label>
                                    <input type="text" name="assign_to_jde" id="assign_to_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['assign_to_jde']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="assign_to_name" class="form-label">Assign To Name</label>
                                    <input type="text" name="assign_to_name" id="assign_to_name" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['assign_to_name']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="assign_to_dept" class="form-label">Assign To Dept</label>
                                    <input type="text" name="assign_to_dept" id="assign_to_dept" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['assign_to_dept']??'') ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="assign_by_jde" class="form-label">Assign By JDE</label>
                                    <input type="text" name="assign_by_jde" id="assign_by_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['assign_by_jde']??'') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="assign_by_name" class="form-label">Assign By Name</label>
                                    <input type="text" name="assign_by_name" id="assign_by_name" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['assign_by_name']??'') ?>">
                                </div>
                            </div>
                             <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="date_assign" class="form-label">Date Assign</label>
                                    <input type="date" name="date_assign" id="date_assign" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_assign']??'') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="bast_file" class="form-label">BAST File</label>
                                    <?php if(!empty($data['bast_file'])): ?>
                                    <div class="small mb-1">
                                        <a href="../uploads/<?= htmlspecialchars($data['bast_file']) ?>" target="_blank">View Current File</a> |
                                        <a href="computer_delete_bast.php?id=<?= $data['id'] ?>&option=<?= $option ?>" class="text-danger" onclick="return confirm('Delete this file?')">Delete File</a>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" name="bast_file" id="bast_file" class="form-control form-control-sm">
                                </div>
                            </div>

                            <h5 class="h6 text-secondary mt-4">Return Info</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="return_to_jde" class="form-label">Return To JDE</label>
                                    <input type="text" name="return_to_jde" id="return_to_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['return_to_jde']??'') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="return_to_name" class="form-label">Return To Name</label>
                                    <input type="text" name="return_to_name" id="return_to_name" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['return_to_name']??'') ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="return_by_jde" class="form-label">Return By JDE</label>
                                    <input type="text" name="return_by_jde" id="return_by_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['return_by_jde']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="return_by_name" class="form-label">Return By Name</label>
                                    <input type="text" name="return_by_name" id="return_by_name" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($data['return_by_name']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="date_return" class="form-label">Date Return</label>
                                    <input type="date" name="date_return" id="date_return" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_return']??'') ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <h5 class="h6 text-secondary mt-4">Status & Procurement</h5><hr class="mt-1">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="condition" class="form-label">Condition</label>
                                <select name="condition" id="condition" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <option value="Broken" <?= ($data['condition']??'')=='Broken'?'selected':'' ?>>Broken</option>
                                    <option value="Good" <?= ($data['condition']??'Good')=='Good'?'selected':'' ?>>Good</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select form-select-sm">
                                    <?php
                                    $defaultStatus = ($option==='Desktop') ? 'Installed' : 'Used';
                                    $statusOptions = ($option==='Desktop') ? ['Disposal','Installed','Spare','Transfer'] : ['Disposal','Used','Spare','Transfer'];
                                    foreach($statusOptions as $s){
                                        $sel = ($data['status'] ?? $defaultStatus) == $s ? 'selected' : '';
                                        echo "<option value='$s' $sel>$s</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="warranty_expiration" class="form-label">Warranty Expiration</label>
                                <input type="date" name="warranty_expiration" id="warranty_expiration" class="form-control form-control-sm" value="<?= htmlspecialchars($data['warranty_expiration']??'') ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="po" class="form-label">PO</label>
                                <input type="text" name="po" id="po" class="form-control form-control-sm" value="<?= htmlspecialchars($data['po']??'') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="date_received" class="form-label">Date Received</label>
                                <input type="date" name="date_received" id="date_received" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_received']??'') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($data['remarks']??'') ?></textarea>
                        </div>
                        
                        <div class="mt-4 border-top pt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= $id ? "Update" : "Save" ?></button>
                            <?php
                                $cancel_query = http_build_query([
                                    'option' => $option,
                                    'page' => $page,
                                    'filter_serial' => $filter_serial,
                                    'filter_hostname' => $filter_hostname,
                                    'filter_jde' => $filter_jde
                                ]);
                            ?>
                            <a href="computer.php?<?= $cancel_query ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi AJAX untuk ambil data Merk -> Tipe
    document.getElementById('merk_select').addEventListener('change', function() {
        var merkId = this.value;
        var typeSelect = document.getElementById('type_select');
        typeSelect.innerHTML = '<option value="">Loading...</option>'; // Kasih placeholder
        
        fetch('get_types_by_merk.php?merk_id=' + merkId)
        .then(res => res.json())
        .then(data => {
            var options = '<option value="">Select Type</option>';
            data.forEach(function(t) {
                options += `<option value="${t.id}">${t.type_name}</option>`;
            });
            typeSelect.innerHTML = options;
        })
        .catch(err => {
             typeSelect.innerHTML = '<option value="">Error loading types</option>';
        });
    });

    // Fungsi Autofill Name/Dept dari JDE
    function setupAutoFill(jdeId, nameId, deptId = null) {
        var jdeInput = document.getElementById(jdeId);
        if (!jdeInput) return; // Lewati jika elemen tidak ada (misal di form HT vs RIG)
        
        jdeInput.addEventListener('blur', function() {
            var jde = jdeInput.value.trim();
            // Target elemen
            var nameEl = document.getElementById(nameId);
            var deptEl = deptId ? document.getElementById(deptId) : null;

            // Reset jika JDE kosong
            if (!jde) {
                if (nameEl) nameEl.value = '';
                if (deptEl) deptEl.value = '';
                return;
            }
            
            // Panggil AJAX
            var xhr = new XMLHttpRequest();
            // UBAH: Pastikan path ini benar
            xhr.open('GET', 'get_employee.php?jde=' + encodeURIComponent(jde), true); 
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var obj = JSON.parse(xhr.responseText);
                        if (nameEl) nameEl.value = obj.name || '';
                        if (deptEl) deptEl.value = obj.department || '';
                    } catch(e) {
                        if (nameEl) nameEl.value = '';
                        if (deptEl) deptEl.value = '';
                    }
                }
            };
            xhr.send();
        });
    }

    // --- Untuk Desktop ---
    setupAutoFill('user_jde', 'user_name', 'user_dept');
    setupAutoFill('installed_jde', 'installed_name');
    setupAutoFill('removed_jde', 'removed_name');

    // --- Untuk Laptop ---
    setupAutoFill('assign_to_jde', 'assign_to_name', 'assign_to_dept');
    setupAutoFill('assign_by_jde', 'assign_by_name');
    setupAutoFill('return_to_jde', 'return_to_name');
    setupAutoFill('return_by_jde', 'return_by_name');
});
</script>
</body>
</html>