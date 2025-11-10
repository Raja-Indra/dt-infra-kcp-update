<?php
include '../db.php';
include '../includes/session.php';

// Tangani POST sebelum ada output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device        = $_POST['device'];
    $part_name     = $_POST['part_name'];
    $description   = $_POST['description'];
    $qty           = floatval($_POST['qty']);
    $uom           = $_POST['uom'] ?? '';
    $est_cost      = floatval(str_replace(['.', ','], ['', '.'], $_POST['est_cost'])) ?: 0;
    $total_est     = $qty * $est_cost;
    $pr            = trim($_POST['pr']);
    $po            = trim($_POST['po']);
    $date_created  = $_POST['date_created'];
    $item_type     = $_POST['item_type'] ?? '';
    $date_received = $_POST['date_received'] ?: null;
    $qty_received  = floatval($_POST['qty_received']) ?: 0;
    $qty_dev       = $qty - $qty_received;
    $cost_actual   = floatval(str_replace(['.', ','], ['', '.'], $_POST['cost_actual'])) ?: 0;
    $order_proc    = $_POST['order_process'] ?? '';
    $remarks       = trim($_POST['remarks']);

    $stmt = $conn->prepare("
      INSERT INTO material_request
        (device, part_name, description, qty, uom, est_cost, total_est_cost,
         pr, po, date_created, item_type, date_received,
         qty_received, qty_deviation, cost_actual, order_process, remarks)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // UBAH: Tipe bind_param diperbaiki (i -> d untuk float/double)
    $stmt->bind_param(
        'sssdsddssssssddss', // sebelumnya: sssisdisssssiidss
        $device, $part_name, $description, $qty, $uom, $est_cost, $total_est,
        $pr, $po, $date_created, $item_type, $date_received,
        $qty_received, $qty_dev, $cost_actual, $order_proc, $remarks
    );
    $stmt->execute();
    
    // UBAH: Redirect dengan notifikasi
    header('Location: material_request.php?added=1');
    exit;
}

// include nav & session
include '../nav.php';

