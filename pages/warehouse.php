<?php
include '../db.php';
include '../includes/session.php';

// --- UBAH: Logika Pagination ---
$limit = 20; // Jumlah data per halaman
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Halaman saat ini, default 1
$offset = ($page - 1) * $limit; // Offset untuk SQL

// --- Logika Search ---
$search_device = $_GET['device'] ?? '';
$search_part = $_GET['part_name'] ?? '';

$where = [];
$params = [];
$types = '';

if (!empty($search_device)) {
    $where[] = "mr.device LIKE ?";
    $params[] = "%" . $search_device . "%";
    $types .= "s";
}
if (!empty($search_part)) {
    $where[] = "mr.part_name LIKE ?";
    $params[] = "%" . $search_part . "%";
    $types .= "s";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";


// --- UBAH: Query dipecah untuk menghitung total ---
// Query ini hanya menghitung jumlah unik (device, part_name) untuk total halaman
$sql_count_base = "FROM material_request mr $where_sql GROUP BY mr.device, mr.part_name";
$sql_total = "SELECT COUNT(*) as total FROM (SELECT mr.device, mr.part_name $sql_count_base) AS subquery";

$stmt_total = $conn->prepare($sql_total);
if ($where) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);


// --- UBAH: Query utama untuk mengambil data (dengan LIMIT/OFFSET) ---
$sql = "
    SELECT 
        mr.device, 
        mr.part_name, 
        MAX(mr.description) AS description,
        SUM(mr.qty_received) AS total_received,

        (
            SELECT COALESCE(SUM(djp.qty), 0)
            FROM daily_job_parts djp
            WHERE djp.part_name = mr.part_name AND djp.device = mr.device
        ) AS total_used,

        GREATEST(
            COALESCE(MAX(mr.date_received), '0000-00-00'),
            COALESCE((
                SELECT MAX(dj.date_action)
                FROM daily_job_parts djp
                JOIN daily_job dj ON djp.daily_job_id = dj.id
                WHERE djp.part_name = mr.part_name AND djp.device = mr.device
            ), '0000-00-00')
        ) AS last_update

    FROM material_request mr
    $where_sql
    GROUP BY mr.device, mr.part_name
    ORDER BY mr.device, mr.part_name
    LIMIT ? OFFSET ?
";

// Tambahkan param pagination ke param search
$params[] = $limit;
$params[] = $offset;
$types .= "ii"; // Tambah 2 integer (LIMIT, OFFSET)

$stmt_data = $conn->prepare($sql);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$data = $stmt_data->get_result();

include '../nav.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(to bottom, #FFE0C0, #FF6600);
            min-height: 100vh;
            font-size: 13px;
        }
        .table-sm th, .table-sm td {
            padding: 0.4rem;
            white-space: nowrap; 
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
        .pagination {
            justify-content: center;
            margin-top: 15px;
        }
        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #90ca4b; /* Ganti warna active */
            border-color: #90ca4b;
        }
        .page-link {
            color: #90ca4b; /* Ganti warna link */
        }
        .page-link:hover {
            color: #5a8e1d;
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
    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="h5 mb-0">WAREHOUSE</h2>
        </div>
        <div class="card-body">

            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="device" class="form-control form-control-sm" placeholder="Search Device" value="<?= htmlspecialchars($search_device) ?>">
                </div>
                 <div class="col-md-3">
                    <input type="text" name="part_name" class="form-control form-control-sm" placeholder="Search Part Name" value="<?= htmlspecialchars($search_part) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </div>
                <div class="col-auto">
                    <a href="warehouse.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive" style="max-height: calc(100vh - 245px); overflow-y: auto;">
                <table class="table table-bordered table-striped table-hover table-sm">
                    
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Device</th>
                            <th>Part Name</th>
                            <th>Description</th>
                            <th class="text-end">Stock</th>
                            <th>Last Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1; 
                        while($row = mysqli_fetch_assoc($data)) :
                            $stock = ($row['total_received'] ?? 0) - ($row['total_used'] ?? 0);
                            $lastUpdate = ($row['last_update'] === '0000-00-00' || $row['last_update'] === null) ? '-' : $row['last_update'];
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['device']) ?></td>
                            <td><?= htmlspecialchars($row['part_name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td class="text-end"><?= number_format($stock) ?></td>
                            <td><?= htmlspecialchars($lastUpdate) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination pagination-sm">
                    <?php
                    // Logika untuk link pagination (mempertahankan search query)
                    $query_params = $_GET;
                    unset($query_params['page']); // Hapus param 'page' lama
                    $base_url = http_build_query($query_params);
                    $base_url = $base_url ? $base_url . '&' : '';
                    
                    // Tombol Previous
                    $prev_page = $page - 1;
                    echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">';
                    echo '<a class="page-link" href="?' . $base_url . 'page=' . $prev_page . '">Previous</a>';
                    echo '</li>';

                    // Nomor Halaman (Anda bisa membuat ini lebih kompleks jika halaman terlalu banyak)
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                        echo '<a class="page-link" href="?' . $base_url . 'page=' . $i . '">' . $i . '</a>';
                        echo '</li>';
                    }

                    // Tombol Next
                    $next_page = $page + 1;
                    echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '">';
                    echo '<a class="page-link" href="?' . $base_url . 'page=' . $next_page . '">Next</a>';
                    echo '</li>';
                    ?>
                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</html>