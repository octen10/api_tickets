<?php

include 'db.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Method tidak diizinkan"
    ]);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode([
        "status" => "error",
        "message" => "File gambar tidak ditemukan"
    ]);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status" => "error",
        "message" => "Upload file gagal. Error code: " . $file['error']
    ]);
    exit;
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowedTypes[$mimeType])) {
    echo json_encode([
        "status" => "error",
        "message" => "Format gambar tidak didukung"
    ]);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode([
        "status" => "error",
        "message" => "Ukuran gambar maksimal 5 MB"
    ]);
    exit;
}

$uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = $allowedTypes[$mimeType];

$fileName =
    'ticket_' .
    date('Ymd_His') .
    '_' .
    bin2hex(random_bytes(5)) .
    '.' .
    $extension;

$targetPath = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan file ke folder uploads"
    ]);
    exit;
}

$url = 'http://localhost/api_tickets/uploads/' . $fileName;

echo json_encode([
    "status" => "success",
    "message" => "Gambar berhasil diupload",
    "url" => $url,
    "filename" => $fileName
]);

$conn->close();

?>