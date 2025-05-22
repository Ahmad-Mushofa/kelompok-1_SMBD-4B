<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Rumah Sakit</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f8ff;
    }

   .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100%;
    background: linear-gradient(180deg, #d6f0fa, #a3d8f4); /* biru muda ke biru pastel */
    color: #003366; /* biru gelap untuk teks */
    padding: 20px;
    box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
    z-index: 1000;
    transition: all 0.3s ease;
  }


    .sidebar.collapsed {
      width: 60px;
      padding: 10px;
      background: none;
      box-shadow: none;
      overflow: visible;
    }

    .sidebar-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .sidebar h1 {
      font-size: 20px;
      margin: 0;
    }

    .sidebar.collapsed h1,
    .sidebar.collapsed .menu {
      display: none;
    }

    .toggle-btn {
      background: none;
      border: none;
      color: #003366;
      font-size: 22px;
      cursor: pointer;
      padding: 5px;
    }

    .main-content {
      margin-left: 250px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }

    .main-content.expanded {
      margin-left: 60px;
    }

    .menu {
      margin-top: 20px;
    }

    .menu a {
      display: flex;
      align-items: center;
      padding: 12px 10px;
      text-decoration: none;
      color: #003366;
      font-size: 16px;
      font-weight: 500;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .menu a i {
      margin-right: 12px;
      font-size: 18px;
      width: 24px;
      text-align: center;
    }

    .menu a:hover {
      background-color: #cce5ff;
      color: #0056b3;
    }
  </style>
</head>
<body>



<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h1>Rumah Sakit</h1>
    <button class="toggle-btn" id="toggle-btn">
      <i class="fas fa-bars"></i>
    </button>
  </div>
  <div class="menu">
    <?php if (isset($_SESSION['admin_logged_in'])): ?>
      <a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <a href="../admin/pasien.php"><i class="fas fa-user-injured"></i><span>Pasien</span></a>
      <a href="../admin/dokter.php"><i class="fas fa-user-md"></i><span>Dokter</span></a>
      <a href="../admin/rawat_inap.php"><i class="fas fa-procedures"></i><span>Rawat Inap</span></a>
      <a href="../admin/transaksi.php"><i class="fas fa-file-invoice-dollar"></i><span>Transaksi</span></a>
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    <?php elseif (isset($_SESSION['dokter_logged_in'])): ?>
      <a href="../menudokter/dashboard_dokter.php"><i class="fas fa-home"></i><span>Beranda</span></a>
      <a href="../menudokter/pasien.php"><i class="fas fa-user-injured"></i><span>Pasien</span></a>
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    <?php else: ?>
      <a href="../login.php"><i class="fas fa-user"></i><span>Login</span></a>
    <?php endif; ?>
  </div>
</div>

<div class="main-content" id="main-content">
  <h2 class="text-primary">Selamat Datang di Sistem Manajemen Rumah Sakit</h2>
  <p>Silakan gunakan menu di samping untuk navigasi.</p>
</div>

<script>
  const toggleBtn = document.getElementById('toggle-btn');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('main-content');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
  });

  document.addEventListener('DOMContentLoaded', () => {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
      sidebar.classList.add('collapsed');
      mainContent.classList.add('expanded');
    }
  });
</script>

</body>
</html>
