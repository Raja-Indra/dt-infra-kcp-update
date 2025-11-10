<?php
include '../db.php';
include '../includes/session.php';

$error = '';
$success = '';

// Insert baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jde = trim($_POST['jde']);
    $employee_name = $_POST['employee_name'];
    $user_login = $_POST['user_login'];
    $email_address = $_POST['email_address'];
    $departments = $_POST['departments'];
    $position = $_POST['position'];
    $mail_type = $_POST['mail_type'];
    $status = $_POST['status'];

    // Cek JDE dengan prepared statement
    $stmt_check = $conn->prepare("SELECT id FROM email WHERE jde = ? LIMIT 1");
    $stmt_check->bind_param("s", $jde);
    $stmt_check->execute();
    $check = $stmt_check->get_result();

    if ($check->num_rows > 0) {
        $error = "JDE '$jde' sudah terdaftar.";
    } else {
        $request_form = null;
        if (!empty($_FILES['request_form']['name'])) {
            $ext = pathinfo($_FILES['request_form']['name'], PATHINFO_EXTENSION);
            $request_form = uniqid('form_') . '.' . $ext;
            move_uploaded_file($_FILES['request_form']['tmp_name'], "../uploads/" . $request_form);
        }

        $stmt = $conn->prepare("INSERT INTO email (jde, employee_name, user_login, email_address, departments, position, mail_type, status, request_form) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $jde, $employee_name, $user_login, $email_address, $departments, $position, $mail_type, $status, $request_form);
        $stmt->execute();
        header("Location: email.php?added=1");
        exit;
    }
}

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Email Entry</title>
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
                    <h2 class="h5 mb-0">Add Email Entry</h2>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">JDE</label>
                                <input type="text" name="jde" id="jde" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Name</label>
                                <input type="text" name="employee_name" id="employee_name" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">User Login</label>
                                <input type="text" name="user_login" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email_address" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Departments</label>
                                <input type="text" name="departments" id="departments" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input type="text" name="position" id="position" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Mail Type</label>
                                <select name="mail_type" class="form-select form-select-sm" required>
                                    <option value="">-- Select --</option>
                                    <option>Exchange Online</option>
                                    <option>Office 365</option>
                                    <option>On Premise</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="">-- Select --</option>
                                    <option>Active</option>
                                    <option>Remove</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Request Form</label>
                            <input type="file" name="request_form" class="form-control form-control-sm">
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
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
// Skrip AJAX Anda untuk get_employee.php
document.getElementById('jde').addEventListener('change', function () {
    var jde = this.value;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_employee.php?jde=' + jde, true);
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
        }
    };
    xhr.send();
});
</script>

</body>
</html>