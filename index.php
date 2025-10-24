<?php
session_start();
require_once 'db_reservasibus.php';

// 🔒 Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ⚙️ Variabel dasar
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = 10;
$offset = ($page - 1) * $limit;

// ------------------------------
// 🔍 HITUNG TOTAL DATA
// ------------------------------
if ($search !== '') {
    $countSql = "SELECT COUNT(*) FROM bus 
                 WHERE nama_bus LIKE :s1 
                    OR asal LIKE :s2 
                    OR tujuan LIKE :s3 
                    OR deskripsi LIKE :s4";
    $countStmt = $pdo->prepare($countSql);
    $kw = "%$search%";
    $countStmt->bindValue(':s1', $kw);
    $countStmt->bindValue(':s2', $kw);
    $countStmt->bindValue(':s3', $kw);
    $countStmt->bindValue(':s4', $kw);
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();
} else {
    $total = (int)$pdo->query("SELECT COUNT(*) FROM bus")->fetchColumn();
}

$totalPages = max(1, ceil($total / $limit));

// ------------------------------
// 🚌 AMBIL DATA BUS
// ------------------------------
if ($search !== '') {
    $dataSql = "SELECT * FROM bus 
                WHERE nama_bus LIKE :s1 
                   OR asal LIKE :s2 
                   OR tujuan LIKE :s3 
                   OR deskripsi LIKE :s4
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($dataSql);
    $kw = "%$search%";
    $stmt->bindValue(':s1', $kw);
    $stmt->bindValue(':s2', $kw);
    $stmt->bindValue(':s3', $kw);
    $stmt->bindValue(':s4', $kw);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $dataSql = "SELECT * FROM bus 
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($dataSql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
}

$buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Bus - Sistem Reservasi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="bus.css">
</head>
<body class="bg-light">

<header class="py-3 bg-primary text-white text-center shadow-sm">
  <h2 class="mb-0">Sistem Reservasi Bus</h2>
  <p class="mb-0 small">Selamat datang, <?= htmlspecialchars($_SESSION['username']); ?> 👋</p>
</header>

<main class="container my-4">
  <div class="card shadow-sm">
    <div class="card-body">

      <!-- Tombol Navigasi -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <a href="riwayat.php" class="btn btn-outline-success btn-sm">📋 Lihat Booking Saya</a>
        </div>
        <div>
          <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
      </div>

      <!-- Form Pencarian -->
      <form id="searchForm" class="row g-2 mb-3" method="get" action="index.php">
        <div class="col-md-9">
          <input id="searchInput" name="search" type="search" class="form-control"
                 placeholder="Cari nama bus, asal, tujuan, atau deskripsi..."
                 value="<?= htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3 d-grid">
          <button class="btn btn-primary" type="submit">Cari</button>
        </div>
      </form>

      <hr>

      <h5 class="mb-3">🚌 Daftar Bus Tersedia</h5>

      <?php if (count($buses) === 0): ?>
        <div class="alert alert-warning text-center">Tidak ada bus ditemukan.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-primary text-center">
              <tr>
                <th>Nama Bus</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>Kelas</th>
                <th>Harga</th>
                <th>Kursi Tersedia</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($buses as $bus): ?>
              <tr class="text-center">
                <td><?= htmlspecialchars($bus['nama_bus']); ?></td>
                <td><?= htmlspecialchars($bus['asal']); ?></td>
                <td><?= htmlspecialchars($bus['tujuan']); ?></td>
                <td><?= htmlspecialchars($bus['kelas']); ?></td>
                <td>Rp <?= number_format($bus['harga'], 0, ',', '.'); ?></td>
                <td>
                  <?php if ($bus['kursi_tersedia'] > 0): ?>
                    <?= $bus['kursi_tersedia']; ?>
                  <?php else: ?>
                    <span class="text-danger fw-bold">Penuh</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($bus['kursi_tersedia'] > 0): ?>
                    <a href="reservasi.php?bus_id=<?= $bus['id']; ?>" class="btn btn-success btn-sm">Booking</a>
                  <?php else: ?>
                    <button class="btn btn-secondary btn-sm" disabled>Penuh</button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
          <ul class="pagination justify-content-center">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <li class="page-item <?= $p == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?search=<?= urlencode($search); ?>&page=<?= $p; ?>"><?= $p; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

