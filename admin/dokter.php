<?php
session_start();
include '../includes/koneksi.php';

// Fungsi untuk update status dokter otomatis sesuai pasien yang ditangani
function updateStatusDokter($koneksi, $id_dokter) {
    // Hitung jumlah pasien yang ditangani dokter ini
    $resPeriksa = $koneksi->query("SELECT COUNT(*) as total FROM periksa WHERE id_dokter = $id_dokter");
    $resRawat = $koneksi->query("SELECT COUNT(*) as total FROM rawat_inap WHERE id_dokter = $id_dokter AND status != 'selesai'");

    $countPeriksa = $resPeriksa->fetch_assoc()['total'];
    $countRawat = $resRawat->fetch_assoc()['total'];

    $totalPasien = $countPeriksa + $countRawat;

    $status = ($totalPasien > 0) ? 'aktif' : 'tidak aktif';

    // Update status dokter
    $koneksi->query("UPDATE dokter SET status = '$status' WHERE id_dokter = $id_dokter");
}

// Hapus dokter + update status dokter lain jika perlu
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    try {
        $koneksi->query("DELETE FROM dokter WHERE id_dokter = $id");
        header('Location: dokter.php');
        exit();
    } catch (mysqli_sql_exception $e) {
        // Tangkap error dari trigger (misal: dokter masih aktif dan menangani pasien)
        $_SESSION['error'] = "Gagal menghapus dokter: " . $e->getMessage();
        header('Location: dokter.php');
        exit();
    }
}



// Update password
if (isset($_POST['update_password'])) {
    $id_dokter = intval($_POST['id_dokter']);
    $new_password = $koneksi->real_escape_string($_POST['password']);
    $koneksi->query("UPDATE dokter SET password = '$new_password' WHERE id_dokter = $id_dokter");

    header('Location: dokter.php');
    exit();
}

// Simpan tambah/edit dokter
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_password'])) {
    $id_dokter = $_POST['id_dokter'] ?? null;
    $nama = $koneksi->real_escape_string($_POST['nama_dokter']);
    $spesialis = $koneksi->real_escape_string($_POST['spesialis']);
    $no_telepon = $koneksi->real_escape_string($_POST['no_telepon']);
    $username = $koneksi->real_escape_string($_POST['username']);

    if ($id_dokter) {
        // Update data dokter (tanpa ubah status manual)
        $koneksi->query("UPDATE dokter SET 
            nama_dokter = '$nama',
            spesialis = '$spesialis',
            no_telepon = '$no_telepon',
            username = '$username'
            WHERE id_dokter = $id_dokter");

        // Update status otomatis
        updateStatusDokter($koneksi, $id_dokter);

    } else {
        // Saat tambah, insert dulu dengan status 'tidak aktif'
        $default_pass = "123456";
        $status = 'tidak aktif';

        $koneksi->query("INSERT INTO dokter 
            (nama_dokter, spesialis, no_telepon, username, password, status) 
            VALUES ('$nama', '$spesialis', '$no_telepon', '$username', '$default_pass', '$status')");

        // Ambil ID dokter baru
        $new_id = $koneksi->insert_id;

        // Update status dokter baru jika dia sudah menangani pasien (biasanya belum, tapi aman)
        updateStatusDokter($koneksi, $new_id);
    }

    header('Location: dokter.php');
    exit();
}

$edit_dokter = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $result = $koneksi->query("SELECT * FROM dokter WHERE id_dokter = $id");
    $edit_dokter = $result->fetch_assoc();
}

// Ambil data dokter
$result = $koneksi->query("SELECT * FROM dokter ORDER BY id_dokter ASC");

include '../includes/header.php';
?>

