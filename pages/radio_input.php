<?php
include '../db.php';
include '../includes/session.php';

$option = $_GET['option'] ?? 'RIG';
if (!in_array($option, ['RIG', 'HT'])) $option = 'RIG';

// UBAH: Ambil parameter filter/halaman untuk redirect kembali
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = null;
if ($id > 0) {
    $table = $option === 'RIG' ? 'radio_rig' : 'radio_ht';
    // UBAH: Prepared statement
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    
    if (!$data) { // Jika ID tidak ditemukan, redirect
        header("Location: radio.php?option=$option");
        exit;
    }
}

// Data untuk select merk/type
$merk_q = mysqli_query($conn, "SELECT id, merk_name FROM merk_asset WHERE category='Radio' ORDER BY merk_name");
$type_q = mysqli_query($conn, "SELECT id, type_name, merk_id FROM type_asset WHERE merk_id IN (SELECT id FROM merk_asset WHERE category='Radio') ORDER BY type_name");

$merk_options = [];
while ($m = mysqli_fetch_assoc($merk_q)) $merk_options[] = $m;
$type_options = [];
while ($t = mysqli_fetch_assoc($type_q)) $type_options[] = $t;

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $id > 0 ? "Edit" : "Input" ?> Radio <?= htmlspecialchars($option) ?></title>
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
                    <h2 class="h5 mb-0"><?= $id > 0 ? "Edit" : "Input" ?> Radio <?= htmlspecialchars($option) ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="radio_process.php?option=<?= $option ?>" enctype="multipart/form-data" autocomplete="off">
                        <?php if ($id > 0): ?>
                            <input type="hidden" name="id" value="<?= $id ?>">
                        <?php endif; ?>
                        
                        <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">

                        <h5 class="h6 text-secondary">Basic Info</h5>
                        <hr class="mt-1">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" name="model" id="model" class="form-control form-control-sm" required value="<?= htmlspecialchars($data['model'] ?? '') ?>">
                            </div>
                             <div class="col-md-6">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control form-control-sm" required value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="merk_id" class="form-label">Merk</label>
                                <select name="merk_id" id="merk_id" class="form-select form-select-sm" required>
                                    <option value="">Select Merk</option>
                                    <?php foreach ($merk_options as $m): ?>
                                        <option value="<?= $m['id'] ?>" <?= isset($data['merk_id']) && $data['merk_id'] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['merk_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="type_id" class="form-label">Type</label>
                                <select name="type_id" id="type_id" class="form-select form-select-sm" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($type_options as $t): ?>
                                        <option value="<?= $t['id'] ?>" data-merk="<?= $t['merk_id'] ?>" 
                                            <?= isset($data['type_id']) && $data['type_id'] == $t['id'] ? 'selected' : '' ?>
                                            style="<?= isset($data['merk_id']) && $data['merk_id'] != $t['merk_id'] ? 'display:none;' : '' ?>">
                                            <?= htmlspecialchars($t['type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if ($option === 'RIG'): ?>
                        
                            <h5 class="h6 text-secondary mt-4">Unit Info</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="unit_number" class="form-label">Unit Number</label>
                                    <input type="text" name="unit_number" id="unit_number" class="form-control form-control-sm" value="<?= htmlspecialchars($data['unit_number'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="unit_type" class="form-label">Unit Type</label>
                                    <input type="text" name="unit_type" id="unit_type" class="form-control form-control-sm" value="<?= htmlspecialchars($data['unit_type'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <h5 class="h6 text-secondary mt-4">Installed Info</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="installed_by_jde" class="form-label">Installed By JDE</label>
                                    <input type="text" name="installed_by_jde" id="installed_by_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['installed_by_jde'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="installed_by_name" class="form-label">Installed By Name</label>
                                    <input type="text" name="installed_by_name" id="installed_by_name" class="form-control form-control-sm" value="<?= htmlspecialchars($data['installed_by_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="date_installed" class="form-label">Date Installed</label>
                                    <input type="date" name="date_installed" id="date_installed" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_installed'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <h5 class="h6 text-secondary mt-4">Removed Info</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="removed_by_jde" class="form-label">Removed By JDE</label>
                                    <input type="text" name="removed_by_jde" id="removed_by_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['removed_by_jde'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="removed_by_name" class="form-label">Removed By Name</label>
                                    <input type="text" name="removed_by_name" id="removed_by_name" class="form-control form-control-sm" value="<?= htmlspecialchars($data['removed_by_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="date_removed" class="form-label">Date Removed</label>
                                    <input type="date" name="date_removed" id="date_removed" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_removed'] ?? '') ?>">
                                </div>
                            </div>

                        <?php else: ?>
                            
                            <h5 class="h6 text-secondary mt-4">Assignment Info</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="assign_to_jde" class="form-label">Assign To JDE</label>
                                    <input type="text" name="assign_to_jde" id="assign_to_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['assign_to_jde'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="assign_to_name" class="form-label">Assign To Name</label>
                                    <input type="text" name="assign_to_name" id="assign_to_name" class="form-control form-control-sm" value="<?= htmlspecialchars($data['assign_to_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="assign_to_dept" class="form-label">Assign To Dept</label>
                                    <input type="text" name="assign_to_dept" id="assign_to_dept" class="form-control form-control-sm" value="<?= htmlspecialchars($data['assign_to_dept'] ?? '') ?>" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="assign_by_jde" class="form-label">Assign By JDE</label>
                                    <input type="text" name="assign_by_jde" id="assign_by_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['assign_by_jde'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="assign_by_name" class="form-label">Assign By Name</label>
                                    <input type="text" name="assign_by_name" id="assign_by_name" class="form-control form-control-sm" value="<?= htmlspecialchars($data['assign_by_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="date_assign" class="form-label">Date Assign</label>
                                    <input type="date" name="date_assign" id="date_assign" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_assign'] ?? '') ?>">
                                </div>
                            </div>

                            <h5 class="h6 text-secondary mt-4">Return Info</h5><hr class="mt-1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="return_to_jde" class="form-label">Return To JDE</label>
                                    <input type="text" name="return_to_jde" id="return_to_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['return_to_jde'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="return_to_name" class="form-label">Return To Name</label>
                                    <input type="text" name="return_to_name" id="return_to_name" class="form-control form-control-sm" value="<?= htmlspecialchars($data['return_to_name'] ?? '') ?>" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="return_by_jde" class="form-label">Return By JDE</label>
                                    <input type="text" name="return_by_jde" id="return_by_jde" class="form-control form-control-sm" value="<?= htmlspecialchars($data['return_by_jde'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="return_by_name" class="form-label">Return By Name</label>
                                    <input type="text" name="return_by_name" id="return_by_name" class="form-control form-control-sm" value="<?= htmlspecialchars($data['return_by_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="date_return" class="form-label">Date Return</label>
                                    <input type="date" name="date_return" id="date_return" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_return'] ?? '') ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <h5 class="h6 text-secondary mt-4">Status & Procurement</h5><hr class="mt-1">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="condition" class="form-label">Condition</label>
                                <input type="text" name="condition" id="condition" class="form-control form-control-sm" value="<?= htmlspecialchars($data['condition'] ?? 'Good') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <input type="text" name="status" id="status" class="form-control form-control-sm" value="<?= htmlspecialchars($data['status'] ?? 'Installed') ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-4">
                                <label for="po" class="form-label">PO</label>
                                <input type="text" name="po" id="po" class="form-control form-control-sm" value="<?= htmlspecialchars($data['po'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="date_received" class="form-label">Date Received</label>
                                <input type="date" name="date_received" id="date_received" class="form-control form-control-sm" value="<?= htmlspecialchars($data['date_received'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="warranty_expiration" class="form-label">Warranty Expiration</label>
                                <input type="date" name="warranty_expiration" id="warranty_expiration" class="form-control form-control-sm" value="<?= htmlspecialchars($data['warranty_expiration'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
                        </div>
                        
                        <?php if ($option === 'HT'): ?>
                        <div class="mb-3">
                            <label for="bast_file" class="form-label">BAST (PDF, Optional)</label>
                            <?php if (!empty($data['bast_file'])): ?>
                                <span class="d-block mb-1 small"><a href="../uploads/<?= htmlspecialchars($data['bast_file']) ?>" target="_blank">View Current File</a></span>
                            <?php endif; ?>
                            <input type="file" name="bast_file" id="bast_file" class="form-control form-control-sm" accept="application/pdf">
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 border-top pt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= $id > 0 ? "Update" : "Save" ?></button>
                            <a href="radio.php?option=<?= $option ?>&page=<?= $page ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-outline-secondary">Cancel</a>
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
    // Type filtering by Merk
    document.getElementById('merk_id').addEventListener('change', function() {
        var merkId = this.value;
        var typeSelect = document.getElementById('type_id');
        Array.from(typeSelect.options).forEach(function(opt) {
            opt.style.display = (!merkId || !opt.getAttribute('data-merk') || opt.getAttribute('data-merk') === merkId) ? '' : 'none';
        });
        // Reset selection if current type not valid
        if (typeSelect.selectedOptions.length && typeSelect.selectedOptions[0].style.display === 'none') {
            typeSelect.selectedIndex = 0;
        }
    });

    // Autofill Name/Dept from JDE (for both RIG and HT)
    function setupAutoFill(jdeId, nameId, deptId=null) {
        var jdeInput = document.getElementById(jdeId);
        if (!jdeInput) return;
        jdeInput.addEventListener('blur', function() {
            var jde = jdeInput.value.trim();
            if (!jde) {
                if (nameId) document.getElementById(nameId).value = '';
                if (deptId) document.getElementById(deptId).value = '';
                return;
            }
            var xhr = new XMLHttpRequest();
            // UBAH: Pastikan path ini benar
            xhr.open('GET', 'get_employee.php?jde=' + encodeURIComponent(jde), true); 
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var obj = JSON.parse(xhr.responseText);
                        if (nameId) document.getElementById(nameId).value = obj.name || '';
                        if (deptId) document.getElementById(deptId).value = obj.department || '';
                    } catch(e) {
                        if (nameId) document.getElementById(nameId).value = '';
                        if (deptId) document.getElementById(deptId).value = '';
                    }
                }
            };
            xhr.send();
        });
    }

    // --- Untuk RIG ---
    setupAutoFill('installed_by_jde', 'installed_by_name');
    setupAutoFill('removed_by_jde', 'removed_by_name');

    // --- Untuk HT ---
    setupAutoFill('assign_to_jde', 'assign_to_name', 'assign_to_dept');
    setupAutoFill('assign_by_jde', 'assign_by_name');
    setupAutoFill('return_to_jde', 'return_to_name');
    setupAutoFill('return_by_jde', 'return_by_name');
    
    // UBAH: Jalankan filter Merk saat halaman dimuat (jika sedang edit)
    if (document.getElementById('merk_id').value) {
         document.getElementById('merk_id').dispatchEvent(new Event('change'));
    }
});
</script>
</body>
</html>