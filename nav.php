<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'db.php';

$jde = $_SESSION['jde'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE jde = '$jde' LIMIT 1");
$akses = mysqli_fetch_assoc($query);
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
<style>
nav.navbar {
  display:flex;
  align-items:center;
  background:#ff8800;
  font-size: 0.85em;
  white-space:nowrap;
  min-width:1500px;
  box-sizing:border-box;
  padding:5px 12px;
  
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 2000; /* pastikan tinggi agar di atas semua elemen */
  overflow-x: auto;
  overflow-y: visible; /* pastikan dropdown tidak terpotong */
}

nav.navbar .menu-left {
  display:flex;
  align-items:center;
  gap:8px;
}
nav.navbar .greeting {
  font-weight:bold;
  color:white;
  font-size:1em;
  margin-right:6px;
}
nav.navbar .menu-item {
  position: relative;
  display: flex;
  align-items: center;
}
nav.navbar .menu-item > a {
  display:inline-block;
  padding:7px 14px;
  color:white;
  font-weight:bold;
  font-size:1em;
  text-decoration:none;
  background:rgba(0,0,139,0.3);
  border-radius:4px;
  transition:background 0.3s;
}
nav.navbar .menu-item > a:hover,
nav.navbar .menu-item.open > a {
  background:rgba(0,0,139,0.5);
}
nav.navbar .submenu {
  display: none;
  position: absolute;
  top: 105%;
  left: 0;
  min-width: 170px;
  background: #ff8800;
  padding:7px 0;
  z-index: 9999;
  border-radius: 7px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
nav.navbar .menu-item.open .submenu {
  display: block;
}
nav.navbar .submenu a {
  display: block;
  font-size: 1em;
  font-weight: bold;
  color: white;
  background: rgba(0,0,139,0.30);
  padding: 7px 16px;
  border-radius: 4px;
  margin: 4px 10px;
  transition: background 0.2s;
  text-decoration: none;
}
nav.navbar .submenu a:hover {
  background: rgba(0,0,139,0.55);
  color: #fff;
}
nav.navbar .logout {
  margin-left:auto;
}
nav.navbar .logout a {
  display:inline-block;
  padding:7px 16px;
  color:white;
  font-weight:bold;
  text-decoration:none;
  background:rgba(0,0,1,0.3);
  border-radius:4px;
  transition:background 0.3s;
  text-transform:uppercase;
  font-size:1em;
  letter-spacing:1px;
}
nav.navbar .logout a:hover {
  background:rgba(128,128,128,0.5);
}

.footer {
  position:fixed;
  bottom:0;
  left:0;
  width:100%;
  text-align:center;
  padding:10px;
  font-size:0.8em;
  color:#FFFFFF;
}
body {
  padding-top: 38px;
  padding-bottom: 40px;
  box-sizing: border-box;
  overflow: visible; /* tambahkan ini agar dropdown tidak terpotong */
}


nav.navbar {
  display:flex;
  align-items:center;
  background:#ff8800;
  font-size: 0.85em;
  white-space:nowrap;
  min-width:1500px; /* Tetap ada untuk konten wide-screen Anda */
  box-sizing:border-box;
  padding:5px 12px;
  
  /* --- UBAHAN DI SINI --- */
  position: fixed; /* Membuatnya menempel */
  top: 0;          /* Menempel di bagian atas */
  left: 0;         /* Menempel di bagian kiri */
  right: 0;        /* Menempel di bagian kanan (membuatnya 100% lebar) */
  z-index: 1000;   /* Memastikan navbar di atas konten lain */
  overflow-x: auto;/* Membuat item di dalam nav bisa di-scroll jika layar kecil */
}
nav.navbar .menu-left {
  display:flex;
  align-items:center;
  gap:8px;
}
nav.navbar .greeting {
  font-weight:bold;
  color:white;
  font-size:1em;
  margin-right:6px;
}
nav.navbar .menu-item {
  position: relative;
  display: flex;
  align-items: center;
  overflow: visible; /* biar dropdown-nya gak ke-clip */
}
nav.navbar .menu-item > a {
  display:inline-block;
  padding:7px 14px;
  color:white;
  font-weight:bold;
  font-size:1em;
  text-decoration:none;
  background:rgba(0,0,139,0.3);
  border-radius:4px;
  transition:background 0.3s;
}
nav.navbar .menu-item > a:hover,
nav.navbar .menu-item.open > a {
  background:rgba(0,0,139,0.5);
}
nav.navbar .submenu {
  display: none;
  position: absolute;
  top: 105%;
  left: 0;
  min-width: 170px;
  background: #ff8800;
  padding:7px 0;
  z-index: 99999; /* pastikan dropdown di atas segalanya */
  border-radius: 7px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

nav.navbar .menu-item.open .submenu {
  display: block;
}
nav.navbar .submenu a {
  display: block;
  font-size: 1em;
  font-weight: bold;
  color: white;
  background: rgba(0,0,139,0.30);
  padding: 7px 16px;
  border-radius: 4px;
  margin: 4px 10px;
  transition: background 0.2s;
  text-decoration: none;
}
nav.navbar .submenu a:hover {
  background: rgba(0,0,139,0.55);
  color: #fff;
}
nav.navbar .logout {
  margin-left:auto;
}
nav.navbar .logout a {
  display:inline-block;
  padding:7px 16px;
  color:white;
  font-weight:bold;
  text-decoration:none;
  background:rgba(0,0,1,0.3);
  border-radius:4px;
  transition:background 0.3s;
  text-transform:uppercase;
  font-size:1em;
  letter-spacing:1px;
}
nav.navbar .logout a:hover {
  background:rgba(128,128,128,0.5);
}

.footer {
  position:fixed;
  bottom:0;
  left:0;
  width:100%;
  text-align:center;
  padding:10px;
  font-size:0.8em;
  color:#FFFFFF;
  
  /* --- UBAHAN DI SINI --- */
  background: #ff8800; /* Tambah background agar tidak transparan */
  z-index: 999;       /* Pastikan footer di atas konten tapi di bawah navbar */
}
/* Pastikan navbar fixed tapi tetap bisa munculkan dropdown */
nav.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  overflow: visible !important; /* pastikan dropdown gak terpotong */
}

/* Menu-item & submenu */
nav.navbar .menu-item {
  position: static !important; /* biar absolute submenu mengacu ke viewport, bukan navbar */
}

/* Buat dropdown muncul di posisi absolut terhadap layar */
nav.navbar .submenu {
  display: none;
  position: fixed !important; /* ubah dari absolute ke fixed */
  top: 45px; /* tinggi navbar + sedikit jarak */
  background: #ff8800;
  min-width: 180px;
  padding: 7px 0;
  border-radius: 7px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
  z-index: 10000; /* di atas segalanya */
}

/* Saat open, tampilkan dropdown */
nav.navbar .menu-item.open .submenu {
  display: block;
}

/* Link di dalam dropdown */
nav.navbar .submenu a {
  display: block;
  font-size: 1em;
  font-weight: bold;
  color: white;
  background: rgba(0,0,139,0.3);
  padding: 7px 16px;
  border-radius: 4px;
  margin: 4px 10px;
  text-decoration: none;
}
nav.navbar .submenu a:hover {
  background: rgba(0,0,139,0.55);
}

</style>
</head>
<body>

<nav class="navbar">
  <div class="menu-left">
    <span class="greeting">Hello <?= htmlspecialchars($username) ?></span>
    <div class="menu-item"><a href="/dt-infra-kcp/index.php">DASHBOARD</a></div>
    <?php if ($akses['daily_job'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/daily_job.php">DAILY JOB</a></div><?php endif; ?>
    <?php if ($akses['detail_job'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/detail_job.php">DETAIL JOB</a></div><?php endif; ?>
    <?php if ($akses['departments'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/departments.php">DEPARTMENTS</a></div><?php endif; ?>
    <?php if ($akses['employee'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/employee.php">EMPLOYEE</a></div><?php endif; ?>
    <?php if ($akses['user'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/asset_merk_type.php">ASSET MERK</a></div><?php endif; ?>

    <?php if ($akses['assets'] === 'Yes'): ?>
    <div class="menu-item">
      <a href="#">ASSETS ▾</a>
      <div class="submenu">
        <a href="/dt-infra-kcp/pages/radio.php">Radio</a>
        <a href="/dt-infra-kcp/pages/computer.php">Computer</a>
        <a href="/dt-infra-kcp/pages/network.php">Network</a>
        <a href="/dt-infra-kcp/pages/attendance.php">Attendance</a>
        <a href="/dt-infra-kcp/pages/cctv.php">CCTV</a>
        <a href="/dt-infra-kcp/pages/server.php">Server</a>
        <a href="/dt-infra-kcp/pages/printer.php">Printer</a>
        <a href="/dt-infra-kcp/pages/tools.php">Tools</a>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($akses['user'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/warehouse.php">WAREHOUSE</a></div><?php endif; ?>
    <?php if ($akses['user'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/inventory.php">INVENTORY</a></div><?php endif; ?>

    <?php if ($akses['purchasing'] === 'Yes'): ?>
    <div class="menu-item">
      <a href="#">PURCHASING ▾</a>
      <div class="submenu">
        <a href="/dt-infra-kcp/pages/material_request.php">Material Request</a>
        <a href="/dt-infra-kcp/pages/payment_contract.php">Payment Contract</a>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($akses['data_account'] === 'Yes'): ?>
    <div class="menu-item">
      <a href="#">DATA ACCOUNT ▾</a>
      <div class="submenu">
        <a href="/dt-infra-kcp/pages/email.php">Email</a>
        <a href="/dt-infra-kcp/pages/hotspot.php">Hotspot</a>
        <a href="/dt-infra-kcp/pages/ip_address.php">IP Address</a>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($akses['user'] === 'Yes'): ?><div class="menu-item"><a href="/dt-infra-kcp/pages/user.php">USER</a></div><?php endif; ?>
  </div>
  <div class="logout">
      <a href="/dt-infra-kcp/logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')">LOGOUT</a>
  </div>
</nav>
<div class="footer">
    © 2025, IT Infrastructure - PTDH KCP. All Right Reserved
</div>

<script>
document.querySelectorAll('.menu-item > a').forEach(link => {
  link.addEventListener('click', function (e) {
    const parent = link.parentElement;
    const submenu = link.nextElementSibling;

    if (submenu && submenu.classList.contains('submenu')) {
      e.preventDefault();

      // Tutup semua submenu lain
      document.querySelectorAll('.menu-item').forEach(mi => {
        if (mi !== parent) {
          mi.classList.remove('open');
          const sm = mi.querySelector('.submenu');
          if (sm) sm.style.display = 'none';
        }
      });

      // Toggle submenu
      const isOpen = parent.classList.toggle('open');

      if (isOpen) {
        const rect = link.getBoundingClientRect();

        // Hitung posisi agar muncul tepat di bawah tombol
        submenu.style.display = 'block';
        submenu.style.position = 'fixed';
        submenu.style.top = rect.bottom + 'px';
        submenu.style.left = rect.left + 'px';
        submenu.style.zIndex = '10000';
      } else {
        submenu.style.display = 'none';
      }
    }
  });
});

// Tutup dropdown kalau klik di luar area menu
document.addEventListener('click', function (e) {
  if (!e.target.closest('.menu-item')) {
    document.querySelectorAll('.menu-item').forEach(mi => {
      mi.classList.remove('open');
      const sm = mi.querySelector('.submenu');
      if (sm) sm.style.display = 'none';
    });
  }
});
</script>


</body>
</html>
