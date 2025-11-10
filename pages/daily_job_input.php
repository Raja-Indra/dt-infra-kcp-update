<?php
include '../db.php';
include '../includes/session.php';

$issues = mysqli_query($conn, "SELECT * FROM issues ORDER BY issue_name");
$devices = mysqli_query($conn, "SELECT DISTINCT device FROM parts ORDER BY device");

function generateTicketNumber($conn) {
    // UBAH: Menggunakan prepared statement untuk COUNT
    $prefix = "DTKCP-" . date("ymd") . "-";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM daily_job WHERE no_ticket LIKE ?");
    $search_prefix = $prefix . "%";
    $stmt->bind_param("s", $search_prefix);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    $number = str_pad($row['total'] + 1, 3, '0', STR_PAD_LEFT);
    return $prefix . $number;
}

$ticket_no = generateTicketNumber($conn);

include '../nav.php';
$user_department = $_SESSION['department'] ?? '';
$is_dt_user = ($user_department === 'Digital Transformation');


?>
<!DOCTYPE html>
<html>
<head>
<title>Daily Job Entry</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: linear-gradient(to bottom, #FFE0C0, #FF6600);
        min-height: 100vh;
        font-size: 13px;
    }
    .form-label {
        font-weight: 500;
    }
    /* Style untuk dropdown kustom sub-issue */
    #sub_issue_list {
        max-height: 300px;
        overflow-y: auto;
        width: 100%; /* Sesuaikan lebar dropdown */
    }
    #sub_issue_list .dropdown-item {
        padding: .25rem 1rem;
        cursor: pointer;
    }
    #sub_issue_list .form-check-label {
        cursor: pointer;
    }
    #sub_issue_selected {
        min-height: 31px; /* Samakan dengan form-control-sm */
    }
