<?php
// apiRangeData.php
// /var/www/html/loggerLora/apiRangeData.php

header('Content-Type: application/json; charset=utf-8');

// ======================================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// ======================================================
$servername 		= "localhost"; // ou o IP do seu Raspberry Pi
$username 			= "pi";
$password 			= "raspberry";
$dbname 			= "loggerLora";

function normalizeDateTime($value, $isEnd = false) {
    $value = trim($value);

    if ($value === "") {
        return "";
    }

    $value = str_replace("T", " ", $value);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value . ($isEnd ? " 23:59:59" : " 00:00:00");
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        return $value . ":00";
    }

    return $value;
}

$limit = isset($_GET["limit"]) ? intval($_GET["limit"]) : 200;

if ($limit <= 0) {
    $limit = 200;
}

if ($limit > 5000) {
    $limit = 5000;
}

$data_inicio = isset($_GET["data_inicio"]) ? normalizeDateTime($_GET["data_inicio"], false) : "";
$data_fim    = isset($_GET["data_fim"]) ? normalizeDateTime($_GET["data_fim"], true) : "";
$d_chipid    = isset($_GET["d_chipid"]) ? trim($_GET["d_chipid"]) : "";
$test_name   = isset($_GET["test_name"]) ? trim($_GET["test_name"]) : "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Erro DB: " . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

$where = "WHERE 1=1";
$params = [];
$types = "";

if ($data_inicio !== "") {
    $where .= " AND created_at >= ?";
    $params[] = $data_inicio;
    $types .= "s";
}

if ($data_fim !== "") {
    $where .= " AND created_at <= ?";
    $params[] = $data_fim;
    $types .= "s";
}

if ($d_chipid !== "") {
    $where .= " AND device_chipid = ?";
    $params[] = $d_chipid;
    $types .= "s";
}

if ($test_name !== "") {
    $where .= " AND test_name = ?";
    $params[] = $test_name;
    $types .= "s";
}

$sql = "
SELECT
    id,
    created_at,
    test_name,
    gateway_chipid,
    gateway_count,
    gateway_lat,
    gateway_long,
    device_chipid,
    device_boardName,
    device_count,
    device_txpower,
    rssi,
    snr,
    gps_lat,
    gps_long,
    gps_date,
    gps_time,
    gps_altitude,
    gps_satellites,
    battery_mv,
    distance_m,
    command_sent
FROM lora_range_tests
$where
ORDER BY id DESC
LIMIT ?
";

$params[] = $limit;
$types .= "i";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Erro prepare: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$data = array_reverse($data);

echo json_encode([
    "status" => "ok",
    "count" => count($data),
    "filters" => [
        "limit" => $limit,
        "data_inicio" => $data_inicio,
        "data_fim" => $data_fim,
        "d_chipid" => $d_chipid,
        "test_name" => $test_name
    ],
    "data" => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();

?>