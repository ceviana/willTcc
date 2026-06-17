<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>LoggerLora - Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <script src="js/chart.umd.min.js"></script>

  <style>
    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: #f4f6f8;
      color: #222;
    }

    header {
      background: #1f2937;
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

    .controls {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: end;
      margin-bottom: 20px;
      background: white;
      padding: 14px 16px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
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

    .controls input,
    .controls button,
    .controls a {
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      font-size: 14px;
    }

    .controls button,
    .controls a.button-link {
      background: #2563eb;
      color: white;
      cursor: pointer;
      border: none;
      text-decoration: none;
      display: inline-block;
    }

    .controls button:hover,
    .controls a.button-link:hover {
      background: #1d4ed8;
    }

    .controls a.secondary {
      background: #64748b;
    }

    .controls a.secondary:hover {
      background: #475569;
    }

    .chart-selector {
      background: white;
      padding: 14px 16px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 20px;
    }

    .chart-selector h2 {
      margin: 0 0 10px 0;
      font-size: 18px;
    }

    .checkbox-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 8px;
    }

    .checkbox-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: #334155;
    }

    .selector-actions {
      margin-top: 12px;
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .selector-actions button {
      padding: 7px 10px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      background: #64748b;
      color: white;
    }

    .selector-actions button:hover {
      background: #475569;
    }

    .status-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
      font-size: 14px;
      color: #64748b;
      font-weight: normal;
    }

    .card .value {
      font-size: 24px;
      font-weight: bold;
      color: #111827;
    }

    .chart-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
      gap: 20px;
    }

    .chart-card {
      background: white;
      border-radius: 12px;
      padding: 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      min-height: 320px;
    }

    .chart-card h2 {
      margin: 0 0 12px 0;
      font-size: 18px;
      color: #111827;
    }

    canvas {
      width: 100% !important;
      height: 260px !important;
    }

    .footer {
      margin-top: 24px;
      color: #64748b;
      font-size: 13px;
      text-align: center;
    }

    .small-info {
      color: #64748b;
      font-size: 13px;
      margin-bottom: 12px;
    }

    #pointDetailCard {
      margin-bottom: 20px;
    }

    #pointDetail {
      font-size: 14px;
      line-height: 1.6;
      color: #334155;
    }

    @media (max-width: 600px) {
      .chart-grid {
        grid-template-columns: 1fr;
      }

      .chart-card {
        min-height: 280px;
      }

      .controls {
        flex-direction: column;
        align-items: stretch;
      }

      .controls button,
      .controls a.button-link {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>

<body>

<header>
  <h1>LoggerLora - Dashboard</h1>
  <p>Monitoramento dos sensores, bateria, LoRa RSSI/SNR, potência e alcance</p>
</header>

<div class="container">

  <div class="controls">
    <div class="field">
      <label for="dataInicioInput">Data/hora inicial</label>
      <input type="datetime-local" id="dataInicioInput">
    </div>

    <div class="field">
      <label for="dataFimInput">Data/hora final</label>
      <input type="datetime-local" id="dataFimInput">
    </div>

    <div class="field">
      <label for="limitInput">Últimos pontos</label>
      <input type="number" id="limitInput" value="200" min="10" max="5000">
    </div>

    <div class="field">
      <label for="chipIdInput">d_chipid</label>
      <input type="text" id="chipIdInput" placeholder="Todos">
    </div>

    <div class="field">
      <label for="testNameInput">test_name</label>
      <input type="text" id="testNameInput" placeholder="Todos">
    </div>

    <div class="field">
      <label>&nbsp;</label>
      <label style="display:flex;align-items:center;gap:6px;">
        <input type="checkbox" id="autoRefresh" checked>
        Atualizar automaticamente
      </label>
    </div>

    <button onclick="loadData()">Aplicar filtros</button>
    <button onclick="clearFilters()">Limpar filtros</button>

    <a href="consultas.php" class="button-link">Consultas</a>
    <a href="index.php" class="button-link secondary">Início</a>
  </div>

  <div class="chart-selector">
    <h2>Gráficos exibidos</h2>

    <div class="checkbox-grid">
      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardTempHumidity" checked>
        Temperatura e Umidade
      </label>

      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardPressure" checked>
        Pressão Atmosférica
      </label>

      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardLux" checked>
        Luminosidade
      </label>

      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardBattery" checked>
        Bateria
      </label>

      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardRssiSnr" checked>
        RSSI e SNR
      </label>

      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardTxDistance" checked>
        Potência TX e Distância
      </label>

      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardRssiDistance" checked>
        RSSI versus Distância
      </label>

      <label class="checkbox-item">
        <input type="checkbox" class="chart-toggle" data-target="cardGps" checked>
        GPS - Latitude/Longitude
      </label>
    </div>

    <div class="selector-actions">
      <button onclick="selectAllCharts()">Selecionar todos</button>
      <button onclick="hideAllCharts()">Ocultar todos</button>
      <button onclick="saveChartPreferences()">Salvar preferências</button>
    </div>
  </div>

  <div class="small-info" id="filterInfo">
    Carregando dados...
  </div>

  <div class="card" id="pointDetailCard">
    <h3>Detalhes do ponto selecionado</h3>
    <div id="pointDetail">
      Passe o mouse sobre um ponto do gráfico para ver os dados, ou clique para fixar os detalhes aqui.
    </div>
  </div>

  <div class="status-grid">
    <div class="card">
      <h3>Última temperatura</h3>
      <div class="value" id="lastTemp">-- °C</div>
    </div>

    <div class="card">
      <h3>Última umidade</h3>
      <div class="value" id="lastHumidity">-- %</div>
    </div>

    <div class="card">
      <h3>Bateria</h3>
      <div class="value" id="lastBattery">-- mV</div>
    </div>

    <div class="card">
      <h3>RSSI</h3>
      <div class="value" id="lastRssi">-- dBm</div>
    </div>

    <div class="card">
      <h3>SNR</h3>
      <div class="value" id="lastSnr">-- dB</div>
    </div>

    <div class="card">
      <h3>Distância</h3>
      <div class="value" id="lastDistance">-- m</div>
    </div>
  </div>

  <div class="chart-grid">
    <div class="chart-card" id="cardTempHumidity">
      <h2>Temperatura e Umidade</h2>
      <canvas id="chartTempHumidity"></canvas>
    </div>

    <div class="chart-card" id="cardPressure">
      <h2>Pressão Atmosférica</h2>
      <canvas id="chartPressure"></canvas>
    </div>

    <div class="chart-card" id="cardLux">
      <h2>Luminosidade</h2>
      <canvas id="chartLux"></canvas>
    </div>

    <div class="chart-card" id="cardBattery">
      <h2>Bateria</h2>
      <canvas id="chartBattery"></canvas>
    </div>

    <div class="chart-card" id="cardRssiSnr">
      <h2>RSSI e SNR</h2>
      <canvas id="chartRssiSnr"></canvas>
    </div>

    <div class="chart-card" id="cardTxDistance">
      <h2>Potência TX e Distância</h2>
      <canvas id="chartTxDistance"></canvas>
    </div>

    <div class="chart-card" id="cardRssiDistance">
      <h2>RSSI versus Distância</h2>
      <canvas id="chartRssiDistance"></canvas>
    </div>

    <div class="chart-card" id="cardGps">
      <h2>GPS - Latitude/Longitude</h2>
      <canvas id="chartGps"></canvas>
    </div>
  </div>

  <div class="footer">
    LoggerLora - dados atualizados a partir do banco MySQL
  </div>

</div>

<script>
let chartTempHumidity;
let chartPressure;
let chartLux;
let chartBattery;
let chartRssiSnr;
let chartTxDistance;
let chartRssiDistance;
let chartGps;

function fmt(value, decimals = 2) {
  const n = parseFloat(value);

  if (isNaN(n)) {
    return "--";
  }

  return n.toLocaleString("pt-BR", {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  });
}

function safe(value) {
  if (value === null || value === undefined || value === "") {
    return "--";
  }

  return value;
}

function setPointDetail(title, lines) {
  const html = `
    <strong>${title}</strong><br>
    ${lines.map(line => `${line}<br>`).join("")}
  `;

  document.getElementById("pointDetail").innerHTML = html;
}

function showLoggerPointDetail(row) {
  if (!row) return;

  setPointDetail("Registro operacional - loggerLora", [
    `Data/hora: ${safe(row.created_at)}`,
    `Dispositivo: ${safe(row.d_chipid)}`,
    `Board: ${safe(row.d_boardName)}`,
    `Contador dispositivo: ${safe(row.d_count)}`,
    `Temperatura: ${fmt(row.d_t_amb, 2)} °C`,
    `Umidade: ${fmt(row.d_u_amb, 2)} %`,
    `Pressão: ${fmt(row.d_p_amb, 2)} hPa`,
    `Luminosidade: ${fmt(row.d_l_amb, 2)} lux`,
    `Bateria: ${fmt(row.d_mvbat, 0)} mV`,
    `RSSI: ${fmt(row.d_rssi, 0)} dBm`,
    `SNR: ${fmt(row.d_srn, 1)} dB`,
    `GPS: ${fmt(row.d_gps_lat, 6)}, ${fmt(row.d_gps_long, 6)}`,
    `Satélites: ${safe(row.d_gps_satellites)}`
  ]);
}

function showRangePointDetail(row) {
  if (!row) return;

  setPointDetail("Registro de alcance - lora_range_tests", [
    `Data/hora: ${safe(row.created_at)}`,
    `Teste: ${safe(row.test_name)}`,
    `Dispositivo: ${safe(row.device_chipid)}`,
    `Board: ${safe(row.device_boardName)}`,
    `Contador dispositivo: ${safe(row.device_count)}`,
    `Potência TX: ${fmt(row.device_txpower, 0)} dBm`,
    `RSSI: ${fmt(row.rssi, 0)} dBm`,
    `SNR: ${fmt(row.snr, 1)} dB`,
    `Distância: ${fmt(row.distance_m, 2)} m`,
    `GPS: ${fmt(row.gps_lat, 6)}, ${fmt(row.gps_long, 6)}`,
    `Altitude: ${fmt(row.gps_altitude, 2)} m`,
    `Satélites: ${safe(row.gps_satellites)}`,
    `Bateria: ${fmt(row.battery_mv, 0)} mV`,
    `Comando enviado: ${safe(row.command_sent)}`
  ]);
}

function loggerTooltipCallbacks(loggerData, unitsByLabel = {}) {
  return {
    title: function(items) {
      if (!items.length) return "";

      const index = items[0].dataIndex;
      const row = loggerData[index];

      return row ? row.created_at : "";
    },

    label: function(context) {
      const label = context.dataset.label || "";
      const unit = unitsByLabel[label] || "";
      const value = context.parsed.y;

      const decimals = unit === "mV" || unit === "dBm" ? 0 : 2;

      return `${label}: ${fmt(value, decimals)} ${unit}`;
    },

    afterBody: function(items) {
      if (!items.length) return [];

      const index = items[0].dataIndex;
      const row = loggerData[index];

      if (!row) return [];

      return [
        `Dispositivo: ${safe(row.d_chipid)}`,
        `d_count: ${safe(row.d_count)}`,
        `Bateria: ${fmt(row.d_mvbat, 0)} mV`,
        `RSSI: ${fmt(row.d_rssi, 0)} dBm`,
        `SNR: ${fmt(row.d_srn, 1)} dB`,
        `GPS: ${fmt(row.d_gps_lat, 6)}, ${fmt(row.d_gps_long, 6)}`,
        `Satélites: ${safe(row.d_gps_satellites)}`
      ];
    }
  };
}

function rangeTooltipCallbacks(rangeData, unitsByLabel = {}) {
  return {
    title: function(items) {
      if (!items.length) return "";

      const index = items[0].dataIndex;
      const row = rangeData[index];

      return row ? row.created_at : "";
    },

    label: function(context) {
      const label = context.dataset.label || "";
      const unit = unitsByLabel[label] || "";
      const value = context.parsed.y;

      const decimals = unit === "dBm" ? 0 : 2;

      return `${label}: ${fmt(value, decimals)} ${unit}`;
    },

    afterBody: function(items) {
      if (!items.length) return [];

      const index = items[0].dataIndex;
      const row = rangeData[index];

      if (!row) return [];

      return [
        `Teste: ${safe(row.test_name)}`,
        `Dispositivo: ${safe(row.device_chipid)}`,
        `TX: ${fmt(row.device_txpower, 0)} dBm`,
        `RSSI: ${fmt(row.rssi, 0)} dBm`,
        `SNR: ${fmt(row.snr, 1)} dB`,
        `Distância: ${fmt(row.distance_m, 2)} m`,
        `GPS: ${fmt(row.gps_lat, 6)}, ${fmt(row.gps_long, 6)}`,
        `Satélites: ${safe(row.gps_satellites)}`
      ];
    }
  };
}

function commonLoggerOptions(loggerData, unitsByLabel = {}) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: "nearest",
      intersect: false
    },
    plugins: {
      tooltip: {
        callbacks: loggerTooltipCallbacks(loggerData, unitsByLabel)
      }
    },
    onClick: function(event, elements) {
      if (elements.length > 0) {
        const index = elements[0].index;
        showLoggerPointDetail(loggerData[index]);
      }
    }
  };
}

