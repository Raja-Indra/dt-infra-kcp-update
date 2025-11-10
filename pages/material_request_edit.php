<?php
include '../db.php';
include '../includes/session.php';

$id = intval($_GET['id'] ?? 0);

// UBAH: Ambil parameter filter/halaman untuk redirect kembali
$page = $_GET['page'] ?? 1;
$filter_pr = $_GET['filter_pr'] ?? '';
$filter_po = $_GET['filter_po'] ?? '';

if (!$id) {
    header('Location: material_request.php');
    exit;
}

// UBAH: Buat query string untuk link "Cancel" dan "Update"
$return_query = http_build_query([
    'page' => $page,
    'filter_pr' => $filter_pr,
    'filter_po' => $filter_po
]);


// Tangani POST
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id            = intval($_POST['id']);
    $device        = $_POST['device'];
    $part_name     = $_POST['part_name'];
    $description   = $_POST['description'];
    $qty           = floatval($_POST['qty']);
    $uom           = $_POST['uom'] ?? '';
    $est_cost      = floatval(str_replace(['.',','],['','.'],$_POST['est_cost']))?:0;
    $total_est     = $qty * $est_cost;
    $pr            = trim($_POST['pr']);
    $po            = trim($_POST['po']);
    $date_created  = $_POST['date_created'];
    $item_type     = $_POST['item_type'] ?? '';
    $date_received = $_POST['date_received'] ?: null;
    $qty_received  = floatval($_POST['qty_received'])?:0;
    $qty_dev       = $qty - $qty_received;
    $cost_actual   = floatval(str_replace(['.',','],['','.'],$_POST['cost_actual']))?:0;
    $order_proc    = $_POST['order_process'] ?? '';
    $remarks       = trim($_POST['remarks']);

    $upd=$conn->prepare("
        UPDATE material_request SET
            device=?, part_name=?, description=?, qty=?, uom=?, est_cost=?, total_est_cost=?,
            pr=?, po=?, date_created=?, item_type=?, date_received=?,
            qty_received=?, qty_deviation=?, cost_actual=?, order_process=?, remarks=?
        WHERE id=?
    ");
    
    // Tipe bind_param Anda sudah benar (sssd s dd ssss s ddd ssi)
    $upd->bind_param(
        'sssdsddsssssddssi', 
        $device,$part_name,$description,$qty,$uom,$est_cost,$total_est,
        $pr,$po,$date_created,$item_type,$date_received,
        $qty_received,$qty_dev,$cost_actual,$order_proc,$remarks,
        $id
    );
    $upd->execute();
    
    // UBAH: Redirect dengan notifikasi sukses DAN filter
    header('Location: material_request.php?updated=1&' . $return_query); 
    exit;
}

