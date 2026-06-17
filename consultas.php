<?php
// consultas.php
// /var/www/html/loggerLora/consultas.php

// ======================================================
// consultas.php
// Projeto LoggerLora
// Tela de consultas estatísticas dos sensores e LoRa
// ======================================================
// ======================================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// ======================================================
$servername 		= "localhost"; // ou o IP do seu Raspberry Pi
$username 			= "pi";
$password 			= "raspberry";
$dbname 			= "loggerLora";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro DB: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ======================================================
// FILTROS
// ======================================================

$data_inicio = isset($_GET["data_inicio"]) ? $_GET["data_inicio"] : "";
$data_fim    = isset($_GET["data_fim"]) ? $_GET["data_fim"] : "";

$whereLogger = "WHERE 1=1";
$whereRange  = "WHERE 1=1";

$paramsLogger = [];
$typesLogger  = "";

$paramsRange = [];
$typesRange  = "";

if ($data_inicio != "") {
    $whereLogger .= " AND created_at >= ?";
    $whereRange  .= " AND created_at >= ?";
    $paramsLogger[] = $data_inicio . " 00:00:00";
    $paramsRange[]  = $data_inicio . " 00:00:00";
    $typesLogger .= "s";
    $typesRange  .= "s";
}

if ($data_fim != "") {
    $whereLogger .= " AND created_at <= ?";
    $whereRange  .= " AND created_at <= ?";
    $paramsLogger[] = $data_fim . " 23:59:59";
    $paramsRange[]  = $data_fim . " 23:59:59";
    $typesLogger .= "s";
    $typesRange  .= "s";
}

// ======================================================
// FUNÇÕES
// ======================================================

function fetchOne($conn, $sql, $types = "", $params = []) {
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro prepare: " . $conn->error);
    }

    if ($types != "" && count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return $row;
}

function formatNumber($value, $decimals = 2) {
    if ($value === null || $value === "") {
        return "-";
    }

    return number_format((float)$value, $decimals, ",", ".");
}

// ======================================================
// ESTATÍSTICAS LOGGERLORA
// ======================================================

$sqlLoggerStats = "
SELECT
    COUNT(*) AS total_amostras,

    MIN(d_l_amb) AS min_lux,
    MAX(d_l_amb) AS max_lux,
    AVG(d_l_amb) AS avg_lux,

    MIN(d_t_amb) AS min_temp,
    MAX(d_t_amb) AS max_temp,
    AVG(d_t_amb) AS avg_temp,

    MIN(d_p_amb) AS min_press,
    MAX(d_p_amb) AS max_press,
    AVG(d_p_amb) AS avg_press,

    MIN(d_u_amb) AS min_umid,
    MAX(d_u_amb) AS max_umid,
    AVG(d_u_amb) AS avg_umid,

    MIN(d_mvbat) AS min_bat,
    MAX(d_mvbat) AS max_bat,
    AVG(d_mvbat) AS avg_bat,

    MIN(d_rssi) AS min_rssi,
    MAX(d_rssi) AS max_rssi,
    AVG(d_rssi) AS avg_rssi,

    MIN(d_srn) AS min_snr,
    MAX(d_srn) AS max_snr,
    AVG(d_srn) AS avg_snr,

    MIN(created_at) AS primeiro_registro,
    MAX(created_at) AS ultimo_registro
FROM loggerLora
$whereLogger
";

$loggerStats = fetchOne($conn, $sqlLoggerStats, $typesLogger, $paramsLogger);

// ======================================================
// ESTATÍSTICAS RANGE TESTS
// ======================================================

