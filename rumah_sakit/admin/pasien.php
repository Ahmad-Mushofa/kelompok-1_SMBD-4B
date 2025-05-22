<?php
session_start();
include '../includes/koneksi.php';

// hapus data pasien
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $stmt = $koneksi->prepare("CALL hapus_pasien_dan_relasi(?)");
    if ($stmt === false) {
        die("Prepare failed: " . $koneksi->error);
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();

    header('Location: pasien.php?success=delete');
    exit();
}

// Simpan atau update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pasien = $_POST['id_pasien'] ?? null;
    $nama = $koneksi->real_escape_string($_POST['nama']);
    $jenis_kelamin = $koneksi->real_escape_string($_POST['jenis_kelamin']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $no_telepon = $koneksi->real_escape_string($_POST['no_telepon']);
    $tanggal_lahir = $koneksi->real_escape_string($_POST['tanggal_lahir']);

    $stmt = $koneksi->prepare("CALL simpan_pasien(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "isssss",
        $id_pasien,
        $nama,
        $jenis_kelamin,
        $alamat,
        $no_telepon,
        $tanggal_lahir
    );
    $stmt->execute();

    $stmt->close();

    $successType = $id_pasien ? 'update' : 'insert';
    header("Location: pasien.php?success={$successType}");
    exit();
}

include '../includes/header.php';

// Ambil data jika mode edit
$edit_pasien = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $result = $koneksi->query("SELECT * FROM pasien WHERE id_pasien = $id");
    $edit_pasien = $result->fetch_assoc();
}

$result = $koneksi->query("SELECT * FROM pasien ORDER BY id_pasien ASC");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container my-5">

    <?php if (isset($_GET['success'])): ?>
        <?php
        $msg = '';
        switch ($_GET['success']) {
            case 'insert':
                $msg = 'Pasien berhasil ditambahkan.';
                break;
            case 'update':
                $msg = 'Data pasien berhasil diperbarui.';
                break;
            case 'delete':
                $msg = 'Pasien berhasil dihapus.';
                break;
        }
        ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <?= $edit_pasien ? 'Edit Pasien' : 'Tambah Pasien' ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id_pasien" value="<?= $edit_pasien['id_pasien'] ?? '' ?>">

                <div class="mb-3">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" name="nama" class="form-control" required value="<?= $edit_pasien['nama_pasien'] ?? '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" <?= ($edit_pasien['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= ($edit_pasien['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" required><?= $edit_pasien['alamat'] ?? '' ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telepon" class="form-control" required value="<?= $edit_pasien['no_telepon'] ?? '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" required value="<?= $edit_pasien['tanggal_lahir'] ?? '' ?>">
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success"><?= $edit_pasien ? 'Update' : 'Tambah' ?> Pasien</button>
                    <?php if ($edit_pasien): ?>
                        <a href="pasien.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Daftar Pasien
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th>Tgl Lahir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                while ($pasien = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($pasien['nama_pasien']) ?></td>
                        <td><?= $pasien['jenis_kelamin'] ?></td>
                        <td><?= htmlspecialchars($pasien['alamat']) ?></td>
                        <td><?= htmlspecialchars($pasien['no_telepon']) ?></td>
                        <td><?= $pasien['tanggal_lahir'] ?></td>
                        <td>
                            <a href="pasien.php?edit_id=<?= $pasien['id_pasien'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="pasien.php?delete_id=<?= $pasien['id_pasien'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus pasien ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
