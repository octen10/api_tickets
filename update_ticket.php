<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id']) && !empty($data['status'])) {
    $id         = (int)$data['id'];
    $status     = $conn->real_escape_string($data['status']);
    $user       = isset($data['user']) ? $conn->real_escape_string($data['user']) : 'System';
    $notes      = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
    $attachment = !empty($data['attachment']) ? "'" . $conn->real_escape_string($data['attachment']) . "'" : "NULL";

    $sql = "UPDATE tickets SET status = '$status' WHERE id = $id";

    if ($conn->query($sql)) {
        $sql_hist = "INSERT INTO ticket_history (ticket_id, user, status, notes, attachment) 
                     VALUES ($id, '$user', '$status', '$notes', $attachment)";
        $conn->query($sql_hist);

        echo json_encode(["status" => "success", "message" => "Tiket berhasil diperbarui"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
?>