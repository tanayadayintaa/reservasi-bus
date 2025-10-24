<?php
session_start();
require_once 'db_reservasibus.php';

// 🔒 Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 🔍 Ambil semua data reservasi user
$sql = "SELECT r.id, b.nama_bus, b.asal, b.tujuan, b.kelas, r.nama_penumpang, 
               r.telepon, r.jumlah_tiket, r.total_harga, r.tanggal_reservasi
        FROM reservasi r
        JOIN bus b ON r.bus_id = b.id
        WHERE r.user_id = :uid
        ORDER BY r.tanggal_reservasi DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $user_id]);
$reservasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Reservasi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="bus.css">
</head>
<body class="bg-light">

<header class="py-3 bg-primary text-white text-center shadow-sm">
  <h2 class="mb-0">Riwayat Reservasi Anda</h2>
  <p class="mb-0 small">Selamat datang, <?= htmlspecialchars($_SESSION['username']); ?> 👋</p>
</header>

<main class="container my-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Reservasi</h5>
        <a href="index.php" class="btn btn-secondary btn-sm">← Kembali</a>
      </div>

      <?php if (count($reservasi) === 0): ?>
        <div class="alert alert-warning text-center">
          Belum ada data reservasi yang tersimpan.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead class="table-primary">
              <tr>
                <th>No</th>
                <th>Nama Bus</th>
                <th>Rute</th>
                <th>Kelas</th>
                <th>Nama Penumpang</th>
                <th>Telepon</th>
                <th>Jumlah Tiket</th>
                <th>Total Harga</th>
                <th>Tanggal Reservasi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($reservasi as $r): ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($r['nama_bus']); ?></td>
                <td><?= htmlspecialchars($r['asal']); ?> → <?= htmlspecialchars($r['tujuan']); ?></td>
                <td><?= htmlspecialchars($r['kelas']); ?></td>
                <td><?= htmlspecialchars($r['nama_penumpang']); ?></td>
                <td><?= htmlspecialchars($r['telepon']); ?></td>
                <td><?= $r['jumlah_tiket']; ?></td>
                <td>Rp <?= number_format($r['total_harga'], 0, ',', '.'); ?></td>
                <td><?= date('d/m/Y H:i', strtotime($r['tanggal_reservasi'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
