<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id'])) {
    $id = (int)$data['id'];

    $sql = "DELETE FROM tickets WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "message" => "Tiket berhasil dihapus"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "ID Tiket tidak valid"]);
}
?>