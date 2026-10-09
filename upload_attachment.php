<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!isset($_FILES['file'])) {
    echo json_encode([
        "status" => "error",
        "message" => "File tidak ditemukan"
    ]);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status" => "error",
        "message" => "Upload file gagal"
    ]);
    exit;
}

// Batas ukuran 5 MB
$maxSize = 5 * 1024 * 1024;

if ($file['size'] > $maxSize) {
    echo json_encode([
        "status" => "error",
        "message" => "Ukuran file maksimal 5 MB"
    ]);
    exit;
}

// Ambil ekstensi
$extension = strtolower(
    pathinfo($file['name'], PATHINFO_EXTENSION)
);

// Jenis file yang diperbolehkan
$allowedExtensions = [
    'jpg',
    'jpeg',
    'png',
    'webp',
    'pdf'
];

if (!in_array($extension, $allowedExtensions)) {
    echo json_encode([
        "status" => "error",
        "message" => "Format file tidak diperbolehkan"
    ]);
    exit;
}

// Buat nama file unik
$newFileName = uniqid('attachment_', true) . '.' . $extension;

// Lokasi folder upload
$uploadDir = __DIR__ . '/uploads/';

// Buat folder jika belum ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$destination = $uploadDir . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan file"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "File berhasil diupload",
    "filename" => $newFileName,
    "url" => "http://localhost/api_tickets/uploads/" . $newFileName
]);
?>