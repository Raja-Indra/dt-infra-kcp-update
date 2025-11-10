<?php
include '../db.php';
include '../includes/session.php';

// Tangani POST sebelum output
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $isp = $_POST['isp'];
    $no_valuation = $_POST['no_valuation'];
    $no_pr = $_POST['no_pr'];
    $no_po = $_POST['no_po'];
    $periode = $_POST['periode'];
    $process_payment = $_POST['process_payment'];
    $reference_ir = $_POST['reference_ir'];
    $date_receipt = $_POST['date_receipt'] ?: null; // Izinkan NULL
    $status_receipt = $_POST['status_receipt'];

    // Upload file
    $fields = ['invoice','tax','bai'];
    $uploads = [];
    foreach ($fields as $f) {
        if (!empty($_FILES[$f]['name'])) {
            $ext = pathinfo($_FILES[$f]['name'], PATHINFO_EXTENSION);
            $fn = uniqid($f.'_').'.'.$ext;
            move_uploaded_file($_FILES[$f]['tmp_name'], __DIR__.'/../uploads/'.$fn);
            $uploads[$f] = $fn;
        } else {
            $uploads[$f] = null;
        }
    }

    $stmt = $conn->prepare("
      INSERT INTO payment_contract
      (isp,no_valuation,no_pr,no_po,periode,process_payment,reference_ir,date_receipt,status_receipt,invoice,tax,bai)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param('ssssssssssss',
      $isp,$no_valuation,$no_pr,$no_po,$periode,$process_payment,
      $reference_ir,$date_receipt,$status_receipt,
      $uploads['invoice'],$uploads['tax'],$uploads['bai']
    );
    $stmt->execute();
    
    // UBAH: Redirect dengan notifikasi
    header('Location: payment_contract.php?added=1');
    exit;
}

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Payment Contract</title>
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
                    <h2 class="h5 mb-0">Add Payment Contract</h2>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="isp" class="form-label">ISP</label>
                                <select name="isp" id="isp" class="form-select form-select-sm" required>
                                    <option value="">-- Select ISP --</option>
                                    <option value="Icon Plus">Icon Plus</option>
                                    <option value="Khamzanet">Khamzanet</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="no_valuation" class="form-label">No Valuation</label>
                                <input type="text" name="no_valuation" id="no_valuation" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="no_pr" class="form-label">No PR</label>
                                <input type="text" name="no_pr" id="no_pr" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label for="no_po" class="form-label">No PO</label>
                                <input type="text" name="no_po" id="no_po" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="periode" class="form-label">Periode (YYYY-MM)</label>
                                <input type="text" name="periode" id="periode" class="form-control form-control-sm" placeholder="Contoh: 2025-11" required>
                            </div>
                            <div class="col-md-6">
                                <label for="process_payment" class="form-label">Process Payment</label>
                                <select name="process_payment" id="process_payment" class="form-select form-select-sm" required>
                                    <option value="">-- Select --</option>
                                    <option>Process Approval BAI</option>
                                    <option>Waiting Approval Valuation</option>
                                    <option>Valuation Approved</option>
                                    <option>Waiting Approval PR</option>
                                    <option>PR Approved</option>
                                    <option>Waiting Approval PO</option>
                                    <option>PO Approved</option>
                                    <option>Requests AP OK</option>
                                    <option>AP OK</option>
                                    <option>Request Payment</option>
                                    <option>Paid</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="reference_ir" class="form-label">Reference IR</label>
                                <input type="text" name="reference_ir" id="reference_ir" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label for="date_receipt" class="form-label">Date Receipt</label>
                                <input type="date" name="date_receipt" id="date_receipt" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-3">
                             <div class="col-md-6">
                                <label for="status_receipt" class="form-label">Status Receipt</label>
                                <select name="status_receipt" id="status_receipt" class="form-select form-select-sm">
                                    <option value="">-- Select --</option>
                                    <option>Done</option>
                                    <option>Failed</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="invoice" class="form-label">Invoice File</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="invoice" id="invoice" class="form-control">
                                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('invoice').value='';">Clear</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tax" class="form-label">Tax File</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="tax" id="tax" class="form-control">
                                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('tax').value='';">Clear</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="bai" class="form-label">BAI File</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="bai" id="bai" class="form-control">
                                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('bai').value='';">Clear</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 border-top pt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="payment_contract.php" class="btn btn-outline-secondary">Cancel</a>
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