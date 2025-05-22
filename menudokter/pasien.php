<?php
session_start();
include '../includes/koneksi.php';

if (!isset($_SESSION['id_dokter'])) {
    header("Location: ../login.php");
    exit;
}

$dokter_id = $_SESSION['id_dokter'];
$pesan = "";

// Proses simpan diagnosa
if (isset($_POST['submit_diagnosa'])) {
    $id_pasien = intval($_POST['id_pasien']);
    $diagnosa = $koneksi->real_escape_string($_POST['diagnosa']);
    $tanggal_periksa = date('Y-m-d');

    $sql_insert = "
        INSERT INTO periksa (id_pasien, id_dokter, tanggal_periksa, diagnosa)
        VALUES ($id_pasien, $dokter_id, '$tanggal_periksa', '$diagnosa')
    ";

    if ($koneksi->query($sql_insert)) {
        // Update status dokter jadi aktif
        $koneksi->query("UPDATE dokter SET status = 'aktif' WHERE id_dokter = $dokter_id");
        $pesan = "<div class='alert alert-success'>Diagnosa berhasil disimpan.</div>";
    } else {
        if (strpos($koneksi->error, 'Pasien sudah pernah diperiksa dokter') !== false) {
            $pesan = "<div class='alert alert-warning'>Pasien sudah pernah diperiksa oleh dokter lain dan tidak dapat diperiksa lagi.</div>";
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal menyimpan diagnosa: " . $koneksi->error . "</div>";
        }
    }
}

// Proses pengajuan rawat inap
if (isset($_POST['ajukan_rawat_inap'])) {
    $id_pasien = intval($_POST['id_pasien']);

    $cek = $koneksi->query("SELECT * FROM rawat_inap WHERE id_pasien = $id_pasien AND status IN ('menunggu', 'diproses')");
    if ($cek->num_rows == 0) {
        $sql = "INSERT INTO rawat_inap (id_pasien, tanggal_masuk, status) VALUES ($id_pasien, NULL, 'menunggu')";
        if ($koneksi->query($sql)) {
            $pesan .= "<div class='alert alert-info'>Rekomendasi rawat inap diajukan.</div>";
        } else {
            $pesan .= "<div class='alert alert-danger'>Gagal mengajukan rawat inap: " . $koneksi->error . "</div>";
        }
    } else {
        $pesan .= "<div class='alert alert-warning'>Rawat inap pasien ini sudah diajukan dan sedang diproses.</div>";
    }
}

// Query pasien baru dengan info apakah pasien sudah diperiksa dokter lain
$query_pasien_baru = "
    SELECT 
        p.id_pasien, p.nama_pasien, p.alamat, p.no_telepon,
        pr_dokter_lain.id_dokter AS dokter_lain_id,
        d.nama_dokter AS nama_dokter_lain
    FROM pasien p
    LEFT JOIN periksa pr_anda ON p.id_pasien = pr_anda.id_pasien AND pr_anda.id_dokter = $dokter_id
    LEFT JOIN periksa pr_dokter_lain ON p.id_pasien = pr_dokter_lain.id_pasien AND pr_dokter_lain.id_dokter != $dokter_id
    LEFT JOIN dokter d ON pr_dokter_lain.id_dokter = d.id_dokter
    WHERE pr_anda.id_pasien IS NULL
    GROUP BY p.id_pasien
";

$result_pasien = $koneksi->query($query_pasien_baru);

$query_sudah_diagnosa = "
    SELECT p.id_pasien, p.nama_pasien, pr.diagnosa
    FROM periksa pr
    JOIN pasien p ON p.id_pasien = pr.id_pasien
    WHERE pr.id_dokter = $dokter_id
    AND p.id_pasien NOT IN (
        SELECT id_pasien FROM rawat_inap WHERE status IN ('menunggu', 'diproses', 'selesai')
    )
";
$result_rawat_inap = $koneksi->query($query_sudah_diagnosa);

include '../includes/header.php';
?>

<style>
    /* Perkecil ukuran heading utama */
    h1 {
        font-size: 1.8rem;
    }

    /* Perkecil ukuran subheading */
    h3 {
        font-size: 1.2rem;
    }

    /* Perkecil ukuran teks dalam tabel */
    table {
        font-size: 0.9rem;
    }

    /* Perkecil ukuran tombol dan input form */
    .form-control-sm, .btn-sm {
        font-size: 0.85rem;
    }
</style>

<div class="container my-5">
    <h1 class="mb-4 text-primary">Dashboard Dokter</h1>

    <?= $pesan ?>

    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Pasien Baru (Belum Pernah Didiagnosa oleh Anda)</h3>
        </div>
        <div class="card-body p-0">
            <?php if ($result_pasien && $result_pasien->num_rows > 0): ?>
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Pasien</th>
                            <th>Alamat</th>
                            <th>No Telepon</th>
                            <th style="width: 320px;">Diagnosa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_pasien->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= htmlspecialchars($row['no_telepon']) ?></td>
                            <td>
                                <?php if (!empty($row['dokter_lain_id'])): ?>
                                    <div class="alert alert-info mb-0 p-1">
                                        Sudah diperiksa oleh dr. <?= htmlspecialchars($row['nama_dokter_lain']) ?>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="id_pasien" value="<?= $row['id_pasien'] ?>">
                                        <input type="text" name="diagnosa" class="form-control form-control-sm" placeholder="Masukkan diagnosa..." required>
                                        <button type="submit" name="submit_diagnosa" class="btn btn-sm btn-success">Simpan</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center p-4 mb-0">Tidak ada pasien baru.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0">Pasien yang Sudah Didiagnosa (Opsi Rekomendasi Rawat Inap)</h3>
        </div>
        <div class="card-body p-0">
            <?php if ($result_rawat_inap && $result_rawat_inap->num_rows > 0): ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Pasien</th>
                            <th>Diagnosa</th>
                            <th style="width: 200px;">Rekomendasi Rawat Inap</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_rawat_inap->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                            <td><?= htmlspecialchars($row['diagnosa']) ?></td>
                            <td>
                                <form method="POST" class="d-flex justify-content-center">
                                    <input type="hidden" name="id_pasien" value="<?= $row['id_pasien'] ?>">
                                    <button type="submit" name="ajukan_rawat_inap" class="btn btn-warning btn-sm">Ajukan Rawat Inap</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center p-4 mb-0">Belum ada pasien yang dapat direkomendasikan untuk rawat inap.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