// ambil daftar device
$devices = mysqli_query($conn, "SELECT DISTINCT device FROM parts ORDER BY device");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Material Request</title>
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
                    <h2 class="h5 mb-0">Add Material Request</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="device" class="form-label">Device</label>
                                <select name="device" id="device" class="form-select form-select-sm" required>
                                    <option value="">-- Select Device --</option>
                                    <?php mysqli_data_seek($devices, 0); ?>
                                    <?php while($d=mysqli_fetch_assoc($devices)): ?>
                                        <option value="<?= htmlspecialchars($d['device']) ?>"><?= htmlspecialchars($d['device']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="part_name" class="form-label">Part Name</label>
                                <select name="part_name" id="part_name" class="form-select form-select-sm" required>
                                    <option value="">-- Select Device First --</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control form-control-sm" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="qty" class="form-label">Qty</label>
                                <input type="number" name="qty" id="qty" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label for="uom" class="form-label">UoM</label>
                                <select name="uom" id="uom" class="form-select form-select-sm">
                                    <option value="EA">EA</option>
                                    <?php foreach(['UN','RL','PH','MR','PR','ST','LN','BX'] as $u): ?>
                                        <option value="<?= $u ?>"><?= $u ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="item_type" class="form-label">Item Type</label>
                                <select name="item_type" id="item_type" class="form-select form-select-sm" required>
                                    <option value="">-- Select Item Type --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Invoice">Invoice</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="est_cost" class="form-label">Estimated Cost</label>
                                <input type="text" name="est_cost" id="est_cost" class="form-control form-control-sm" placeholder="0,00">
                            </div>
                            <div class="col-md-6">
                                <label for="total_est_cost" class="form-label">Total Estimated Cost</label>
                                <input type="text" id="total_est_cost" class="form-control form-control-sm" readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="pr" class="form-label">PR</label>
                                <input type="text" name="pr" id="pr" class="form-control form-control-sm" placeholder="e.g. ABC123">
                            </div>
                            <div class="col-md-4">
                                <label for="po" class="form-label">PO</label>
                                <input type="text" name="po" id="po" class="form-control form-control-sm" placeholder="e.g. PO456">
                            </div>
                            <div class="col-md-4">
                                <label for="date_created" class="form-label">Date Created</label>
                                <input type="date" name="date_created" id="date_created" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="qty_received" class="form-label">Qty Received</label>
                                <input type="number" name="qty_received" id="qty_received" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="qty_deviation" class="form-label">Qty Deviation</label>
                                <input type="text" id="qty_deviation" class="form-control form-control-sm" readonly>
                            </div>
                             <div class="col-md-4">
                                <label for="date_received" class="form-label">Date Received</label>
                                <input type="date" name="date_received" id="date_received" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cost_actual" class="form-label">Cost Actual</label>
                                <input type="text" name="cost_actual" id="cost_actual" class="form-control form-control-sm" placeholder="0,00">
                            </div>
                            <div class="col-md-6">
                                <label for="order_process" class="form-label">Order Process</label>
                                <select name="order_process" id="order_process" class="form-select form-select-sm" required>
                                    <option value="">-- Select Order Process --</option>
                                    <?php foreach([
                                    'Waiting Approval PR','Waiting Quotation','PR Approved',
                                    'Waiting Approval PO','PO Release','Partial','Delivered','Completed'
                                    ] as $opt): ?>
                                    <option value="<?= $opt ?>"><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="3"></textarea>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="material_request.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    // Fungsi Kalkulasi Biaya
    function calculateCosts(){
        let q = +document.getElementById('qty').value || 0;
        let e = parseFloat(document.getElementById('est_cost').value.replace(/\./g,'').replace(',','.')) || 0;
        document.getElementById('total_est_cost').value =
            (q * e).toLocaleString('id-ID',{minimumFractionDigits:2, maximumFractionDigits: 2});
    }

    // Fungsi Kalkulasi Deviasi
    function calculateDeviation(){
        let q = +document.getElementById('qty').value || 0;
        let r = +document.getElementById('qty_received').value || 0;
        document.getElementById('qty_deviation').value =
            (q - r).toLocaleString('id-ID',{minimumFractionDigits:0, maximumFractionDigits: 0});
    }

    // Fungsi Format Mata Uang
    function setupCurrency(id){
        let el = document.getElementById(id);
        if (!el) return;
        
        el.addEventListener('blur', function(){
            let v = parseFloat(el.value.replace(/\./g,'').replace(',','.')) || 0;
            el.value = v.toLocaleString('id-ID',{minimumFractionDigits:2, maximumFractionDigits: 2});
            calculateCosts(); 
        });
        
        el.addEventListener('focus', function(){
            let rawValue = el.value.replace(/\./g,'').replace(',','.');
            if (parseFloat(rawValue) === 0) {
                el.value = '';
            } else {
                el.value = rawValue;
            }
        });
    }

    setupCurrency('est_cost');
    setupCurrency('cost_actual');
    
    // Event Listeners
    const qtyInput = document.getElementById('qty');
    const qtyReceivedInput = document.getElementById('qty_received');
    
    qtyInput.addEventListener('input', calculateCosts);
    qtyInput.addEventListener('input', calculateDeviation);
    qtyReceivedInput.addEventListener('input', calculateDeviation); 

    // AJAX untuk Part Name
    document.getElementById('device').addEventListener('change', function(){
        let partSelect = document.getElementById('part_name');
        
        // UBAH: Jangan reset deskripsi jika user sudah mengetik
        // document.getElementById('description').value = ''; 
        
        partSelect.innerHTML = '<option value="">Loading...</option>';
        
        fetch('get_part_by_device.php?device=' + encodeURIComponent(this.value))
            .then(r => r.text())
            .then(html => {
                partSelect.innerHTML = html;
            });
    });

    // UBAH: HAPUS FUNGSI UNTUK AUTO-FILL DESCRIPTION
    /* document.getElementById('part_name').addEventListener('change', function() {
        // ... BLOK INI DIHAPUS ...
    });
    */

    // Panggil kalkulasi saat load
    calculateCosts();
    calculateDeviation();
})();
</script>

</body>
</html>