function commonRangeOptions(rangeData, unitsByLabel = {}) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: "nearest",
      intersect: false
    },
    plugins: {
      tooltip: {
        callbacks: rangeTooltipCallbacks(rangeData, unitsByLabel)
      }
    },
    onClick: function(event, elements) {
      if (elements.length > 0) {
        const index = elements[0].index;
        showRangePointDetail(rangeData[index]);
      }
    }
  };
}

function createOrUpdateChart(chart, canvasId, config) {
  if (chart) {
    chart.data = config.data;
    chart.options = config.options;
    chart.update();
    return chart;
  }

  return new Chart(document.getElementById(canvasId), config);
}

function getLimit() {
  const value = parseInt(document.getElementById("limitInput").value);

  if (isNaN(value) || value <= 0) {
    return 200;
  }

  return Math.min(value, 5000);
}

function buildParams() {
  const params = new URLSearchParams();

  params.set("limit", getLimit());

  const dataInicio = document.getElementById("dataInicioInput").value;
  const dataFim = document.getElementById("dataFimInput").value;
  const chipId = document.getElementById("chipIdInput").value.trim();
  const testName = document.getElementById("testNameInput").value.trim();

  if (dataInicio !== "") {
    params.set("data_inicio", dataInicio);
  }

  if (dataFim !== "") {
    params.set("data_fim", dataFim);
  }

  if (chipId !== "") {
    params.set("d_chipid", chipId);
  }

  if (testName !== "") {
    params.set("test_name", testName);
  }

  return params.toString();
}