$sqlRangeStats = "
SELECT
    COUNT(*) AS total_range,

    MIN(device_txpower) AS min_txpower,
    MAX(device_txpower) AS max_txpower,
    AVG(device_txpower) AS avg_txpower,

    MIN(rssi) AS min_range_rssi,
    MAX(rssi) AS max_range_rssi,
    AVG(rssi) AS avg_range_rssi,

    MIN(snr) AS min_range_snr,
    MAX(snr) AS max_range_snr,
    AVG(snr) AS avg_range_snr,

    MIN(distance_m) AS min_distance,
    MAX(distance_m) AS max_distance,
    AVG(distance_m) AS avg_distance,

    MIN(battery_mv) AS min_range_bat,
    MAX(battery_mv) AS max_range_bat,
    AVG(battery_mv) AS avg_range_bat,

    MIN(created_at) AS primeiro_range,
    MAX(created_at) AS ultimo_range
FROM lora_range_tests
$whereRange
";

$rangeStats = fetchOne($conn, $sqlRangeStats, $typesRange, $paramsRange);

// ======================================================
// MAIORES E MENORES EVENTOS INDIVIDUAIS
// ======================================================

function fetchExtreme($conn, $table, $field, $where, $types, $params, $order) {
    $sql = "
    SELECT *
    FROM $table
    $where
    ORDER BY $field $order
    LIMIT 1
    ";

    return fetchOne($conn, $sql, $types, $params);
}

$maxTempRow = fetchExtreme($conn, "loggerLora", "d_t_amb", $whereLogger, $typesLogger, $paramsLogger, "DESC");
$minTempRow = fetchExtreme($conn, "loggerLora", "d_t_amb", $whereLogger, $typesLogger, $paramsLogger, "ASC");

$maxBatteryRow = fetchExtreme($conn, "loggerLora", "d_mvbat", $whereLogger, $typesLogger, $paramsLogger, "DESC");
$minBatteryRow = fetchExtreme($conn, "loggerLora", "d_mvbat", $whereLogger, $typesLogger, $paramsLogger, "ASC");

$maxDistanceRow = fetchExtreme($conn, "lora_range_tests", "distance_m", $whereRange, $typesRange, $paramsRange, "DESC");

