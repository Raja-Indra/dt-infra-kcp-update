<?php
include '../db.php';
include '../includes/session.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: payment_contract.php');
    exit;
}

// UBAH: Ambil parameter filter/halaman untuk redirect kembali
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

// UBAH: Buat query string untuk link "Cancel" dan "Update"
$return_query = http_build_query([
    'page' => $page,
    'search' => $search
]);


// Tangani POST
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = intval($_POST['id']);
    $isp = $_POST['isp'];
    $no_valuation = $_POST['no_valuation'];
    $no_pr = $_POST['no_pr'];
    $no_po = $_POST['no_po'];
    $periode = $_POST['periode'];
    $process_payment = $_POST['process_payment'];
    $reference_ir = $_POST['reference_ir'];
    $date_receipt = $_POST['date_receipt'] ?: null;
    $status_receipt = $_POST['status_receipt'];

    // Ambil nama file lama
    $stmt_old = $conn->prepare("SELECT invoice,tax,bai FROM payment_contract WHERE id=?");
    $stmt_old->bind_param('i', $id);
    $stmt_old->execute();
    $row = $stmt_old->get_result()->fetch_assoc();
    
    $fields = ['invoice','tax','bai'];
    $uploads = [];
    foreach ($fields as $f) {
        if (!empty($_FILES[$f]['name'])) {
            // delete lama
            if (!empty($row[$f]) && file_exists(__DIR__.'/../uploads/'.$row[$f])) {
                unlink(__DIR__.'/../uploads/'.$row[$f]);
            }
            $ext = pathinfo($_FILES[$f]['name'], PATHINFO_EXTENSION);
            $fn = uniqid($f.'_').'.'.$ext;
            move_uploaded_file($_FILES[$f]['tmp_name'], __DIR__.'/../uploads/'.$fn);
            $uploads[$f] = $fn;
        } else {
            // Jika tidak ada file baru, pertahankan file lama
            $uploads[$f] = $row[$f];
        }
    }

    $upd=$conn->prepare("
        UPDATE payment_contract SET
            isp=?, no_valuation=?, no_pr=?, no_po=?, periode=?, process_payment=?,
            reference_ir=?, date_receipt=?, status_receipt=?, invoice=?, tax=?, bai=?
        WHERE id=?
    ");
    $upd->bind_param('ssssssssssssi',
        $isp,$no_valuation,$no_pr,$no_po,$periode,$process_payment,
        $reference_ir,$date_receipt,$status_receipt,
        $uploads['invoice'],$uploads['tax'],$uploads['bai'],$id
    );
    $upd->execute();
    
    // UBAH: Redirect dengan notifikasi DAN filter
    header('Location: payment_contract.php?updated=1&' . $return_query);
    exit;
}

// Ambil data pakai prepared statement
$stmt = $conn->prepare("SELECT * FROM payment_contract WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    header('Location: payment_contract.php');
    exit;
}

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Payment Contract</title>
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
        .current-file-link {
            font-size: 12px; 
            margin-bottom: 4px; 
            display: block;
            color: #333;
        }
        .current-file-link a {
            color: #0056b3;
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
                    <h2 class="h5 mb-0">Edit Payment Contract</h2>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="isp" class="form-label">ISP</label>
                                <select name="isp" id="isp" class="form-select form-select-sm" required>
                                    <option value="">-- Select ISP --</option>
                                    <option value="Icon Plus" <?= $row['isp']==='Icon Plus'?'selected':'' ?>>Icon Plus</option>
                                    <option value="Khamzanet" <?= $row['isp']==='Khamzanet'?'selected':'' ?>>Khamzanet</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="no_valuation" class="form-label">No Valuation</label>
                                <input type="text" name="no_valuation" id="no_valuation" class="form-control form-control-sm" value="<?= htmlspecialchars($row['no_valuation']) ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="no_pr" class="form-label">No PR</label>
                                <input type="text" name="no_pr" id="no_pr" class="form-control form-control-sm" value="<?= htmlspecialchars($row['no_pr']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="no_po" class="form-label">No PO</label>
                                <input type="text" name="no_po" id="no_po" class="form-control form-control-sm" value="<?= htmlspecialchars($row['no_po']) ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="periode" class="form-label">Periode (YYYY-MM)</label>
                                <input type="text" name="periode" id="periode" class="form-control form-control-sm" value="<?= htmlspecialchars($row['periode']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="process_payment" class="form-label">Process Payment</label>
                                <select name="process_payment" id="process_payment" class="form-select form-select-sm" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach([
                                    'Process Approval BAI','Waiting Approval Valuation','Valuation Approved',
                                    'Waiting Approval PR','PR Approved','Waiting Approval PO','PO Approved',
                                    'Requests AP OK','AP OK','Request Payment','Paid'
                                    ] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $row['process_payment']===$opt?'selected':'' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="reference_ir" class="form-label">Reference IR</label>
                                <input type="text" name="reference_ir" id="reference_ir" class="form-control form-control-sm" value="<?= htmlspecialchars($row['reference_ir']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="date_receipt" class="form-label">Date Receipt</label>
                                <input type="date" name="date_receipt" id="date_receipt" class="form-control form-control-sm" value="<?= $row['date_receipt'] ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status_receipt" class="form-label">Status Receipt</label>
                                <select name="status_receipt" id="status_receipt" class="form-select form-select-sm">
                                    <option value="" <?= $row['status_receipt']===''?'selected':'' ?>>-- Select --</option>
                                    <option value="Done" <?= $row['status_receipt']==='Done'?'selected':'' ?>>Done</option>
                                    <option value="Failed" <?= $row['status_receipt']==='Failed'?'selected':'' ?>>Failed</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="invoice" class="form-label">Invoice File</label>
                                <?php if($row['invoice']): ?>
                                    <span class="current-file-link">Current: <a href="/dt-infra-kcp-update/uploads/<?= $row['invoice'] ?>" target="_blank">View File</a></span>
                                <?php endif; ?>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="invoice" id="invoice" class="form-control">
                                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('invoice').value='';">Clear</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tax" class="form-label">Tax File</label>
                                <?php if($row['tax']): ?>
                                    <span class="current-file-link">Current: <a href="/dt-infra-kcp-update/uploads/<?= $row['tax'] ?>" target="_blank">View File</a></span>
                                <?php endif; ?>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="tax" id="tax" class="form-control">
                                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('tax').value='';">Clear</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="bai" class="form-label">BAI File</label>
                                <?php if($row['bai']): ?>
                                    <span class="current-file-link">Current: <a href="/dt-infra-kcp-update/uploads/<?= $row['bai'] ?>" target="_blank">View File</a></span>
                                <?php endif; ?>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="bai" id="bai" class="form-control">
                                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('bai').value='';">Clear</button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="payment_contract.php?<?= $return_query ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>