<?php

include 'db.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Method tidak diizinkan"
    ]);
    exit;
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!is_array($data)) {
    echo json_encode([
        "status" => "error",
        "message" => "Data JSON tidak valid",
        "raw" => $rawData
    ]);
    exit;
}

$id = isset($data['id']) ? (int)$data['id'] : 0;
$status = isset($data['status']) ? trim($data['status']) : '';
$user = isset($data['user']) ? trim($data['user']) : 'Admin';
$notes = isset($data['notes']) ? trim($data['notes']) : '';
$attachment = isset($data['attachment']) ? trim($data['attachment']) : '';

if ($id <= 0 || $status === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Data update tidak lengkap",
        "debug" => [
            "id" => $id,
            "status" => $status,
            "user" => $user,
            "notes" => $notes,
            "attachment" => $attachment
        ]
    ]);
    exit;
}

$statusEscaped = $conn->real_escape_string($status);
$userEscaped = $conn->real_escape_string($user);
$notesEscaped = $conn->real_escape_string($notes);
$attachmentEscaped = $conn->real_escape_string($attachment);

$checkSql = "SELECT id FROM tickets WHERE id = $id LIMIT 1";
$checkResult = $conn->query($checkSql);

if (!$checkResult) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengecek ticket: " . $conn->error
    ]);
    exit;
}

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Ticket dengan ID $id tidak ditemukan"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Update status ticket
|--------------------------------------------------------------------------
*/

$updateSql = "
    UPDATE tickets
    SET status = '$statusEscaped'
    WHERE id = $id
";

if (!$conn->query($updateSql)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal update status ticket: " . $conn->error
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Simpan history + attachment
|--------------------------------------------------------------------------
*/

$attachmentValue = $attachmentEscaped !== ''
    ? "'$attachmentEscaped'"
    : "NULL";

$historySql = "
    INSERT INTO ticket_history
    (
        ticket_id,
        user,
        status,
        notes,
        attachment,
        created_at
    )
    VALUES
    (
        $id,
        '$userEscaped',
        '$statusEscaped',
        '$notesEscaped',
        $attachmentValue,
        NOW()
    )
";

if (!$conn->query($historySql)) {
    echo json_encode([
        "status" => "error",
        "message" => "Status berhasil diubah, tetapi history gagal disimpan: " . $conn->error
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Ticket berhasil diperbarui",
    "ticket_id" => $id,
    "attachment" => $attachment
]);

$conn->close();

?>