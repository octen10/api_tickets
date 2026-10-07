<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['idPelanggan']) && !empty($data['subjek'])) {
    $idPelanggan = $conn->real_escape_string($data['idPelanggan']);
    $subjek      = $conn->real_escape_string($data['subjek']);
    $dispatcher  = $conn->real_escape_string($data['dispatcher']);
    $deskripsi   = isset($data['deskripsi']) ? $conn->real_escape_string($data['deskripsi']) : '';
    $user        = isset($data['user']) ? $conn->real_escape_string($data['user']) : 'System';

    $sql = "INSERT INTO tickets (id_pelanggan, subjek, dispatcher, status) 
            VALUES ('$idPelanggan', '$subjek', '$dispatcher', 'NEW')";

    if ($conn->query($sql)) {
        $ticket_id = $conn->insert_id;
        $sql_hist  = "INSERT INTO ticket_history (ticket_id, user, status, notes) 
                      VALUES ($ticket_id, '$user', 'NEW', '$deskripsi')";
        $conn->query($sql_hist);

        echo json_encode(["status" => "success", "message" => "Ticket berhasil dibuat"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
?>