async function loadData() {
  const params = buildParams();

  try {
    const [loggerResponse, rangeResponse] = await Promise.all([
      fetch("apiLoggerData.php?" + params),
      fetch("apiRangeData.php?" + params)
    ]);

    const loggerJson = await loggerResponse.json();
    const rangeJson = await rangeResponse.json();

    if (loggerJson.status !== "ok") {
      console.error("Erro apiLoggerData:", loggerJson);
      document.getElementById("filterInfo").innerText = "Erro ao carregar dados dos sensores.";
      return;
    }

    if (rangeJson.status !== "ok") {
      console.error("Erro apiRangeData:", rangeJson);
      document.getElementById("filterInfo").innerText = "Erro ao carregar dados de alcance.";
      return;
    }

    updateDashboard(loggerJson.data, rangeJson.data);

    document.getElementById("filterInfo").innerText =
      "Sensores: " + loggerJson.count +
      " registros | Alcance: " + rangeJson.count +
      " registros | Última atualização: " + new Date().toLocaleString("pt-BR");

  } catch (error) {
    console.error("Erro ao carregar dados:", error);
    document.getElementById("filterInfo").innerText = "Erro ao carregar dados. Verifique as APIs.";
  }
}

function updateDashboard(loggerData, rangeData) {
  const labelsLogger = loggerData.map(row => row.created_at);

  const temp = loggerData.map(row => parseFloat(row.d_t_amb));
  const humidity = loggerData.map(row => parseFloat(row.d_u_amb));
  const pressure = loggerData.map(row => parseFloat(row.d_p_amb));
  const lux = loggerData.map(row => parseFloat(row.d_l_amb));
  const battery = loggerData.map(row => parseFloat(row.d_mvbat));
  const rssi = loggerData.map(row => parseFloat(row.d_rssi));
  const snr = loggerData.map(row => parseFloat(row.d_srn));

  const labelsRange = rangeData.map(row => row.created_at);
  const txPower = rangeData.map(row => parseFloat(row.device_txpower));
  const distance = rangeData.map(row => parseFloat(row.distance_m));

  const rssiDistanceData = rangeData
    .filter(row => parseFloat(row.distance_m) > 0)
    .map(row => ({
      x: parseFloat(row.distance_m),
      y: parseFloat(row.rssi),
      created_at: row.created_at,
      test_name: row.test_name,
      device_chipid: row.device_chipid,
      device_boardName: row.device_boardName,
      device_count: row.device_count,
      device_txpower: row.device_txpower,
      rssi: row.rssi,
      snr: row.snr,
      gps_lat: row.gps_lat,
      gps_long: row.gps_long,
      gps_altitude: row.gps_altitude,
      gps_satellites: row.gps_satellites,
      battery_mv: row.battery_mv,
      distance_m: row.distance_m,
      command_sent: row.command_sent
    }));

  const gpsData = rangeData
    .filter(row => parseFloat(row.gps_lat) !== 0 && parseFloat(row.gps_long) !== 0)
    .map(row => ({
      x: parseFloat(row.gps_long),
      y: parseFloat(row.gps_lat),
      created_at: row.created_at,
      test_name: row.test_name,
      device_chipid: row.device_chipid,
      device_boardName: row.device_boardName,
      device_count: row.device_count,
      device_txpower: row.device_txpower,
      rssi: row.rssi,
      snr: row.snr,
      gps_lat: row.gps_lat,
      gps_long: row.gps_long,
      gps_altitude: row.gps_altitude,
      gps_satellites: row.gps_satellites,
      battery_mv: row.battery_mv,
      distance_m: row.distance_m,
      command_sent: row.command_sent
    }));

  if (loggerData.length > 0) {
    const last = loggerData[loggerData.length - 1];

    document.getElementById("lastTemp").innerText = fmt(last.d_t_amb, 2) + " °C";
    document.getElementById("lastHumidity").innerText = fmt(last.d_u_amb, 1) + " %";
    document.getElementById("lastBattery").innerText = fmt(last.d_mvbat, 0) + " mV";
    document.getElementById("lastRssi").innerText = fmt(last.d_rssi, 0) + " dBm";
    document.getElementById("lastSnr").innerText = fmt(last.d_srn, 1) + " dB";
  } else {
    document.getElementById("lastTemp").innerText = "-- °C";
    document.getElementById("lastHumidity").innerText = "-- %";
    document.getElementById("lastBattery").innerText = "-- mV";
    document.getElementById("lastRssi").innerText = "-- dBm";
    document.getElementById("lastSnr").innerText = "-- dB";
  }

  if (rangeData.length > 0) {
    const lastRange = rangeData[rangeData.length - 1];
    document.getElementById("lastDistance").innerText = fmt(lastRange.distance_m, 1) + " m";
  } else {
    document.getElementById("lastDistance").innerText = "-- m";
  }

  chartTempHumidity = createOrUpdateChart(chartTempHumidity, "chartTempHumidity", {
    type: "line",
    data: {
      labels: labelsLogger,
      datasets: [
        {
          label: "Temperatura (°C)",
          data: temp,
          borderWidth: 2,
          tension: 0.2
        },
        {
          label: "Umidade (%)",
          data: humidity,
          borderWidth: 2,
          tension: 0.2
        }
      ]
    },
    options: commonLoggerOptions(loggerData, {
      "Temperatura (°C)": "°C",
      "Umidade (%)": "%"
    })
  });

  chartPressure = createOrUpdateChart(chartPressure, "chartPressure", {
    type: "line",
    data: {
      labels: labelsLogger,
      datasets: [
        {
          label: "Pressão (hPa)",
          data: pressure,
          borderWidth: 2,
          tension: 0.2
        }
      ]
    },
    options: commonLoggerOptions(loggerData, {
      "Pressão (hPa)": "hPa"
    })
  });

  chartLux = createOrUpdateChart(chartLux, "chartLux", {
    type: "line",
    data: {
      labels: labelsLogger,
      datasets: [
        {
          label: "Luminosidade (lux)",
          data: lux,
          borderWidth: 2,
          tension: 0.2
        }
      ]
    },
    options: commonLoggerOptions(loggerData, {
      "Luminosidade (lux)": "lux"
    })
  });

  chartBattery = createOrUpdateChart(chartBattery, "chartBattery", {
    type: "line",
    data: {
      labels: labelsLogger,
      datasets: [
        {
          label: "Bateria (mV)",
          data: battery,
          borderWidth: 2,
          tension: 0.2
        }
      ]
    },
    options: commonLoggerOptions(loggerData, {
      "Bateria (mV)": "mV"
    })
  });

  chartRssiSnr = createOrUpdateChart(chartRssiSnr, "chartRssiSnr", {
    type: "line",
    data: {
      labels: labelsLogger,
      datasets: [
        {
          label: "RSSI (dBm)",
          data: rssi,
          borderWidth: 2,
          tension: 0.2
        },
        {
          label: "SNR (dB)",
          data: snr,
          borderWidth: 2,
          tension: 0.2
        }
      ]
    },
    options: commonLoggerOptions(loggerData, {
      "RSSI (dBm)": "dBm",
      "SNR (dB)": "dB"
    })
  });

  chartTxDistance = createOrUpdateChart(chartTxDistance, "chartTxDistance", {
    type: "line",
    data: {
      labels: labelsRange,
      datasets: [
        {
          label: "Potência TX (dBm)",
          data: txPower,
          borderWidth: 2,
          tension: 0.2
        },
        {
          label: "Distância (m)",
          data: distance,
          borderWidth: 2,
          tension: 0.2
        }
      ]
    },
    options: commonRangeOptions(rangeData, {
      "Potência TX (dBm)": "dBm",
      "Distância (m)": "m"
    })
  });

  chartRssiDistance = createOrUpdateChart(chartRssiDistance, "chartRssiDistance", {
    type: "scatter",
    data: {
      datasets: [
        {
          label: "RSSI versus distância",
          data: rssiDistanceData,
          pointRadius: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: "nearest",
        intersect: false
      },
      plugins: {
        tooltip: {
          callbacks: {
            title: function(items) {
              if (!items.length) return "";

              const raw = items[0].raw;
              return raw.created_at || "";
            },
            label: function(context) {
              const raw = context.raw;

              return [
                `Distância: ${fmt(raw.distance_m, 2)} m`,
                `RSSI: ${fmt(raw.rssi, 0)} dBm`
              ];
            },
            afterBody: function(items) {
              if (!items.length) return [];

              const raw = items[0].raw;

              return [
                `SNR: ${fmt(raw.snr, 1)} dB`,
                `TX: ${fmt(raw.device_txpower, 0)} dBm`,
                `Dispositivo: ${safe(raw.device_chipid)}`,
                `GPS: ${fmt(raw.gps_lat, 6)}, ${fmt(raw.gps_long, 6)}`,
                `Satélites: ${safe(raw.gps_satellites)}`,
                `Bateria: ${fmt(raw.battery_mv, 0)} mV`,
                `Comando: ${safe(raw.command_sent)}`
              ];
            }
          }
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: "Distância (m)"
          }
        },
        y: {
          title: {
            display: true,
            text: "RSSI (dBm)"
          }
        }
      },
      onClick: function(event, elements) {
        if (elements.length > 0) {
          const point = elements[0];
          const raw = chartRssiDistance.data.datasets[point.datasetIndex].data[point.index];

          setPointDetail("Ponto selecionado - RSSI versus distância", [
            `Data/hora: ${safe(raw.created_at)}`,
            `Teste: ${safe(raw.test_name)}`,
            `Dispositivo: ${safe(raw.device_chipid)}`,
            `Distância: ${fmt(raw.distance_m, 2)} m`,
            `RSSI: ${fmt(raw.rssi, 0)} dBm`,
            `SNR: ${fmt(raw.snr, 1)} dB`,
            `TX: ${fmt(raw.device_txpower, 0)} dBm`,
            `GPS: ${fmt(raw.gps_lat, 6)}, ${fmt(raw.gps_long, 6)}`,
            `Satélites: ${safe(raw.gps_satellites)}`,
            `Bateria: ${fmt(raw.battery_mv, 0)} mV`,
            `Comando: ${safe(raw.command_sent)}`
          ]);
        }
      }
    }
  });

  chartGps = createOrUpdateChart(chartGps, "chartGps", {
    type: "scatter",
    data: {
      datasets: [
        {
          label: "Coordenadas GPS",
          data: gpsData,
          pointRadius: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: "nearest",
        intersect: false
      },
      plugins: {
        tooltip: {
          callbacks: {
            title: function(items) {
              if (!items.length) return "";

              const raw = items[0].raw;
              return raw.created_at || "";
            },
            label: function(context) {
              const raw = context.raw;

              return [
                `Latitude: ${fmt(raw.gps_lat, 6)}`,
                `Longitude: ${fmt(raw.gps_long, 6)}`
              ];
            },
            afterBody: function(items) {
              if (!items.length) return [];

              const raw = items[0].raw;

              return [
                `Distância: ${fmt(raw.distance_m, 2)} m`,
                `Altitude: ${fmt(raw.gps_altitude, 2)} m`,
                `Satélites: ${safe(raw.gps_satellites)}`,
                `RSSI: ${fmt(raw.rssi, 0)} dBm`,
                `SNR: ${fmt(raw.snr, 1)} dB`,
                `TX: ${fmt(raw.device_txpower, 0)} dBm`,
                `Bateria: ${fmt(raw.battery_mv, 0)} mV`,
                `Dispositivo: ${safe(raw.device_chipid)}`
              ];
            }
          }
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: "Longitude"
          }
        },
        y: {
          title: {
            display: true,
            text: "Latitude"
          }
        }
      },
      onClick: function(event, elements) {
        if (elements.length > 0) {
          const point = elements[0];
          const raw = chartGps.data.datasets[point.datasetIndex].data[point.index];

          setPointDetail("Ponto GPS selecionado", [
            `Data/hora: ${safe(raw.created_at)}`,
            `Teste: ${safe(raw.test_name)}`,
            `Dispositivo: ${safe(raw.device_chipid)}`,
            `Latitude: ${fmt(raw.gps_lat, 6)}`,
            `Longitude: ${fmt(raw.gps_long, 6)}`,
            `Altitude: ${fmt(raw.gps_altitude, 2)} m`,
            `Satélites: ${safe(raw.gps_satellites)}`,
            `Distância: ${fmt(raw.distance_m, 2)} m`,
            `RSSI: ${fmt(raw.rssi, 0)} dBm`,
            `SNR: ${fmt(raw.snr, 1)} dB`,
            `TX: ${fmt(raw.device_txpower, 0)} dBm`,
            `Bateria: ${fmt(raw.battery_mv, 0)} mV`,
            `Comando: ${safe(raw.command_sent)}`
          ]);
        }
      }
    }
  });

  applyChartVisibility();
}

