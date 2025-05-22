<?php
session_start();
include '../includes/koneksi.php';

// Cek login dokter
if (!isset($_SESSION['id_dokter'])) {
    header("Location: ../login.php");
    exit;
}

$dokter_id = intval($_SESSION['id_dokter']); // amankan input

// Ambil data nama dokter
$query_dokter = "SELECT nama_dokter FROM dokter WHERE id_dokter = $dokter_id LIMIT 1";
$result_dokter = $koneksi->query($query_dokter);

if ($result_dokter && $result_dokter->num_rows > 0) {
    $dokter = $result_dokter->fetch_assoc();
    $nama_dokter = $dokter['nama_dokter'];
} else {
    $nama_dokter = "Dokter";
}

// Query ambil data pasien yang sudah diperiksa oleh dokter ini
$query = "
    SELECT p.id_pasien, p.nama_pasien, p.alamat, p.no_telepon, pr.id_periksa, pr.tanggal_periksa, pr.diagnosa
    FROM pasien p
    JOIN periksa pr ON p.id_pasien = pr.id_pasien
    WHERE pr.id_dokter = $dokter_id
    ORDER BY pr.tanggal_periksa DESC
";

$result = $koneksi->query($query);

include '../includes/header.php';
?>

<div class="container mt-5">
    <h2>Dashboard Dokter</h2>
    <p>Selamat datang, <?= htmlspecialchars($nama_dokter) ?>!</p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nama Pasien</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Tanggal Periksa</th>
                <th>Diagnosa</th>
                
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                    <td><?= htmlspecialchars($row['no_telepon']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_periksa']) ?></td>
                    <td><?= $row['diagnosa'] ? htmlspecialchars($row['diagnosa']) : '<span class="text-danger">Belum diisi</span>' ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Belum ada pasien yang diperiksa oleh Anda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