</style>
</head>
<body>
<!-- var_dump(($_SESSION)['jde']); -->
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">DAILY JOB ENTRY</h2>
                    <?=$_SESSION['username'];?>
                </div>
                <div class="card-body">
                    <form method="POST" action="daily_job_process.php">
                        <div class="row g-3">
                            
                            <div class="col-md-6">
                                <h5 class="h6 text-secondary">Issue Details</h5>
                                <hr class="mt-1">
                                <div class="mb-3">
                                    <label for="no_ticket" class="form-label">No Ticket</label>
                                    <input type="text" id="no_ticket" class="form-control form-control-sm" value="<?= $ticket_no ?>" disabled>
                                    <input type="hidden" name="no_ticket" value="<?= $ticket_no ?>">
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="issues" class="form-label">Issue</label>
                                        <select name="issues" id="issues" class="form-select form-select-sm" required>
                                            <option value="">Select</option>
                                            <?php mysqli_data_seek($issues, 0); while ($i = mysqli_fetch_assoc($issues)): ?>
                                                <option value="<?= $i['id'] ?>"><?= $i['issue_name'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="sub_issue_selected" class="form-label">Sub Issue</label>
                                        <div class="dropdown" id="sub_issue_dropdown">
                                            <button class="btn btn-light border btn-sm dropdown-toggle w-100 text-start" type="button" id="sub_issue_selected" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                                Select Sub Issue
                                            </button>
                                            <ul class="dropdown-menu" id="sub_issue_list" aria-labelledby="sub_issue_selected">
                                                <li><span class="dropdown-item-text text-muted">Select Issue First</span></li>
                                            </ul>
                                            <input type="hidden" name="sub_issues" id="sub_issues_input">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control form-control-sm" rows="3" required></textarea>
                                </div>

                                <h5 class="h6 text-secondary mt-4">Reporter Info</h5>
                                <hr class="mt-1">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="report_by_jde" class="form-label">Report By JDE</label>
                                        <input type="text" name="report_by_jde" id="report_by_jde" class="form-control form-control-sm" required autocomplete="off">
                                        <div class="position-relative">
                                            <div class="list-group position-absolute w-100" id="jde_results" style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                                                </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="report_by_name" class="form-label">Report By Name</label>
                                        <input type="text" name="report_by_name" id="report_by_name" class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="report_by_department" class="form-label">Department</label>
                                    <input type="text" name="report_by_department" id="report_by_department" class="form-control form-control-sm" readonly>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="date_report" class="form-label">Date Report</label>
                                        <input type="date" name="date_report" id="date_report" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="time_report" class="form-label">Time Report</label>
                                        <input type="time" name="time_report" id="time_report" class="form-control form-control-sm" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="h6 text-secondary">Action Details</h5>
                                <hr class="mt-1">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="action_by_jde" class="form-label">Action By JDE</label>
                                        <input type="text" name="action_by_jde" id="action_by_jde" class="form-control form-control-sm" autocomplete="off">
                                        <div class="position-relative">
                                            <div class="list-group position-absolute w-100" id="action_jde_results" style="z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="action_by_name" class="form-label">Action By Name</label>
                                        <input type="text" name="action_by_name" id="action_by_name" class="form-control form-control-sm" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select form-select-sm">
                                        <option value="Open">Open</option>
                                        <option value="Progress">Progress</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Close">Close</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="date_action" class="form-label">Date Action</label>
                                        <input type="date" name="date_action" id="date_action" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="time_action" class="form-label">Time Action</label>
                                        <input type="time" name="time_action" id="time_action" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date & Time Close</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" name="date_close" id="date_close" class="form-control" disabled>
                                        <input type="time" name="time_close" id="time_close" class="form-control" disabled>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="resolution" class="form-label">Resolution</label>
                                    <textarea name="resolution" id="resolution" class="form-control form-control-sm" rows="3" required></textarea>
                                </div>

                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="use_part" name="use_part">
                                    <label class="form-check-label" for="use_part">Use Part</label>
                                </div>
                            </div>

                            <div style="display:none;" id="parts_table_wrapper" class="mt-4">
                                <h5 class="h6 text-secondary">Parts Used</h5>
                                <hr class="mt-1">
                                <div class="table-responsive">
                                    <table id="parts_table" class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Device</th>
                                                <th>Part Name</th>
                                                <th>Qty</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <select name="device[]" class="form-select form-select-sm device-select">
                                                        <option value="">Select</option>
                                                        <?php
                                                        mysqli_data_seek($devices, 0); // Reset pointer
                                                        while ($d = mysqli_fetch_assoc($devices)) {
                                                            echo "<option value='" . htmlspecialchars($d['device']) . "'>" . htmlspecialchars($d['device']) . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="part_name[]" class="form-select form-select-sm part-name-select">
                                                        <option value="">Select Device First</option>
                                                    </select>
                                                </td>
                                                <td><input type="number" name="qty[]" class="form-control form-control-sm" min="1" value="1"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">❌</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" id="add_row" class="btn btn-success btn-sm mt-2">+ Add Part</button>
                            </div>



                        <div class="mt-4 border-top pt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="daily_job.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// UBAH: Script disesuaikan dengan Bootstrap 5
document.addEventListener('DOMContentLoaded', function() {
    var usePartCheckbox = document.getElementById('use_part');
    var partTableWrapper = document.getElementById('parts_table_wrapper');
    partTableWrapper.style.display = usePartCheckbox.checked ? 'block' : 'none';
    usePartCheckbox.addEventListener('change', function() {
        partTableWrapper.style.display = this.checked ? 'block' : 'none';
    });

    // Enable/Disable Date Close & Time Close
    var statusSelect = document.getElementById('status');
    var dateClose = document.getElementById('date_close');
    var timeClose = document.getElementById('time_close');
    function toggleCloseFields() {
        if (statusSelect.value === 'Close') {
            dateClose.disabled = false;
            timeClose.disabled = false;
        } else {
            dateClose.disabled = true;
            timeClose.disabled = true;
            dateClose.value = '';
            timeClose.value = '';
        }
    }
    toggleCloseFields();
    statusSelect.addEventListener('change', toggleCloseFields);

    // Add/Remove Part Row (DYNAMIC)
    document.getElementById('add_row').onclick = function() {
        var tbody = document.querySelector('#parts_table tbody');
        var row = tbody.rows[0].cloneNode(true);
        row.querySelectorAll('select, input').forEach(function(el) {
            if (el.type === 'number') el.value = '1';
            else el.selectedIndex = 0;
        });
        tbody.appendChild(row);
    };
    document.querySelector('#parts_table').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            var tr = e.target.closest('tr');
            var tbody = tr.parentNode;
            if (tbody.rows.length > 1) tr.remove();
        }
    });

    // Dependent Dropdown PARTS
    function fetchParts(deviceValue, partNameSelect) {
        partNameSelect.innerHTML = '<option value="">Loading...</option>';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_parts_by_device.php?device=' + encodeURIComponent(deviceValue), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                partNameSelect.innerHTML = xhr.responseText;
            } else {
                partNameSelect.innerHTML = '<option value="">Select Device First</option>';
            }
        };
        xhr.send();
    }
    document.getElementById('parts_table').addEventListener('change', function(e) {
        if (e.target.classList.contains('device-select')) {
            var partNameSelect = e.target.closest('tr').querySelector('.part-name-select');
            fetchParts(e.target.value, partNameSelect);
        }
    });

    // === SUB ISSUE MULTI DROPDOWN (Bootstrap 5) ===
    let subIssueData = [];
    let subIssueSelected = document.getElementById('sub_issue_selected');
    let subIssueList = document.getElementById('sub_issue_list');
    let subIssuesInput = document.getElementById('sub_issues_input');

    document.getElementById('issues').addEventListener('change', function() {
        var issueId = this.value;
        subIssueSelected.textContent = 'Select Sub Issue';
        subIssuesInput.value = '';
        subIssueList.innerHTML = '<li><span class="dropdown-item-text text-muted">Loading...</span></li>';

        if (issueId) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_sub_issues_checkbox.php?issue_id=' + encodeURIComponent(issueId), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        subIssueData = JSON.parse(xhr.responseText);
                        renderSubIssues();
                    } catch(e) {
                         subIssueList.innerHTML = '<li><span class="dropdown-item-text text-danger">Parsing Error</span></li>';
                    }
                } else {
                    subIssueList.innerHTML = '<li><span class="dropdown-item-text text-danger">Failed to Load</span></li>';
                }
            };
            xhr.send();
        } else {
            subIssueList.innerHTML = '<li><span class="dropdown-item-text text-muted">Select Issue First</span></li>';
        }
    });

    function renderSubIssues() {
        if (!subIssueData.length) {
            subIssueList.innerHTML = '<li><span class="dropdown-item-text text-muted">No Sub Issue Found</span></li>';
            return;
        }
        let html = '';
        subIssueData.forEach(function(item){
            html += `
                <li class="dropdown-item">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${item.id}" id="subi_${item.id}" onchange="updateSubIssuesSelected()">
                        <label class="form-check-label" for="subi_${item.id}">${item.name}</label>
                    </div>
                </li>`;
        });
        subIssueList.innerHTML = html;
    }
    
    // Dibuat global agar 'onchange' di HTML bisa memanggilnya
    window.updateSubIssuesSelected = function(){
        let checked = Array.from(subIssueList.querySelectorAll('input[type=checkbox]:checked'));
        let names = checked.map(cb => cb.parentNode.querySelector('label').innerText);
        let ids = checked.map(cb => cb.value);
        subIssuesInput.value = ids.join(',');
        subIssueSelected.textContent = names.length ? names.join(', ') : 'Select Sub Issue';
    };

    // === AutoCOMPLETE Search by JDE or Name ===
    // Fungsi ini menggantikan setupAutoFill
    function setupJdeAutocomplete(inputId, resultsId, nameId, deptId = null) {
        var jdeInput = document.getElementById(inputId);
        var resultsDiv = document.getElementById(resultsId);
        var nameInput = document.getElementById(nameId);
        var deptInput = deptId ? document.getElementById(deptId) : null;
        var searchTimeout;

        jdeInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            var term = jdeInput.value.trim();

            if (term.length < 2) { // Jangan search jika terlalu pendek
                resultsDiv.innerHTML = '';
                return;
            }

            // Debounce - tunggu 300ms setelah user berhenti mengetik
            searchTimeout = setTimeout(function() {
                var xhr = new XMLHttpRequest();
                // Kita akan panggil file baru: search_employee.php
                xhr.open('GET', 'search_employee.php?term=' + encodeURIComponent(term), true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var users = JSON.parse(xhr.responseText);
                            var html = '';
                            if (users.length > 0) {
                                users.forEach(function(user) {
                                    // Gunakan data- attribute untuk menyimpan data
                                    html += `<a href="#" class="list-group-item list-group-item-action list-group-item-light p-2" 
                                                data-jde="${user.jde}" 
                                                data-name="${user.name}" 
                                                data-dept="${user.department || ''}">
                                                <strong>${user.name}</strong> (${user.jde})
                                             </a>`;
                                });
                            } else {
                                html = '<span class="list-group-item list-group-item-light p-2 text-muted">No results found</span>';
                            }
                            resultsDiv.innerHTML = html;
                        } catch(e) {
                            resultsDiv.innerHTML = '<span class="list-group-item list-group-item-danger p-2">Error</span>';
                        }
                    }
                };
                xhr.send();
            }, 300);
        });

        // Handle click on result item
        resultsDiv.addEventListener('click', function(e) {
            e.preventDefault();
            var target = e.target.closest('a'); // Cari elemen <a> yang diklik
            if (target) {
                // Ambil data dari data- attributes dan isi form
                jdeInput.value = target.getAttribute('data-jde');
                nameInput.value = target.getAttribute('data-name');
                if (deptInput) {
                    deptInput.value = target.getAttribute('data-dept');
                }
                resultsDiv.innerHTML = ''; // Kosongkan hasil
            }
        });

        // Sembunyikan hasil jika klik di luar input/hasil
        document.addEventListener('click', function(e) {
            if (!jdeInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                resultsDiv.innerHTML = '';
            }
        });

        // Jika user blur (klik keluar) setelah mengisi JDE manual
        // kita tetap jalankan logic autofill (seperti fungsi lama)
        jdeInput.addEventListener('blur', function() {
            // Beri sedikit jeda agar click event di dropdown sempat jalan
            setTimeout(function() {
                if (resultsDiv.innerHTML !== '') { // Jika dropdown masih ada, jangan lakukan apa2
                     return;
                }
                
                // Jika tidak ada JDE, kosongkan field
                var jde = jdeInput.value.trim();
                if (jde.length === 0) {
                     nameInput.value = '';
                     if (deptInput) deptInput.value = '';
                     return;
                }

                // Jika nama sudah terisi (dari autocomplete), jangan fetch ulang
                if (nameInput.value.length > 0) {
                    return;
                }

                // Jika nama kosong, tapi JDE diisi manual, fetch datanya
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_employee.php?jde=' + encodeURIComponent(jde), true);
                xhr.onload = function() {
                     if (xhr.status === 200) {
                         try {
                             var obj = JSON.parse(xhr.responseText);
                             if (nameInput) nameInput.value = obj.name || '';
                             if (deptInput) deptInput.value = obj.department || '';
                         } catch(e) {
                             if (nameInput) nameInput.value = '';
                             if (deptInput) deptInput.value = '';
                         }
                     }
                };
                xhr.send();

            }, 200); // jeda 200ms
        });
    }

    // Panggil fungsi autocomplete BARU
    setupJdeAutocomplete('report_by_jde', 'jde_results', 'report_by_name', 'report_by_department');
    setupJdeAutocomplete('action_by_jde', 'action_jde_results', 'action_by_name');

// ... (kode JS lainnya seperti setupAutoFill JANGAN dimasukkan lagi)
// Pastikan ini ada di atas penutup });
});
</script>
</body>
</html>