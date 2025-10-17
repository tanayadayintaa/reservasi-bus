<?php
require_once "db_reservasibus.php";

class Reservasi extends Database {
    public function tambah($nama, $nomor, $kursi, $bus_id) {
        $stmt = $this->conn->prepare("INSERT INTO reservasi (nama_penumpang, nomor_hp, kursi, bus_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nama, $nomor, $kursi, $bus_id]);
    }

    public function getAll() {
        $stmt = $this->conn->prepare("
            SELECT r.*, b.nama AS nama_bus, b.asal, b.tujuan, b.harga 
            FROM reservasi r
            JOIN bus b ON r.bus_id = b.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
