<?php

require_once 'db_reservasibus.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM bus WHERE id = :id");
$stmt->execute([':id' => $id]);
$bus = $stmt->fetch();
if (!$bus) {
    echo "Bus tidak ditemukan.";
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Detail Bus — <?php echo htmlspecialchars($bus['nama_bus']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="bus.css">
</head>
<body>
<header class="py-3 bg-primary text-white text-center">
  <h1 class="mb-0">Detail Bus</h1>
</header>

<main class="container my-4">
  <div class="card">
    <div class="card-body">
      <h2><?php echo htmlspecialchars($bus['nama_bus']); ?></h2>
      <p class="mb-1"><strong>Rute:</strong> <?php echo htmlspecialchars($bus['rute']); ?></p>
      <p class="mb-1"><strong>Jam Berangkat:</strong> <?php echo htmlspecialchars($bus['jam_berangkat']); ?></p>
      <p class="mb-1"><strong>Harga:</strong> Rp <?php echo number_format($bus['harga'],0,',','.'); ?></p>
      <p class="mt-3"><?php echo nl2br(htmlspecialchars($bus['deskripsi'])); ?></p>

      <div class="mt-4">
        <a class="btn btn-success" href="reservasi.php?bus_id=<?php echo (int)$bus['id']; ?>">Pesan Sekarang</a>
        <a class="btn btn-outline-secondary" href="index.php">Kembali</a>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
