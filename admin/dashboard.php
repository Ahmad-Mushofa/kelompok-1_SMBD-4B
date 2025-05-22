<?php 
session_start(); 
include '../includes/koneksi.php'; 
include '../includes/header.php'; 
?>

<style>
  body {
    font-family: sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #ccc;
  }

  th, td {
    padding: 10px 15px;
    border: 1px solid #ccc;
    text-align: left;
  }

  th {
    background-color: #f8f9fa;
  }

  #dashboard-cards {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 30px;
  }

  .card {
    flex: 1 1 250px;
    padding: 20px;
    border-radius: 8px;
    color: white;
    min-width: 200px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }

  .card.blue { background-color: #007bff; }
  .card.green { background-color: #28a745; }
  .card.yellow { background-color: #ffc107; }

  .card h4 {
    margin-bottom: 10px;
  }

  .card p {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
  }

  .card-table {
    margin-bottom: 30px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    background-color: white;
  }

  .card-header {
    padding: 12px 20px;
    font-weight: bold;
    text-align: center;
  }

  .table-responsive {
    overflow-x: auto;
  }

  .badge {
    padding: 0.3em 0.7em;
    border-radius: 0.25rem;
    font-size: 0.9em;
  }
  .bg-success { background-color: #28a745; color: white; }
  .bg-secondary { background-color: #6c757d; color: white; }
  .bg-info { background-color: #17a2b8; }
  .bg-primary { background-color: #007bff; }
  .bg-warning { background-color: #ffc107; }
  .bg-dark { background-color: #343a40; }
</style>

<div class="container">
  <div id="dashboard-cards">
    <div class="card blue">
      <h4>Total Dokter</h4>
      <p>
        <?php
        $res = $koneksi->query("SELECT COUNT(*) AS total FROM dokter");
        $row = $res->fetch_assoc();
        echo $row['total'];
        ?>
      </p>
    </div>
    <div class="card green">
      <h4>Total Pasien</h4>
      <p>
        <?php
        $res = $koneksi->query("SELECT COUNT(*) AS total FROM pasien");
        $row = $res->fetch_assoc();
        echo $row['total'];
        ?>
      </p>
    </div>
    <div class="card yellow">
      <h4>Total Ruangan</h4>
      <p>
        <?php
        $res = $koneksi->query("SELECT COUNT(*) AS total FROM ruangan");
        $row = $res->fetch_assoc();
        echo $row['total'];
        ?>
      </p>
    </div>
  </div>

  <!-- Riwayat Diagnosa Pasien -->
  <div class="card-table">
    <div class="card-header bg-info text-white">Riwayat Diagnosa Pasien</div>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Nama Pasien</th>
            <th>Diagnosa</th>
            <th>Tanggal Periksa</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $koneksi->query("SELECT * FROM view_pasien_diagnosa LIMIT 5");
          while ($row = $res->fetch_assoc()):
          ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
              <td><?= htmlspecialchars($row['diagnosa']) ?></td>
              <td><?= htmlspecialchars($row['tanggal_periksa']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Dokter yang Mendiagnosa -->
  <div class="card-table">
    <div class="card-header bg-primary text-white">Dokter yang Mendiagnosa Pasien</div>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Nama Dokter</th>
            <th>Nama Pasien</th>
            <th>Diagnosa</th>
            <th>Tanggal Periksa</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $koneksi->query("SELECT * FROM view_dokter_diagnosa LIMIT 5");
          while ($row = $res->fetch_assoc()):
          ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
              <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
              <td><?= htmlspecialchars($row['diagnosa']) ?></td>
              <td><?= htmlspecialchars($row['tanggal_periksa']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pasien Rawat Inap Aktif -->
  <div class="card-table">
    <div class="card-header bg-warning text-white">Pasien Rawat Inap Aktif</div>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Nama Pasien</th>
            <th>Ruangan</th>
            <th>Tanggal Masuk</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $koneksi->query("SELECT * FROM view_pasien_rawat_inap_aktif");
          while ($row = $res->fetch_assoc()):
          ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
              <td><?= htmlspecialchars($row['nama_ruangan']) ?></td>
              <td><?= htmlspecialchars($row['tanggal_masuk']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Statistik Jumlah Pasien per Dokter -->
  <div class="card-table">
    <div class="card-header bg-success text-white">Statistik Jumlah Pasien per Dokter</div>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Nama Dokter</th>
            <th>Jumlah Pasien</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $koneksi->query("SELECT * FROM view_statistik_dokter");
          while ($row = $res->fetch_assoc()):
          ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
              <td><?= htmlspecialchars($row['jumlah_pasien']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  
<!-- Log Riwayat Penggunaan Ruangan -->
<div class="card mb-5">
  <div class="card-header bg-dark text-white text-center">Log Riwayat Penggunaan Ruangan</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-bordered mb-0 text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>Nama Pasien</th>
            <th>Nama Ruangan</th>
            <th>Status</th>
            <th>Tanggal Masuk</th>
            <th>Tanggal Keluar</th>
            <th>Durasi Penggunaan</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $koneksi->query("SELECT * FROM view_log_ruangan");
          while ($row = $res->fetch_assoc()):
              $isAktif = strtolower($row['status']) === 'sedang digunakan';
              $durasi = "00:00:00";

              if (!$isAktif && !empty($row['tanggal_keluar']) && !empty($row['tanggal_masuk'])) {
                  $start = strtotime($row['tanggal_masuk']);
                  $end = strtotime($row['tanggal_keluar']);
                  if ($end > $start) {
                      $durasi = gmdate("H:i:s", $end - $start);
                  }
              }
          ?>
            <tr>
              <td class="text-center align-middle"><?= htmlspecialchars($row['nama_pasien']) ?></td>
              <td class="text-center align-middle"><?= htmlspecialchars($row['nama_ruangan']) ?></td>
              <td class="text-center align-middle">
                <span class="badge <?= $isAktif ? 'bg-success' : 'bg-secondary' ?>">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
              <td class="text-center align-middle"><?= !empty($row['tanggal_masuk']) ? date('d-m-Y', strtotime($row['tanggal_masuk'])) : '-' ?></td>
              <td class="text-center align-middle"><?= !$isAktif && !empty($row['tanggal_keluar']) ? date('d-m-Y H:i', strtotime($row['tanggal_keluar'])) : '-' ?></td>
              <td class="text-center align-middle">
                <?php if ($isAktif): ?>
                  <span class="live-timer" data-start="<?= date('Y-m-d H:i:s', strtotime($row['tanggal_masuk'])) ?>">00:00:00</span>
                <?php else: ?>
                  <?= $durasi ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Script Live Timer -->

<script>
  const toggleBtn = document.getElementById('toggle-btn');
  const sidebar = document.getElementById('sidebar');

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
    });
  }

function updateTimers() {
    const timers = document.querySelectorAll('.live-timer');
    timers.forEach(el => {
      const startTimeStr = el.dataset.start;
      if (!startTimeStr) return;

      const startTime = new Date(startTimeStr);
      const now = new Date();
      const diff = now - startTime;

      if (isNaN(diff) || diff < 0) {
        el.textContent = "00:00:00";
        return;
      }

      const hours = Math.floor(diff / 1000 / 60 / 60);
      const minutes = Math.floor((diff / 1000 / 60) % 60);
      const seconds = Math.floor((diff / 1000) % 60);

      el.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    });
  }

  setInterval(updateTimers, 1000);
  updateTimers();
</script>
