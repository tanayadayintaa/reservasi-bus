<?php
session_start();
require_once 'db_reservasibus.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';

$stmt = $pdo->query("SELECT * FROM buses");
$buses = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $asal = $_POST['asal'] ?? '';
    $tujuan = $_POST['tujuan'] ?? '';
    $bus_id = $_POST['bus_id'] ?? '';

    if (!$asal || !$tujuan || !$bus_id) {
        $errors[] = "Semua field harus diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT harga FROM buses WHERE id = :id");
        $stmt->execute([':id' => $bus_id]);
        $bus = $stmt->fetch();

        if ($bus) {
            $harga = $bus['harga'];
            $stmt = $pdo->prepare("INSERT INTO booking (user_id, asal, tujuan, bus_id, harga, tanggal) VALUES (:uid, :asal, :tujuan, :bus_id, :harga, NOW())");
            $stmt->execute([
                ':uid' => $user_id,
                ':asal' => $asal,
                ':tujuan' => $tujuan,
                ':bus_id' => $bus_id,
                ':harga' => $harga
            ]);
            $success = "Booking berhasil! Harga: Rp " . number_format($harga,0,",",".");
        } else {
            $errors[] = "Bus tidak ditemukan.";
        }
    }
}

// Ambil semua booking user
$stmt = $pdo->prepare("SELECT b.id, b.asal, b.tujuan, bs.nama_bus, b.harga, b.tanggal 
                       FROM booking b 
                       JOIN buses bs ON b.bus_id = bs.id 
                       WHERE b.user_id = :uid
                       ORDER BY b.tanggal DESC");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking Bus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f8f9fa;
    padding: 2rem 0;
}
.card {
    border-radius: 1rem;
}
.card-body {
    padding: 2rem;
}
</style>
</head>
<body>
<main class="container">
<div class="card shadow-sm mb-4">
<div class="card-body">
<h3 class="mb-4 text-center">Booking Bus</h3>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post">
<div class="mb-3">
<label class="form-label">Asal</label>
<input type="text" name="asal" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Tujuan</label>
<input type="text" name="tujuan" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Pilih Bus</label>
<select name="bus_id" class="form-select" required>
<option value="">-- Pilih Bus --</option>
<?php foreach($buses as $b): ?>
<option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nama_bus']) ?> - Rp <?= number_format($b['harga'],0,",",".") ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="d-grid mb-3">
<button class="btn btn-primary btn-lg" type="submit">Booking</button>
</div>
</form>

<h5 class="mt-4">Riwayat Booking</h5>
<table class="table table-striped mt-2">
<thead>
<tr>
<th>No</th>
<th>Asal</th>
<th>Tujuan</th>
<th>Bus</th>
<th>Harga</th>
<th>Tanggal</th>
</tr>
</thead>
<tbody>
<?php if ($bookings): foreach($bookings as $i => $row): ?>
<tr>
<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($row['asal']) ?></td>
<td><?= htmlspecialchars($row['tujuan']) ?></td>
<td><?= htmlspecialchars($row['nama_bus']) ?></td>
<td>Rp <?= number_format($row['harga'],0,",",".") ?></td>
<td><?= $row['tanggal'] ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" class="text-center">Belum ada booking</td></tr>
<?php endif; ?>
</tbody>
</table>

<p class="text-center mt-3"><a href="logout.php" class="btn btn-danger">Logout</a></p>
</div>
</div>
</main>
</body>
</html>
