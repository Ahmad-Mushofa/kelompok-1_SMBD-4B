<?php
session_start();
include '../includes/koneksi.php';

// Inisialisasi pesan
$pesan = "";

// Proses hapus rawat inap
if (isset($_POST['hapus_rawat_inap'])) {
    $id_rawat_inap_hapus = intval($_POST['id_rawat_inap']);
    $hapus_sql = "DELETE FROM rawat_inap WHERE id_rawat_inap = $id_rawat_inap_hapus";
    if ($koneksi->query($hapus_sql)) {
        $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    Data rawat inap berhasil dihapus.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } else {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Gagal menghapus data rawat inap.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    }
}

// Proses update status rawat inap
if (isset($_POST['update_status'])) {
    $id_rawat_inap = intval($_POST['id_rawat_inap']);
    $aksi = $_POST['aksi'];
    $id_ruangan = $_POST['id_ruangan'] ?? 'NULL';

    // Validasi ruangan hanya jika proses
    if ($aksi === 'proses' && $id_ruangan !== 'NULL') {
        $cek = $koneksi->query("SELECT * FROM rawat_inap WHERE id_ruangan = $id_ruangan AND status IN ('menunggu','diproses')");
        if ($cek->num_rows > 0) {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Ruangan sudah digunakan. Pilih ruangan lain.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        } else {
            $call = "CALL sp_update_status_rawat_inap($id_rawat_inap, '$aksi', $id_ruangan)";
            if ($koneksi->query($call)) {
                $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            Status rawat inap berhasil diperbarui.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            } else {
                $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            Gagal memperbarui status rawat inap.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            }
        }
    } elseif ($aksi === 'selesai') {
        $call = "CALL sp_update_status_rawat_inap($id_rawat_inap, '$aksi', NULL)";
        if ($koneksi->query($call)) {
            $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Status rawat inap berhasil diperbarui.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        } else {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Gagal memperbarui status rawat inap.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        }
    }



    if (isset($sql)) {
        if ($koneksi->query($sql)) {
            $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Status rawat inap berhasil diperbarui.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        } else {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Gagal memperbarui status.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        }
    }
}

// Ambil semua data rawat inap
$query = "
    SELECT ri.id_rawat_inap, p.nama_pasien, ri.tanggal_masuk, ri.tanggal_keluar, ri.status, r.nama_ruangan
    FROM rawat_inap ri
    JOIN pasien p ON ri.id_pasien = p.id_pasien
    LEFT JOIN ruangan r ON ri.id_ruangan = r.id_ruangan
    ORDER BY FIELD(ri.status, 'menunggu', 'diproses', 'selesai'), ri.id_rawat_inap DESC
";
$result = $koneksi->query($query);

// Ruangan yang belum dipakai
$ruangan_q = $koneksi->query("
    SELECT r.*
    FROM ruangan r
    LEFT JOIN rawat_inap ri ON r.id_ruangan = ri.id_ruangan AND ri.status IN ('menunggu','diproses')
    WHERE ri.id_rawat_inap IS NULL
    ORDER BY r.nama_ruangan ASC
");
$ruangan_options = [];
while ($r = $ruangan_q->fetch_assoc()) {
    $ruangan_options[] = $r;
}

include '../includes/header.php';
?>

<!-- Tampilan -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
<div class="container mt-5">
    <?= $pesan ?>

    <div class="card shadow-sm">
        <div class="card-header text-center bg-primary text-white">
            <h4>Manajemen Rawat Inap</h4>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Nama Pasien</th>
                                <th>Ruangan</th>
                                <th>Tanggal Masuk</th>
                                <th>Tanggal Keluar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                                <td><?= $row['nama_ruangan'] ?? '<span class="text-muted">-</span>' ?></td>
                                <td><?= $row['tanggal_masuk'] ?? '<span class="text-muted">-</span>' ?></td>
                                <td><?= $row['tanggal_keluar'] ?? '<span class="text-muted">-</span>' ?></td>
                                <td class="text-center">
                                    <?php
                                        $status = $row['status'];
                                        $badgeClass = match($status) {
                                            'menunggu' => 'bg-warning text-dark',
                                            'diproses' => 'bg-info text-dark',
                                            'selesai' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td class="text-center" style="min-width: 260px;">
                                    <?php if ($status === 'menunggu'): ?>
                                        <form method="POST" class="d-inline-flex align-items-center gap-2">
                                            <input type="hidden" name="id_rawat_inap" value="<?= $row['id_rawat_inap'] ?>">
                                            <input type="hidden" name="aksi" value="proses">
                                            <select name="id_ruangan" class="form-select form-select-sm" required>
                                                <option value="" selected disabled>Pilih Ruangan</option>
                                                <?php foreach ($ruangan_options as $ruang): ?>
                                                    <option value="<?= $ruang['id_ruangan'] ?>"><?= htmlspecialchars($ruang['nama_ruangan']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                                <i class="bi bi-play-circle"></i> Proses
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline-block ms-2" onsubmit="return confirm('Yakin ingin menghapus data rawat inap ini?');">
                                            <input type="hidden" name="id_rawat_inap" value="<?= $row['id_rawat_inap'] ?>">
                                            <button type="submit" name="hapus_rawat_inap" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    <?php elseif ($status === 'diproses'): ?>
                                        <form method="POST" class="d-inline-block">
                                            <input type="hidden" name="id_rawat_inap" value="<?= $row['id_rawat_inap'] ?>">
                                            <input type="hidden" name="aksi" value="selesai">
                                            <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-circle"></i> Selesaikan
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline-block ms-2" onsubmit="return confirm('Yakin ingin menghapus data rawat inap ini?');">
                                            <input type="hidden" name="id_rawat_inap" value="<?= $row['id_rawat_inap'] ?>">
                                            <button type="submit" name="hapus_rawat_inap" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="d-inline-block ms-3" onsubmit="return confirm('Yakin ingin menghapus data rawat inap ini?');">
                                            <input type="hidden" name="id_rawat_inap" value="<?= $row['id_rawat_inap'] ?>">
                                            <button type="submit" name="hapus_rawat_inap" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center fst-italic text-muted">Belum ada pengajuan rawat inap.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.alert {
  animation: fadeIn 0.7s ease forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
