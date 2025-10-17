<?php
require_once "db_reservasibus.php";

class Bus extends Database {
    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM bus");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cari($asal, $tujuan) {
        $stmt = $this->conn->prepare("SELECT * FROM bus WHERE asal LIKE ? AND tujuan LIKE ?");
        $stmt->execute(["%$asal%", "%$tujuan%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
