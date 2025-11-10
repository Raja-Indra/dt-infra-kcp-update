<?php
include '../db.php';
include '../includes/session.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: email.php");
    exit;
}

// --- UBAH: Gunakan Prepared Statement untuk keamanan ---
$stmt_get = $conn->prepare("SELECT * FROM email WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result = $stmt_get->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    // Redirect jika data tidak ditemukan
    header("Location: email.php?error=notfound");
    exit;
}

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jde = $_POST['jde'];
    $employee_name = $_POST['employee_name'];
    $user_login = $_POST['user_login'];
    $email_address = $_POST['email_address'];
    $departments = $_POST['departments'];
    $position = $_POST['position'];
    $mail_type = $_POST['mail_type'];
    $status = $_POST['status'];

    $request_form = $data['request_form'];
    if (!empty($_FILES['request_form']['name'])) {
        // Hapus file lama jika ada
        if ($request_form && file_exists("../uploads/" . $request_form)) {
            unlink("../uploads/" . $request_form);
        }
        // Upload file baru
        $ext = pathinfo($_FILES['request_form']['name'], PATHINFO_EXTENSION);
        $request_form = uniqid('form_') . '.' . $ext;
        move_uploaded_file($_FILES['request_form']['tmp_name'], "../uploads/" . $request_form);
    }

    $stmt = $conn->prepare("UPDATE email SET jde=?, employee_name=?, user_login=?, email_address=?, departments=?, position=?, mail_type=?, status=?, request_form=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $jde, $employee_name, $user_login, $email_address, $departments, $position, $mail_type, $status, $request_form, $id);
    $stmt->execute();

    // --- UBAH: Redirect dengan notifikasi sukses ---
    header("Location: email.php?updated=1");
    exit;
}

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Email Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(to bottom, #FFE0C0, #FF6600);
            min-height: 100vh;
            font-size: 13px;
        }
        /* Style untuk tombol cancel */
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

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-9"> <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Edit Email Data</h2>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jde" class="form-label">JDE</label>
                                    <input type="text" name="jde" id="jde" value="<?= htmlspecialchars($data['jde']) ?>" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label for="employee_name" class="form-label">Employee Name</label>
                                    <input type="text" name="employee_name" id="employee_name" value="<?= htmlspecialchars($data['employee_name']) ?>" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="user_login" class="form-label">User Login</label>
                                    <input type="text" name="user_login" id="user_login" value="<?= htmlspecialchars($data['user_login']) ?>" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email_address" class="form-label">Email Address</label>
                                    <input type="email" name="email_address" id="email_address" value="<?= htmlspecialchars($data['email_address']) ?>" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="departments" class="form-label">Departments</label>
                                    <input type="text" name="departments" id="departments" value="<?= htmlspecialchars($data['departments']) ?>" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <input type="text" name="position" id="position" value="<?= htmlspecialchars($data['position']) ?>" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="mail_type" class="form-label">Mail Type</label>
                                    <select name="mail_type" id="mail_type" class="form-select form-select-sm" required>
                                        <option <?= $data['mail_type']=='Exchange Online' ? 'selected' : '' ?>>Exchange Online</option>
                                        <option <?= $data['mail_type']=='Office 365' ? 'selected' : '' ?>>Office 365</option>
                                        <option <?= $data['mail_type']=='On Premise' ? 'selected' : '' ?>>On Premise</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select form-select-sm" required>
                                        <option <?= $data['status']=='Active' ? 'selected' : '' ?>>Active</option>
                                        <option <?= $data['status']=='Remove' ? 'selected' : '' ?>>Remove</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="mb-3">
                            <label for="request_form" class="form-label">Request Form</label>
                            <?php if ($data['request_form']) : ?>
                                <div class="mb-1">
                                    <a href="../uploads/<?= htmlspecialchars($data['request_form']) ?>" target="_blank" class="btn btn-info btn-sm">View Existing File</a>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="request_form" id="request_form" class="form-control form-control-sm">
                            <small class="form-text text-muted">Upload a new file to replace the existing one.</small>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">Update</button>
                            <a href="email.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('jde').addEventListener('change', function () {
    var jde = this.value;
    if (!jde) {
        document.getElementById('employee_name').value = '';
        document.getElementById('departments').value = '';
        document.getElementById('position').value = '';
        return;
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_employee.php?jde=' + encodeURIComponent(jde), true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                document.getElementById('employee_name').value = data.name || '';
                document.getElementById('departments').value = data.department || '';
                document.getElementById('position').value = data.position || '';
            } catch (e) {
                console.error('Parsing error: ', e);
                document.getElementById('employee_name').value = '';
                document.getElementById('departments').value = '';
                document.getElementById('position').value = '';
            }
        } else {
            // JDE tidak ditemukan atau error server
            document.getElementById('employee_name').value = '';
            document.getElementById('departments').value = '';
            document.getElementById('position').value = '';
        }
    };
    xhr.send();
});
</script>
</body>
</html>