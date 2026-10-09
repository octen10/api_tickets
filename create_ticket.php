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

/*
|--------------------------------------------------------------------------
| Ambil data dari frontend
|--------------------------------------------------------------------------
*/

$idPelanggan = isset($data['idPelanggan'])
    ? trim($data['idPelanggan'])
    : '';

$subjek = isset($data['subjek'])
    ? trim($data['subjek'])
    : '';

$dispatcher = isset($data['dispatcher'])
    ? trim($data['dispatcher'])
    : '';

$deskripsi = isset($data['deskripsi'])
    ? trim($data['deskripsi'])
    : '';

$user = isset($data['user'])
    ? trim($data['user'])
    : 'Admin';

/*
|--------------------------------------------------------------------------
| Validasi
|--------------------------------------------------------------------------
*/

if ($idPelanggan === '' || $subjek === '') {
    echo json_encode([
        "status" => "error",
        "message" => "ID pelanggan dan subjek wajib diisi",
        "debug" => [
            "idPelanggan" => $idPelanggan,
            "subjek" => $subjek,
            "dispatcher" => $dispatcher,
            "deskripsi" => $deskripsi,
            "user" => $user
        ]
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Escape data
|--------------------------------------------------------------------------
*/

$idPelangganEscaped = $conn->real_escape_string($idPelanggan);
$subjekEscaped = $conn->real_escape_string($subjek);
$dispatcherEscaped = $conn->real_escape_string($dispatcher);
$deskripsiEscaped = $conn->real_escape_string($deskripsi);

/*
|--------------------------------------------------------------------------
| Buat ticket
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO tickets
    (
        id_pelanggan,
        subjek,
        dispatcher,
        status,
        tanggal_ticket
    )
    VALUES
    (
        '$idPelangganEscaped',
        '$subjekEscaped',
        '$dispatcherEscaped',
        'Open',
        NOW()
    )
";

if (!$conn->query($sql)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal membuat ticket: " . $conn->error
    ]);
    exit;
}

$ticketId = $conn->insert_id;

/*
|--------------------------------------------------------------------------
| Simpan history pertama
|--------------------------------------------------------------------------
*/

$userEscaped = $conn->real_escape_string($user);
$deskripsiEscaped = $conn->real_escape_string($deskripsi);

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
        $ticketId,
        '$userEscaped',
        'Open',
        '$deskripsiEscaped',
        NULL,
        NOW()
    )
";

if (!$conn->query($historySql)) {
    echo json_encode([
        "status" => "error",
        "message" => "Ticket berhasil dibuat, tetapi history gagal disimpan: " . $conn->error
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Ticket berhasil dibuat",
    "ticket_id" => $ticketId
]);

$conn->close();

?>