$conn->close();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>LoggerLora - Consultas Estatísticas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            color: #1f2937;
        }

        header {
            background: #111827;
            color: white;
            padding: 18px 24px;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        header p {
            margin: 6px 0 0 0;
            color: #cbd5e1;
            font-size: 14px;
        }

        .container {
            padding: 20px;
        }

        .filters {
            background: white;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: end;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .field label {
            font-size: 13px;
            color: #475569;
        }

        input, button, a.button-link {
            padding: 9px 11px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }

        button, a.button-link {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        button:hover, a.button-link:hover {
            background: #1d4ed8;
        }

        a.secondary {
            background: #64748b;
        }

        a.secondary:hover {
            background: #475569;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h3 {
            margin: 0 0 8px 0;
            color: #64748b;
            font-size: 14px;
            font-weight: normal;
        }

        .card .value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .section h2 {
            margin-top: 0;
            font-size: 20px;
            color: #111827;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 780px;
        }

        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f1f5f9;
            color: #334155;
        }

        tr:hover {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #075985;
            font-size: 12px;
        }

        .footer {
            color: #64748b;
            text-align: center;
            font-size: 13px;
            margin-top: 24px;
        }

        @media (max-width: 700px) {
            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            button, a.button-link {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>LoggerLora - Consultas Estatísticas</h1>
    <p>Valores mínimos, máximos, médias e eventos principais dos sensores e do enlace LoRa</p>
</header>

<div class="container">

    <form method="GET" class="filters">
        <div class="field">
            <label for="data_inicio">Data início</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
        </div>

        <div class="field">
            <label for="data_fim">Data fim</label>
            <input type="date" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>">
        </div>

        <button type="submit">Consultar</button>

        <a class="button-link secondary" href="consultas.php">Limpar filtros</a>
        <a class="button-link" href="dashboard.php">Voltar ao Dashboard</a>
    </form>

    <div class="summary-grid">
        <div class="card">
            <h3>Total de amostras sensores</h3>
            <div class="value"><?php echo intval($loggerStats["total_amostras"]); ?></div>
        </div>

        <div class="card">
            <h3>Total de amostras alcance</h3>
            <div class="value"><?php echo intval($rangeStats["total_range"]); ?></div>
        </div>

        <div class="card">
            <h3>Primeiro registro sensores</h3>
            <div class="value" style="font-size:16px;"><?php echo $loggerStats["primeiro_registro"] ?: "-"; ?></div>
        </div>

        <div class="card">
            <h3>Último registro sensores</h3>
            <div class="value" style="font-size:16px;"><?php echo $loggerStats["ultimo_registro"] ?: "-"; ?></div>
        </div>
    </div>

    <div class="section">
        <h2>Resumo Estatístico dos Sensores</h2>

        <table>
            <thead>
                <tr>
                    <th>Grandeza</th>
                    <th>Mínimo</th>
                    <th>Máximo</th>
                    <th>Média</th>
                    <th>Unidade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Luminosidade</td>
                    <td><?php echo formatNumber($loggerStats["min_lux"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["max_lux"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["avg_lux"], 2); ?></td>
                    <td>lux</td>
                </tr>

                <tr>
                    <td>Temperatura ambiente</td>
                    <td><?php echo formatNumber($loggerStats["min_temp"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["max_temp"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["avg_temp"], 2); ?></td>
                    <td>°C</td>
                </tr>

                <tr>
                    <td>Pressão atmosférica</td>
                    <td><?php echo formatNumber($loggerStats["min_press"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["max_press"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["avg_press"], 2); ?></td>
                    <td>hPa</td>
                </tr>

                <tr>
                    <td>Umidade relativa</td>
                    <td><?php echo formatNumber($loggerStats["min_umid"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["max_umid"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["avg_umid"], 2); ?></td>
                    <td>%</td>
                </tr>

                <tr>
                    <td>Tensão da bateria</td>
                    <td><?php echo formatNumber($loggerStats["min_bat"], 0); ?></td>
                    <td><?php echo formatNumber($loggerStats["max_bat"], 0); ?></td>
                    <td><?php echo formatNumber($loggerStats["avg_bat"], 0); ?></td>
                    <td>mV</td>
                </tr>

                <tr>
                    <td>RSSI</td>
                    <td><?php echo formatNumber($loggerStats["min_rssi"], 0); ?></td>
                    <td><?php echo formatNumber($loggerStats["max_rssi"], 0); ?></td>
                    <td><?php echo formatNumber($loggerStats["avg_rssi"], 2); ?></td>
                    <td>dBm</td>
                </tr>

                <tr>
                    <td>SNR</td>
                    <td><?php echo formatNumber($loggerStats["min_snr"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["max_snr"], 2); ?></td>
                    <td><?php echo formatNumber($loggerStats["avg_snr"], 2); ?></td>
                    <td>dB</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Resumo Estatístico de Alcance LoRa</h2>

        <table>
            <thead>
                <tr>
                    <th>Grandeza</th>
                    <th>Mínimo</th>
                    <th>Máximo</th>
                    <th>Média</th>
                    <th>Unidade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Potência TX do CubeCell</td>
                    <td><?php echo formatNumber($rangeStats["min_txpower"], 0); ?></td>
                    <td><?php echo formatNumber($rangeStats["max_txpower"], 0); ?></td>
                    <td><?php echo formatNumber($rangeStats["avg_txpower"], 2); ?></td>
                    <td>dBm</td>
                </tr>

                <tr>
                    <td>RSSI no Gateway</td>
                    <td><?php echo formatNumber($rangeStats["min_range_rssi"], 0); ?></td>
                    <td><?php echo formatNumber($rangeStats["max_range_rssi"], 0); ?></td>
                    <td><?php echo formatNumber($rangeStats["avg_range_rssi"], 2); ?></td>
                    <td>dBm</td>
                </tr>

                <tr>
                    <td>SNR no Gateway</td>
                    <td><?php echo formatNumber($rangeStats["min_range_snr"], 2); ?></td>
                    <td><?php echo formatNumber($rangeStats["max_range_snr"], 2); ?></td>
                    <td><?php echo formatNumber($rangeStats["avg_range_snr"], 2); ?></td>
                    <td>dB</td>
                </tr>

                <tr>
                    <td>Distância calculada</td>
                    <td><?php echo formatNumber($rangeStats["min_distance"], 2); ?></td>
                    <td><?php echo formatNumber($rangeStats["max_distance"], 2); ?></td>
                    <td><?php echo formatNumber($rangeStats["avg_distance"], 2); ?></td>
                    <td>m</td>
                </tr>

                <tr>
                    <td>Bateria no teste de alcance</td>
                    <td><?php echo formatNumber($rangeStats["min_range_bat"], 0); ?></td>
                    <td><?php echo formatNumber($rangeStats["max_range_bat"], 0); ?></td>
                    <td><?php echo formatNumber($rangeStats["avg_range_bat"], 0); ?></td>
                    <td>mV</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Eventos Relevantes</h2>

        <table>
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Valor</th>
                    <th>Data/Hora</th>
                    <th>Dispositivo</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Maior temperatura</td>
                    <td><?php echo isset($maxTempRow["d_t_amb"]) ? formatNumber($maxTempRow["d_t_amb"], 2) . " °C" : "-"; ?></td>
                    <td><?php echo $maxTempRow["created_at"] ?? "-"; ?></td>
                    <td><?php echo $maxTempRow["d_chipid"] ?? "-"; ?></td>
                    <td><span class="badge">Sensor BME280</span></td>
                </tr>

                <tr>
                    <td>Menor temperatura</td>
                    <td><?php echo isset($minTempRow["d_t_amb"]) ? formatNumber($minTempRow["d_t_amb"], 2) . " °C" : "-"; ?></td>
                    <td><?php echo $minTempRow["created_at"] ?? "-"; ?></td>
                    <td><?php echo $minTempRow["d_chipid"] ?? "-"; ?></td>
                    <td><span class="badge">Sensor BME280</span></td>
                </tr>

                <tr>
                    <td>Maior tensão de bateria</td>
                    <td><?php echo isset($maxBatteryRow["d_mvbat"]) ? formatNumber($maxBatteryRow["d_mvbat"], 0) . " mV" : "-"; ?></td>
                    <td><?php echo $maxBatteryRow["created_at"] ?? "-"; ?></td>
                    <td><?php echo $maxBatteryRow["d_chipid"] ?? "-"; ?></td>
                    <td><span class="badge">Energia</span></td>
                </tr>

                <tr>
                    <td>Menor tensão de bateria</td>
                    <td><?php echo isset($minBatteryRow["d_mvbat"]) ? formatNumber($minBatteryRow["d_mvbat"], 0) . " mV" : "-"; ?></td>
                    <td><?php echo $minBatteryRow["created_at"] ?? "-"; ?></td>
                    <td><?php echo $minBatteryRow["d_chipid"] ?? "-"; ?></td>
                    <td><span class="badge">Energia</span></td>
                </tr>

                <tr>
                    <td>Maior distância registrada</td>
                    <td><?php echo isset($maxDistanceRow["distance_m"]) ? formatNumber($maxDistanceRow["distance_m"], 2) . " m" : "-"; ?></td>
                    <td><?php echo $maxDistanceRow["created_at"] ?? "-"; ?></td>
                    <td><?php echo $maxDistanceRow["device_chipid"] ?? "-"; ?></td>
                    <td>
                        RSSI:
                        <?php echo $maxDistanceRow["rssi"] ?? "-"; ?>
                        dBm |
                        TX:
                        <?php echo $maxDistanceRow["device_txpower"] ?? "-"; ?>
                        dBm
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        LoggerLora - consultas estatísticas geradas a partir do banco MySQL
    </div>

</div>

</body>
</html>