<?php
// fatturato.php - Calcolo fatturato per prenotazione
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

// Filtri periodo
$data_inizio = isset($_GET['data_inizio']) ? $_GET['data_inizio'] : '';
$data_fine = isset($_GET['data_fine']) ? $_GET['data_fine'] : '';
$where = [];
if ($data_inizio) $where[] = "p.data_inizio >= '".$conn->real_escape_string($data_inizio)."'";
if ($data_fine) $where[] = "p.data_fine <= '".$conn->real_escape_string($data_fine)."'";

// Recupera tutte le prenotazioni con dati cliente e ombrellone
$sql = "SELECT p.id_prenotazioni, o.id_ombrellone, c.nome, c.cognome, p.data_inizio, p.data_fine, o.prezzo
        FROM prenotazioni p
        LEFT JOIN clienti c ON p.id_cliente = c.id_cliente
        LEFT JOIN ombrelloni o ON p.id_ombrellone = o.id_ombrellone";
if ($where) $sql .= " WHERE ".implode(' AND ', $where);
$sql .= " ORDER BY p.data_inizio DESC, o.id_ombrellone ASC";
$res = $conn->query($sql);

$totale = 0;
$righe = [];
while($row = $res->fetch_assoc()) {
    $prezzo = isset($row['prezzo']) ? floatval($row['prezzo']) : 0;
    $giorni = 1;
    if (!empty($row['data_inizio']) && !empty($row['data_fine'])) {
        $giorni = (strtotime($row['data_fine']) - strtotime($row['data_inizio'])) / 86400 + 1;
        if ($giorni < 1) $giorni = 1;
    }
    $prezzo = $prezzo * $giorni;
    $totale += $prezzo;
    $righe[] = $row + ['prezzo' => $prezzo, 'giorni' => $giorni, 'prezzo' => $prezzo];
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Fatturato Prenotazioni</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .fatturato-table { width:98%; margin:30px auto; border-collapse:collapse; background:#fff; }
        .fatturato-table th, .fatturato-table td { padding:10px 8px; border:1px solid #bbb; text-align:center; }
        .fatturato-table th { background:#1976D2; color:#fff; }
        .fatturato-table tr:nth-child(even) { background:#f5f5f5; }
        .totale-row { font-weight:bold; background:#E3F2FD; color:#0D47A1; font-size:1.2em; }
        .filter-bar { background: #E3F2FD; padding: 18px 20px 10px 20px; border-radius: 12px; margin: 20px auto 30px auto; width: 98%; max-width: 1100px; box-shadow: 0 2px 10px rgba(25,118,210,0.07); }
        .filter-bar form { display: flex; flex-wrap: wrap; gap: 18px; align-items: center; justify-content: center; }
        .filter-bar label { font-weight: bold; color: #1976D2; margin-right: 6px; }
        .filter-bar input { padding: 7px 10px; border: 1.5px solid #1976D2; border-radius: 6px; font-size: 15px; }
        .filter-bar button { padding: 7px 18px; background: #1976D2; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: 10px; }
        .filter-bar button:hover { background: #1565C0; }
    </style>
</head>
<body>
<h2>Fatturato Prenotazioni</h2>
<div class="filter-bar">
    <form method="get">
        <label>Dal <input type="date" name="data_inizio" value="<?php echo htmlspecialchars($data_inizio); ?>"></label>
        <label>Al <input type="date" name="data_fine" value="<?php echo htmlspecialchars($data_fine); ?>"></label>
        <button type="submit">Filtra</button>
        <a href="fatturato.php" style="margin-left:10px;color:#1976D2;font-weight:bold;text-decoration:underline;">Reset</a>
    </form>
</div>
<table class="fatturato-table">
    <tr>
        <th>#</th>
        <th>Ombrellone</th>
        <th>Cliente</th>
        <th>Dal</th>
        <th>Al</th>
        <th>Giorni</th>
        <th>Importo (€)</th>
    </tr>
    <?php foreach($righe as $row): ?>
    <tr>
        <td><?php echo $row['id_prenotazioni']; ?></td>
        <td><?php echo htmlspecialchars($row['id_ombrellone']); ?></td>
        <td><?php echo htmlspecialchars($row['nome'] . ' ' . $row['cognome']); ?></td>
        <td><?php echo htmlspecialchars($row['data_inizio']); ?></td>
        <td><?php echo htmlspecialchars($row['data_fine']); ?></td>
        <td><?php echo $row['giorni']; ?></td>
        <td><?php echo number_format($row['prezzo'], 2, ',', '.'); ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="totale-row">
        <td colspan="6">Totale Fatturato</td>
        <td><?php echo number_format($totale, 2, ',', '.'); ?> €</td>
    </tr>
</table>
<div class="mare-striscia"></div>
</body>
</html>
