<?php
include '../includes/koneksi.php';

$koneksi->query("CALL sp_total_pendapatan_rawat_inap(@total)");
$hasil = $koneksi->query("SELECT @total AS total_pendapatan");
$data = $hasil->fetch_assoc();
$total = $data['total_pendapatan'] ?? 0;

echo number_format($total, 0, ',', '.');
?>
