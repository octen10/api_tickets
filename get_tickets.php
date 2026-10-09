<?php

include 'db.php';

header('Content-Type: application/json; charset=UTF-8');

$sql = "
    SELECT
        id,
        id_pelanggan AS idPelanggan,
        subjek,
        dispatcher,
        status,
        tanggal_ticket AS tanggalTicket
    FROM tickets
    ORDER BY id DESC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => $conn->error
    ]);
    exit;
}

$tickets = [];

while ($row = $result->fetch_assoc()) {

    $ticketId = (int)$row['id'];

    $historySql = "
        SELECT
            id,
            status,
            created_at AS date,
            user,
            notes,
            attachment
        FROM ticket_history
        WHERE ticket_id = $ticketId
        ORDER BY id ASC
    ";

    $historyResult = $conn->query($historySql);

    $history = [];

    if ($historyResult) {

        while ($historyRow = $historyResult->fetch_assoc()) {

            /*
             * Attachment bisa berupa:
             *
             * 1. URL lengkap:
             *    http://localhost/api_tickets/uploads/foto.jpg
             *
             * 2. Path relatif:
             *    uploads/foto.jpg
             *
             * Jangan tambahkan URL dasar
             * kalau sudah berupa URL lengkap.
             */

            if (!empty($historyRow['attachment'])) {

                $attachment = trim($historyRow['attachment']);

                if (
                    strpos($attachment, 'http://') !== 0 &&
                    strpos($attachment, 'https://') !== 0
                ) {
                    $historyRow['attachment'] =
                        'http://localhost/api_tickets/' .
                        ltrim($attachment, '/');
                } else {
                    $historyRow['attachment'] = $attachment;
                }
            }

            $history[] = $historyRow;
        }
    }

    $row['history'] = $history;

    $tickets[] = $row;
}

echo json_encode($tickets);

$conn->close();

?>