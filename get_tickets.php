<?php
include 'db.php';

$sql = "SELECT id, id_pelanggan AS idPelanggan, subjek, dispatcher, status, tanggal_ticket AS tanggalTicket FROM tickets ORDER BY id DESC";
$result = $conn->query($sql);

$tickets = [];

while ($row = $result->fetch_assoc()) {
    $ticket_id = $row['id'];
    
    // Ambil riwayat ticket
    $hist_sql = "SELECT status, date, user, notes, attachment FROM ticket_history WHERE ticket_id = $ticket_id ORDER BY id ASC";
    $hist_result = $conn->query($hist_sql);
    
    $history = [];
    if ($hist_result && $hist_result->num_rows > 0) {
        while ($h = $hist_result->fetch_assoc()) {
            $history[] = $h;
        }
    }
    
    $row['history'] = $history;
    $tickets[] = $row;
}

echo json_encode($tickets);
?>