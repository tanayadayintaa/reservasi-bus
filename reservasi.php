<?php
session_start();
require_once 'db_reservasibus.php';

// 🔒 Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 🚌 Ambil ID bus dari URL
if (!isset($_GET['bus_id'])) {
    header('Location: index.php');
    exit;
}

$bus_id = (int)$_GET['bus_id'];

// 🔍 Ambil data bus
$stmt = $pdo->prepare("SELECT * FROM bus WHERE id = :id");
$stmt->execute([':id' => $bus_id]);
$bus = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bus) {
    die("<div style='margin:50px auto; width:400px; text-align:center; font-family:sans-serif'>
            <h3>❌ Bus tidak ditemukan.</h3>
            <a href='index.php'>Kembali</a>
         </div>");
}

// 🧾 Proses reservasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penumpang = trim($_POST['nama_penumpang']);
    $telepon        = trim($_POST['telepon']);
    $jumlah_tiket   = (int)$_POST['jumlah_tiket'];
    $metode         = trim($_POST['metode_pembayaran']);

    if ($jumlah_tiket < 1 || $jumlah_tiket > $bus['kursi_tersedia']) {
        echo "<script>alert('Jumlah kursi tidak valid atau melebihi ketersediaan!'); window.location='reservasi.php?bus_id=$bus_id';</script>";
        exit;
    }

    $total_harga = $bus['harga'] * $jumlah_tiket;
    $user_id = $_SESSION['user_id'];

    // 💾 Simpan data reservasi
    $sql = "INSERT INTO reservasi (user_id, bus_id, nama_penumpang, telepon, jumlah_tiket, metode_pembayaran, total_harga, tanggal_reservasi)
            VALUES (:user_id, :bus_id, :nama_penumpang, :telepon, :jumlah_tiket, :metode, :total_harga, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':bus_id' => $bus_id,
        ':nama_penumpang' => $nama_penumpang,
        ':telepon' => $telepon,
        ':jumlah_tiket' => $jumlah_tiket,
        ':metode' => $metode,
        ':total_harga' => $total_harga
    ]);

    // 🔄 Update kursi
    $update = $pdo->prepare("UPDATE bus SET kursi_tersedia = kursi_tersedia - :jumlah WHERE id = :id");
    $update->execute([':jumlah' => $jumlah_tiket, ':id' => $bus_id]);

    echo "<script>alert('✅ Reservasi berhasil! Total bayar: Rp " . number_format($total_harga, 0, ',', '.') . "\\nMetode Pembayaran: $metode'); window.location='riwayat.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reservasi Bus - <?= htmlspecialchars($bus['nama_bus']); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="bus.css">
</head>
<body class="bg-light">

<header class="py-3 bg-primary text-white text-center shadow-sm">
  <h2 class="mb-0">Form Reservasi Bus</h2>
</header>

<main class="container my-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-3 text-primary"><?= htmlspecialchars($bus['nama_bus']); ?></h4>

      <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Rute:</strong> <?= htmlspecialchars($bus['asal']); ?> → <?= htmlspecialchars($bus['tujuan']); ?></li>
        <li class="list-group-item"><strong>Kelas:</strong> <?= htmlspecialchars($bus['kelas']); ?></li>
        <li class="list-group-item"><strong>Harga per tiket:</strong> Rp <?= number_format($bus['harga'], 0, ',', '.'); ?></li>
        <li class="list-group-item"><strong>Kursi tersedia:</strong> <?= $bus['kursi_tersedia']; ?></li>
      </ul>

      <form method="POST">
        <div class="mb-3">
          <label for="nama_penumpang" class="form-label">Nama Penumpang</label>
          <input type="text" name="nama_penumpang" id="nama_penumpang" class="form-control" placeholder="Nama lengkap" required>
        </div>

        <div class="mb-3">
          <label for="telepon" class="form-label">Nomor Telepon</label>
          <input type="text" name="telepon" id="telepon" class="form-control" placeholder="08xxxxxxxxxx" required>
        </div>

        <div class="mb-3">
          <label for="jumlah_tiket" class="form-label">Jumlah Tiket</label>
          <input type="number" name="jumlah_tiket" id="jumlah_tiket" class="form-control"
                 min="1" max="<?= $bus['kursi_tersedia']; ?>" required>
        </div>

        <div class="mb-3">
          <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
          <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
            <option value="">-- Pilih Metode Pembayaran --</option>
            <option value="Tunai">Tunai</option>
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="Dana">Dana</option>
            <option value="Gopay">Gopay</option>
            <option value="Ovo">Ovo</option>
          </select>
        </div>

        <div class="d-flex justify-content-between">
          <a href="index.php" class="btn btn-secondary">← Kembali</a>
          <button type="submit" class="btn btn-primary">Pesan Sekarang</button>
        </div>
      </form>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
