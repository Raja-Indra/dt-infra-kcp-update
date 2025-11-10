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
    $stmt = $conn->prepare("SELECT * FROM server WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    // Tambah validasi jika data tidak ditemukan
    if (!$data) {
        header('Location: server.php'); 
        exit;
    }
}

// Merk list kategori Server
$merk_list = mysqli_query($conn, "SELECT * FROM merk_asset WHERE category='Server' ORDER BY merk_name");

// Type options kalau edit
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
    <title><?= $id ? 'Edit' : 'Input' ?> Server Asset</title>
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
        /* Style untuk checkbox group */
        .checkbox-group {
            background: #f7fff7;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            padding: 10px;
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
                    <h2 class="h5 mb-0"><?= $id ? 'Edit' : 'Input' ?> Server Asset</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="server_process.php">
                        <?php if($id): ?>
                            <input type="hidden" name="id" value="<?= $id ?>">
                        <?php endif; ?>
                        
                        <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                        <input type="hidden" name="model_filter" value="<?= htmlspecialchars($model_filter) ?>">
                        <input type="hidden" name="serial" value="<?= htmlspecialchars($serial_filter) ?>">
                        <input type="hidden" name="merk" value="<?= htmlspecialchars($merk_filter) ?>">


                        <h5 class="h6 text-secondary">Basic Info</h5>
                        <hr class="mt-1">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="model" class="form-label">Model</label>
                                <select name="model" id="model" class="form-select form-select-sm" required>
                                    <option value="">Select</option>
                                    <?php 
                                    $model_val = isset($data['model']) ? $data['model'] : '';
                                    foreach(['Rack Mount','Tower'] as $c): ?>
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
                                <label for="hostname" class="form-label">Hostname</label>
                                <input type="text" name="hostname" id="hostname" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['hostname']) ? $data['hostname'] : '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['serial_number']) ? $data['serial_number'] : '') ?>">
                            </div>
                        </div>
                        
                        <h5 class="h6 text-secondary mt-4">Tech Specs</h5>
                        <hr class="mt-1">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="processor" class="form-label">Processor</label>
                                <input type="text" name="processor" id="processor" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['processor']) ? $data['processor'] : '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="memory" class="form-label">Memory</label>
                                <input type="text" name="memory" id="memory" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['memory']) ? $data['memory'] : '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="storage_type" class="form-label">Storage Type</label>
                                <select name="storage_type" id="storage_type" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php $storage_type_val = isset($data['storage_type']) ? $data['storage_type'] : ''; ?>
                                    <option value="HDD" <?= $storage_type_val == 'HDD' ? 'selected' : ''?>>HDD</option>
                                    <option value="SSD" <?= $storage_type_val == 'SSD' ? 'selected' : ''?>>SSD</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Storage Capacity</label>
                            <?php
                            $capacity_options = ['1 TB','2 TB','3 TB','4 TB','8 TB'];
                            $storage_values = [];
                            if(!empty($data['storage_capacity'])){
                                $parts = explode(',', $data['storage_capacity']);
                                foreach($parts as $p){
                                    $p = trim($p);
                                    if(preg_match('/Storage (\d+): (.+)/',$p,$m)){
                                        $storage_values[$m[1]] = $m[2];
                                    }
                                }
                            }
                            
                            echo '<div class="row g-2 mb-2">'; // Baris 1
                            for($i=1; $i<=4; $i++): ?>
                                <div class="col-md-3">
                                    <label for="storage_<?= $i ?>" class="form-label small">Storage <?= $i ?></label>
                                    <select name="storage_capacity[<?= $i ?>]" id="storage_<?= $i ?>" class="form-select form-select-sm">
                                        <option value="">-</option>
                                        <?php foreach($capacity_options as $cap): ?>
                                            <option value="<?= $cap ?>" <?= (isset($storage_values[$i]) && $storage_values[$i]==$cap)?'selected':'' ?>>
                                                <?= $cap ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php 
                            endfor; 
                            echo '</div>'; // Tutup baris 1
                            
                            echo '<div class="row g-2">'; // Baris 2
                            for($i=5; $i<=8; $i++): ?>
                                <div class="col-md-3">
                                    <label for="storage_<?= $i ?>" class="form-label small">Storage <?= $i ?></label>
                                    <select name="storage_capacity[<?= $i ?>]" id="storage_<?= $i ?>" class="form-select form-select-sm">
                                        <option value="">-</option>
                                        <?php foreach($capacity_options as $cap): ?>
                                            <option value="<?= $cap ?>" <?= (isset($storage_values[$i]) && $storage_values[$i]==$cap)?'selected':'' ?>>
                                                <?= $cap ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php 
                            endfor; 
                            echo '</div>'; // Tutup baris 2
                            ?>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">RAID System</label>
                                <div class="checkbox-group">
                                    <?php
                                    $raid_selected = explode(',', isset($data['raid_system']) ? $data['raid_system'] : '');
                                    foreach(['RAID 0','RAID 1','RAID 2','RAID 3','RAID 4','RAID 5'] as $opt):
                                    ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="raid_system[]" value="<?= $opt ?>" id="raid_<?= $opt ?>" <?= in_array($opt, $raid_selected)?'checked':'' ?>>
                                            <label class="form-check-label" for="raid_<?= $opt ?>"><?= $opt ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="psu" class="form-label">PSU</label>
                                <select name="psu" id="psu" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php 
                                    $psu_val = isset($data['psu']) ? $data['psu'] : '';
                                    foreach(['Dual Redundant','Single'] as $c): ?>
                                        <option value="<?= $c ?>" <?= $psu_val == $c ? 'selected' : ''?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <h5 class="h6 text-secondary mt-4">OS & Status</h5>
                        <hr class="mt-1">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="virtualization" class="form-label">Virtualization</label>
                                <select name="virtualization" id="virtualization" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php $virt_val = isset($data['virtualization']) ? $data['virtualization'] : ''; ?>
                                    <option value="Yes" <?= $virt_val == 'Yes' ? 'selected' : ''?>>Yes</option>
                                    <option value="No" <?= $virt_val == 'No' ? 'selected' : ''?>>No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="operating_system" class="form-label">Operating System</label>
                                <select name="operating_system" id="operating_system" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php 
                                    $os_val = isset($data['operating_system']) ? $data['operating_system'] : '';
                                    foreach(['Windows Server','Linux Server','VMWare'] as $c): ?>
                                        <option value="<?= $c ?>" <?= $os_val == $c ? 'selected' : ''?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="condition" class="form-label">Condition</label>
                                <select name="condition" id="condition" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php $condition_val = isset($data['condition']) ? $data['condition'] : 'Good'; ?>
                                    <option value="Broken" <?= $condition_val == 'Broken' ? 'selected' : ''?>>Broken</option>
                                    <option value="Good" <?= $condition_val == 'Good' ? 'selected' : ''?>>Good</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php 
                                    $status_val = isset($data['status']) ? $data['status'] : 'Installed';
                                    foreach(['Disposal','Installed','Spare','Transfer'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $status_val == $s ? 'selected' : ''?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="mac_address" class="form-label">MAC Address</label>
                                <input type="text" name="mac_address" id="mac_address" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['mac_address']) ? $data['mac_address'] : '') ?>">
                            </div>
                        </div>

                        <h5 class="h6 text-secondary mt-4">Procurement</h5>
                        <hr class="mt-1">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="po" class="form-label">PO</label>
                                <input type="text" name="po" id="po" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['po']) ? $data['po'] : '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="date_received" class="form-label">Date Received</label>
                                <input type="date" name="date_received" id="date_received" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['date_received']) ? $data['date_received'] : '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="warranty_expiration" class="form-label">Warranty Expiration</label>
                                <input type="date" name="warranty_expiration" id="warranty_expiration" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['warranty_expiration']) ? $data['warranty_expiration'] : '') ?>">
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
                            <a href="server.php?<?= $cancel_query ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    document.getElementById('merk_select').addEventListener('change', function(){
        var merkId = this.value;
        var typeSelect = document.getElementById('type_select');
        typeSelect.innerHTML = '<option value="">Loading...</option>'; 
        
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