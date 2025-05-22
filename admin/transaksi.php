<?php
session_start();
include '../includes/koneksi.php';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Daftar Transaksi Rawat Inap</h4>
        </div>
        <div class="card-body">
            <?php
            $query = "SELECT t.*, r.nama_ruangan, r.kelas_ruangan, p.nama_pasien,
                             DATEDIFF(t.tanggal_keluar, t.tanggal_masuk) AS lama_inap
                      FROM transaksi t
                      JOIN pasien p ON t.id_pasien = p.id_pasien
                      JOIN ruangan r ON t.id_ruangan = r.id_ruangan
                      ORDER BY t.tanggal_keluar DESC";

            $result = $koneksi->query($query);

            if ($result->num_rows > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered">';
                echo '<thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Pasien</th>
                            <th>Ruangan</th>
                            <th>Kelas</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Keluar</th>
                            <th>Lama Inap</th>
                            <th>Tarif/Hari</th>
                            <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>';

                $no = 1;
                while ($data = $result->fetch_assoc()) {
                    $kelas = $data['kelas_ruangan'];
                    $lama_inap = max(1, (int)$data['lama_inap']);

                    // Tarif berdasarkan kelas
                    $tarif = match($kelas) {
                        'VIP' => 500000,
                        'Kelas I' => 400000,
                        'Kelas II' => 300000,
                        'Kelas III' => 200000,
                        'Ekonomi' => 100000,
                        default => 0,
                    };

                    $total = $tarif * $lama_inap;

                    echo "<tr>
                            <td>{$no}</td>
                            <td>" . htmlspecialchars($data['nama_pasien']) . "</td>
                            <td>" . htmlspecialchars($data['nama_ruangan']) . "</td>
                            <td>{$kelas}</td>
                            <td>{$data['tanggal_masuk']}</td>
                            <td>{$data['tanggal_keluar']}</td>
                            <td>{$lama_inap} hari</td>
                            <td>Rp " . number_format($tarif, 0, ',', '.') . "</td>
                            <td><strong>Rp " . number_format($total, 0, ',', '.') . "</strong></td>
                          </tr>";
                    $no++;
                }

                echo '</tbody></table>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-info">Belum ada transaksi rawat inap yang selesai.</div>';
            }
            ?>

            <div class="text-center mt-4">
                <button id="btnTotal" class="btn btn-success">Tampilkan Total Pendapatan</button>
            </div>

            <div id="hasilTotal" class="mt-4 text-center"></div>
        </div>
    </div>
</div>

<!-- jQuery (jika belum ada) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#btnTotal').click(function() {
        $.ajax({
            url: 'get_total_pendapatan.php',
            method: 'GET',
            success: function(data) {
                $('#hasilTotal').html(`
                    <table class="table table-bordered w-50 mx-auto">
                        <thead class="table-success"><tr><th class="text-center">Total Pendapatan</th></tr></thead>
                        <tbody>
                            <tr><td class="text-center"><strong>Rp ${data}</strong></td></tr>
                        </tbody>
                    </table>
                `);
            },
            error: function() {
                $('#hasilTotal').html('<div class="alert alert-danger">Gagal mengambil data.</div>');
            }
        });
    });
});
</script>
