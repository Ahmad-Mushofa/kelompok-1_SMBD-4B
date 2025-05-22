<?php
session_start();
include 'includes/koneksi.php'; // koneksi ke database

$error = '';

// Data login admin manual
$admin_username = "admin";
$admin_password = "123";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Cek login dokter terlebih dahulu
  $stmt = $koneksi->prepare("SELECT * FROM dokter WHERE username = ? AND password = ?");
  $stmt->bind_param("ss", $username, $password);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($dokter = $result->fetch_assoc()) {
    $_SESSION['dokter_logged_in'] = true;
    $_SESSION['id_dokter'] = $dokter['id_dokter'];
    $_SESSION['nama_dokter'] = $dokter['nama_dokter'];
    header("Location: menudokter/dashboard_dokter.php");
    exit;
  }

  // Jika bukan dokter, cek admin
  if ($username === $admin_username && $password === $admin_password) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['username'] = $admin_username;
    header("Location: admin/dashboard.php");
    exit;
  } else {
    $error = "Username atau password salah.";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin / Dokter</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #e6f2ff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .login-box {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      width: 360px;
      text-align: center;
    }

    .login-box img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      margin-bottom: 20px;
      border-radius: 50%;
    }

    .login-box h2 {
      margin-bottom: 20px;
      color: #005b96;
    }

    .login-box input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }

    .login-box button {
      width: 100%;
      padding: 10px;
      background: #007acc;
      color: white;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
    }

    .login-box button:hover {
      background: #005f99;
    }

    .error {
      color: red;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="login-box">
  <img src="assets/img/login.jpg" alt="Login Image">
  <h2>Login Admin / Dokter</h2>

  <?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>
</div>

</body>
</html>