// Ambil data untuk form
$stmt=$conn->prepare("SELECT * FROM material_request WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$row=$stmt->get_result()->fetch_assoc();

if (!$row) {
    header('Location: material_request.php');
    exit;
}

// Ambil daftar device
$devices=mysqli_query($conn,"SELECT DISTINCT device FROM parts ORDER BY device");

// Ambil daftar parts untuk device yang sedang dipilih
// (Menggunakan logika dari file Anda sebelumnya, berdasarkan struktur tabel sub_parts)
$parts_q = $conn->prepare("SELECT sp.part_name, sp.part_name as description 
    FROM sub_parts sp
    JOIN parts p ON sp.part_id = p.id 
    WHERE p.device = ? 
    ORDER BY sp.part_name");
$parts_q->bind_param('s', $row['device']);
$parts_q->execute();
$parts = $parts_q->get_result();


include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Material Request</title>
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
                    <h2 class="h5 mb-0">Edit Material Request</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="device" class="form-label">Device</label>
                                <select name="device" id="device" class="form-select form-select-sm" required>
                                    <option value="">-- Select Device --</option>
                                    <?php mysqli_data_seek($devices, 0);
                                    while($d=mysqli_fetch_assoc($devices)): ?>
                                    <option value="<?= htmlspecialchars($d['device']) ?>"
                                        <?= $d['device']===$row['device']?'selected':''?>>
                                        <?= htmlspecialchars($d['device'])?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="part_name" class="form-label">Part Name</label>
                                <select name="part_name" id="part_name" class="form-select form-select-sm" required>
                                    <option value="">-- Select Part --</option>
                                    <?php while($p = $parts->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($p['part_name']) ?>"
                                        data-desc="<?= htmlspecialchars($p['description']) ?>"
                                        <?= $p['part_name'] === $row['part_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['part_name']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control form-control-sm" value="<?= htmlspecialchars($row['description'])?>" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="qty" class="form-label">Qty</label>
                                <input type="number" name="qty" id="qty" class="form-control form-control-sm" value="<?= $row['qty'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="uom" class="form-label">UoM</label>
                                <select name="uom" id="uom" class="form-select form-select-sm">
                                    <?php foreach(['EA','UN','RL','PH','MR','PR','ST','LN','BX'] as $u): ?>
                                    <option value="<?= $u ?>" <?= $row['uom']===$u?'selected':''?>><?= $u?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="item_type" class="form-label">Item Type</label>
                                <select name="item_type" id="item_type" class="form-select form-select-sm" required>
                                    <option value="Cash"    <?= $row['item_type']==='Cash'?'selected':''?>>Cash</option>
                                    <option value="Invoice" <?= $row['item_type']==='Invoice'?'selected':''?>>Invoice</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="est_cost" class="form-label">Estimated Cost</label>
                                <input type="text" name="est_cost" id="est_cost" class="form-control form-control-sm"
                                    value="<?= number_format($row['est_cost'],2,',','.')?>">
                            </div>
                            <div class="col-md-6">
                                <label for="total_est_cost" class="form-label">Total Estimated Cost</label>
                                <input type="text" id="total_est_cost" class="form-control form-control-sm" readonly
                                    value="<?= number_format($row['total_est_cost'],2,',','.')?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="pr" class="form-label">PR</label>
                                <input type="text" name="pr" id="pr" class="form-control form-control-sm" value="<?= htmlspecialchars($row['pr']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="po" class="form-label">PO</label>
                                <input type="text" name="po" id="po" class="form-control form-control-sm" value="<?= htmlspecialchars($row['po']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="date_created" class="form-label">Date Created</label>
                                <input type="date" name="date_created" id="date_created" class="form-control form-control-sm" value="<?= $row['date_created'] ?>">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="qty_received" class="form-label">Qty Received</label>
                                <input type="number" name="qty_received" id="qty_received" class="form-control form-control-sm" value="<?= $row['qty_received'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="qty_deviation" class="form-label">Qty Deviation</label>
                                <input type="text" id="qty_deviation" class="form-control form-control-sm" readonly
                                    value="<?= number_format($row['qty_deviation'],0,',','.')?>">
                            </div>
                            <div class="col-md-4">
                                 <label for="date_received" class="form-label">Date Received</label>
                                <input type="date" name="date_received" id="date_received" class="form-control form-control-sm" value="<?= $row['date_received'] ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cost_actual" class="form-label">Cost Actual</label>
                                <input type="text" name="cost_actual" id="cost_actual" class="form-control form-control-sm"
                                    value="<?= number_format($row['cost_actual'],2,',','.')?>">
                            </div>
                            <div class="col-md-6">
                                <label for="order_process" class="form-label">Order Process</label>
                                <select name="order_process" id="order_process" class="form-select form-select-sm" required>
                                    <?php foreach([
                                    'Completed','Delivered','Partial','PO Release','PR Approved',
                                    'Waiting Approval PO','Waiting Approval PR','Waiting Quotation'
                                    ] as $opt): ?>
                                    <option value="<?= $opt?>" <?= $row['order_process']===$opt?'selected':''?>><?= $opt?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($row['remarks'])?></textarea>
                        </div>

                        <div class="mt-4 border-top pt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="material_request.php?<?= $return_query ?>" class="btn btn-outline-secondary">Cancel</a>
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
    function calculateCosts(){
        let q=+document.getElementById('qty').value||0,
            e=parseFloat(document.getElementById('est_cost').value.replace(/\./g,'').replace(',','.'))||0;
        document.getElementById('total_est_cost').value=
            (q*e).toLocaleString('id-ID',{minimumFractionDigits:2, maximumFractionDigits: 2});
    }
    function calculateDeviation(){
        let q=+document.getElementById('qty').value||0,
            r=+document.getElementById('qty_received').value||0;
        document.getElementById('qty_deviation').value=
            (q-r).toLocaleString('id-ID',{minimumFractionDigits:0, maximumFractionDigits: 0});
    }
    function setupCurrency(id){
        let el=document.getElementById(id);
        if (!el) return;
        
        el.addEventListener('blur',function(){
            let v=parseFloat(el.value.replace(/\./g,'').replace(',','.'))||0;
            el.value=v.toLocaleString('id-ID',{minimumFractionDigits:2, maximumFractionDigits: 2});
            calculateCosts(); 
        });
        
        el.addEventListener('focus',function(){
            let rawValue = el.value.replace(/\./g,'').replace(',','.');
            if (parseFloat(rawValue) === 0) {
                el.value = '';
            } else {
                el.value = rawValue;
            }
        });

        // Format saat load
        let v=parseFloat(el.value.replace(/\./g,'').replace(',','.'))||0;
        el.value=v.toLocaleString('id-ID',{minimumFractionDigits:2, maximumFractionDigits: 2});
    }

    setupCurrency('est_cost');
    setupCurrency('cost_actual');
    
    document.getElementById('qty').addEventListener('input', function() {
        calculateCosts();
        calculateDeviation();
    });
    
    document.getElementById('qty_received').addEventListener('input', calculateDeviation);
    document.getElementById('est_cost').addEventListener('input', calculateCosts);
    
    document.getElementById('device').addEventListener('change',function(){
        document.getElementById('description').value = ''; 
        let partSelect = document.getElementById('part_name');
        partSelect.innerHTML = '<option value="">Loading...</option>';
        
        // Asumsi file ini ada dan berfungsi
        fetch('get_part_by_device.php?device='+encodeURIComponent(this.value))
            .then(r=>r.text())
            .then(html=> {
                partSelect.innerHTML = html;
            });
    });

    document.getElementById('part_name').addEventListener('change', function() {
        try {
            var selectedOption = this.options[this.selectedIndex];
            var desc = selectedOption.getAttribute('data-desc') || ''; 
            document.getElementById('description').value = desc;
        } catch(e) {
            document.getElementById('description').value = ''; 
        }
    });
})();
</script>

</body>
</html>