function clearFilters() {
  document.getElementById("dataInicioInput").value = "";
  document.getElementById("dataFimInput").value = "";
  document.getElementById("limitInput").value = 200;
  document.getElementById("chipIdInput").value = "";
  document.getElementById("testNameInput").value = "";

  loadData();
}

function applyChartVisibility() {
  document.querySelectorAll(".chart-toggle").forEach(cb => {
    const targetId = cb.getAttribute("data-target");
    const card = document.getElementById(targetId);

    if (card) {
      card.style.display = cb.checked ? "block" : "none";
    }
  });
}

function selectAllCharts() {
  document.querySelectorAll(".chart-toggle").forEach(cb => {
    cb.checked = true;
  });

  applyChartVisibility();
}

function hideAllCharts() {
  document.querySelectorAll(".chart-toggle").forEach(cb => {
    cb.checked = false;
  });

  applyChartVisibility();
}

function saveChartPreferences() {
  const prefs = {};

  document.querySelectorAll(".chart-toggle").forEach(cb => {
    prefs[cb.getAttribute("data-target")] = cb.checked;
  });

  localStorage.setItem("loggerLoraChartPrefs", JSON.stringify(prefs));
  applyChartVisibility();

  alert("Preferências de gráficos salvas neste navegador.");
}

function loadChartPreferences() {
  const saved = localStorage.getItem("loggerLoraChartPrefs");

  if (!saved) {
    return;
  }

  try {
    const prefs = JSON.parse(saved);

    document.querySelectorAll(".chart-toggle").forEach(cb => {
      const target = cb.getAttribute("data-target");

      if (Object.prototype.hasOwnProperty.call(prefs, target)) {
        cb.checked = prefs[target];
      }
    });

    applyChartVisibility();

  } catch (e) {
    console.error("Erro ao carregar preferências:", e);
  }
}

document.querySelectorAll(".chart-toggle").forEach(cb => {
  cb.addEventListener("change", applyChartVisibility);
});

loadChartPreferences();
loadData();

setInterval(() => {
  if (document.getElementById("autoRefresh").checked) {
    loadData();
  }
}, 10000);
</script>

</body>
</html>