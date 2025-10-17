<?php
require_once "bus.php";
require_once "reservasi.php";

$bus = new Bus();
$reservasi = new Reservasi();

if (isset($_POST['aksi']) && $_POST['aksi'] == 'cari') {
    $asal = $_POST['asal'];
    $tujuan = $_POST['tujuan'];
    $hasil = $bus->cari($asal, $tujuan);
} elseif (isset($_POST['aksi']) && $_POST['aksi'] == 'pesan') {
    $reservasi->tambah($_POST['nama'], $_POST['nomor'], $_POST['kursi'], $_POST['bus_id']);
}

$riwayat = $reservasi->getAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Aplikasi Reservasi Bus</title>
  <link rel="stylesheet" href="bus.css">
</head>
<body>
  <header><h1>🚌 Aplikasi Reservasi Bus</h1></header>

  <main>
    <section class="search-section">
      <h2>Cari Bus</h2>
      <form method="POST">
        <input type="hidden" name="aksi" value="cari">
        <label>Asal:</label>
        <input type="text" name="asal" required>
        <label>Tujuan:</label>
        <input type="text" name="tujuan" required>
        <button type="submit">Cari</button>
      </form>
    </section>

    <?php if (!empty($hasil)): ?>
    <section>
      <h2>Hasil Pencarian</h2>
      <table>
        <tr><th>Nama Bus</th><th>Asal</th><th>Tujuan</th><th>Harga</th><th>Aksi</th></tr>
        <?php foreach ($hasil as $b): ?>
        <tr>
          <td><?= $b['nama'] ?></td>
          <td><?= $b['asal'] ?></td>
          <td><?= $b['tujuan'] ?></td>
          <td>Rp<?= number_format($b['harga'],0,',','.') ?></td>
          <td>
            <form method="POST">
              <input type="hidden" name="aksi" value="pesan">
              <input type="hidden" name="bus_id" value="<?= $b['id'] ?>">
              <input type="text" name="nama" placeholder="Nama Penumpang" required>
              <input type="text" name="nomor" placeholder="Nomor HP" required>
              <input type="number" name="kursi" placeholder="Kursi" min="1" max="20" required>
              <button type="submit">Pesan</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </section>
    <?php endif; ?>

    <section>
      <h2>Riwayat Reservasi</h2>
      <table>
        <tr><th>Nama</th><th>Bus</th><th>Asal</th><th>Tujuan</th><th>Kursi</th><th>Harga</th></tr>
        <?php foreach ($riwayat as $r): ?>
        <tr>
          <td><?= $r['nama_penumpang'] ?></td>
          <td><?= $r['nama_bus'] ?></td>
          <td><?= $r['asal'] ?></td>
          <td><?= $r['tujuan'] ?></td>
          <td><?= $r['kursi'] ?></td>
          <td>Rp<?= number_format($r['harga'],0,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </section>
  </main>

  <footer><p>© 2025 Aplikasi Reservasi Bus</p></footer>
</body>
</html>
