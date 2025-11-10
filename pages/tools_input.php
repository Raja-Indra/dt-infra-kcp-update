<?php
include '../db.php';
include '../includes/session.php';
// nav.php dipindah ke bawah setelah semua logika PHP selesai

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = [];

// UBAH: Ambil parameter filter/halaman untuk redirect kembali
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$merk_filter = isset($_GET['merk']) ? $_GET['merk'] : '';

if ($id) {
    // UBAH: Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("SELECT * FROM tools WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    // UBAH: Tambah validasi jika data tidak ditemukan
    if (!$data) {
        header('Location: tools.php');
        exit;
    }
}

// UBAH: Pindah nav.php ke sini
include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $id ? 'Edit' : 'Input' ?> Tools Asset</title>
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
                    <h2 class="h5 mb-0"><?= $id ? 'Edit' : 'Input' ?> Tools Asset</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="tools_process.php">
                        <?php if($id): ?>
                            <input type="hidden" name="id" value="<?= $id ?>">
                        <?php endif; ?>
                        
                        <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                        <input type="hidden" name="merk" value="<?= htmlspecialchars($merk_filter) ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" name="description" id="description" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['description']) ? $data['description'] : '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="merk" class="form-label">Merk</label>
                                <input type="text" name="merk" id="merk" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['merk']) ? $data['merk'] : '') ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="qty" class="form-label">Qty</label>
                                <input type="number" name="qty" id="qty" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($data['qty']) ? $data['qty'] : '1') ?>" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="uom" class="form-label">UoM</label>
                                <select name="uom" id="uom" class="form-select form-select-sm" required>
                                    <option value="">Select</option>
                                    <?php 
                                    $uom_val = isset($data['uom']) ? $data['uom'] : 'EA';
                                    foreach(['EA','SET','LENGHT','ROLL','UNIT'] as $u): ?>
                                        <option value="<?= $u ?>" <?= $uom_val == $u ? 'selected':''?>><?= $u ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="condition" class="form-label">Condition</label>
                                <select name="condition" id="condition" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php $condition_val = isset($data['condition']) ? $data['condition'] : 'Good'; ?>
                                    <option value="Broken" <?= $condition_val == 'Broken' ? 'selected' : ''?>>Broken</option>
                                    <option value="Good" <?= $condition_val == 'Good' ? 'selected' : ''?>>Good</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <?php 
                                    $status_val = isset($data['status']) ? $data['status'] : 'Used';
                                    foreach(['Disposal','Missing','Transfer','Used'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $status_val == $s ? 'selected' : ''?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                                    'merk' => $merk_filter
                                ]);
                            ?>
                            <a href="tools.php?<?= $cancel_query ?>" class="btn btn-outline-secondary">Cancel</a>
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