<!-- Form dan tabel dokter (hilangkan input status karena otomatis) -->
<div class="container mt-4">
    <h2 class="mb-4">Data Dokter</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Form tambah/edit dokter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="id_dokter" value="<?= $edit_dokter['id_dokter'] ?? '' ?>">

                <div class="col-md-6">
                    <label for="nama_dokter" class="form-label">Nama Dokter</label>
                    <input type="text" id="nama_dokter" name="nama_dokter" class="form-control" required value="<?= htmlspecialchars($edit_dokter['nama_dokter'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="spesialis" class="form-label">Spesialis</label>
                    <input type="text" id="spesialis" name="spesialis" class="form-control" required value="<?= htmlspecialchars($edit_dokter['spesialis'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="no_telepon" class="form-label">No Telepon</label>
                    <input type="text" id="no_telepon" name="no_telepon" class="form-control" required value="<?= htmlspecialchars($edit_dokter['no_telepon'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required value="<?= htmlspecialchars($edit_dokter['username'] ?? '') ?>">
                </div>

                <!-- Hilangkan input status supaya user tidak bisa atur status manual -->
                <!-- <div class="col-md-6">
                    <label for="status" class="form-label">Status Dokter</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="tidak aktif" <?= (isset($edit_dokter) && $edit_dokter['status'] == 'tidak aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                        <option value="aktif" <?= (isset($edit_dokter) && $edit_dokter['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                    </select>
                </div> -->

                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><?= $edit_dokter ? 'Update' : 'Tambah' ?> Dokter</button>
                    <?php if ($edit_dokter): ?>
                        <a href="dokter.php" class="btn btn-secondary ms-2">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel dokter -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Spesialis</th>
                    <th>No Telepon</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Pasien Ditangani</th>
                    <th>Password</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($dokter = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($dokter['nama_dokter']) ?></td>
                        <td><?= htmlspecialchars($dokter['spesialis']) ?></td>
                        <td><?= htmlspecialchars($dokter['no_telepon']) ?></td>
                        <td><?= htmlspecialchars($dokter['username']) ?></td>
                        <td><?= htmlspecialchars($dokter['status']) ?></td>
                        <td>
                            <?php
                                $id_dokter = $dokter['id_dokter'];
                                $pasien = [];

                                $res1 = $koneksi->query("SELECT DISTINCT p.nama_pasien FROM periksa pr JOIN pasien p ON pr.id_pasien = p.id_pasien WHERE pr.id_dokter = $id_dokter");
                                while ($row = $res1->fetch_assoc()) {
                                    $pasien[] = $row['nama_pasien'];
                                }

                                $res2 = $koneksi->query("SELECT DISTINCT p.nama_pasien FROM rawat_inap ri JOIN pasien p ON ri.id_pasien = p.id_pasien WHERE ri.id_dokter = $id_dokter AND ri.status != 'selesai'");
                                while ($row = $res2->fetch_assoc()) {
                                    if (!in_array($row['nama_pasien'], $pasien)) {
                                        $pasien[] = $row['nama_pasien'];
                                    }
                                }

                                echo $pasien ? implode(', ', $pasien) : '-';
                            ?>
                        </td>
                        <td style="min-width: 250px;">
                            <div id="pass-text-<?= $dokter['id_dokter'] ?>" class="d-inline-block me-2">
                                <?= htmlspecialchars($dokter['password']) ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showEditPassword(<?= $dokter['id_dokter'] ?>)">Edit</button>

                            <form method="POST" style="display:none; margin-top: 8px;" id="pass-form-<?= $dokter['id_dokter'] ?>">
                                <input type="hidden" name="id_dokter" value="<?= $dokter['id_dokter'] ?>">
                                <div class="input-group input-group-sm mb-2">
                                    <input type="password" class="form-control" name="password" id="password-<?= $dokter['id_dokter'] ?>" placeholder="New Password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(<?= $dokter['id_dokter'] ?>)">👁️</button>
                                </div>
                                <div>
                                    <button class="btn btn-success btn-sm me-2" type="submit" name="update_password">Save</button>
                                    <button class="btn btn-danger btn-sm" type="button" onclick="hideEditPassword(<?= $dokter['id_dokter'] ?>)">Cancel</button>
                                </div>
                            </form>
                        </td>
                        <td>
                            <a href="dokter.php?edit_id=<?= $dokter['id_dokter'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="dokter.php?delete_id=<?= $dokter['id_dokter'] ?>" onclick="return confirm('Yakin hapus dokter ini?')" class="btn btn-sm btn-danger ms-1">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showEditPassword(id) {
    document.getElementById('pass-text-' + id).style.display = 'none';
    document.getElementById('pass-form-' + id).style.display = 'block';
    document.getElementById('password-' + id).value = '';
}

function togglePassword(id) {
    const input = document.getElementById('password-' + id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

function hideEditPassword(id) {
    document.getElementById('pass-text-' + id).style.display = 'inline-block';
    document.getElementById('pass-form-' + id).style.display = 'none';
}
</script>
