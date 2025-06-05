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
    $prezzo = isset($row['prezzo']) ? $row['prezzo'] : '0.00';
    $giorni = 1;
    if (!empty($row['data_inizio']) && !empty($row['data_fine'])) {
        $giorni = (strtotime($row['data_fine']) - strtotime($row['data_inizio'])) / 86400 + 1;
        if ($giorni < 1) $giorni = 1;
    }
    // Calcolo importo come stringa per compatibilità DECIMAL
    $importo = bcmul($prezzo, (string)$giorni, 2);
    $importo = ($importo !== null && $importo !== '') ? $importo : '0.00';
    $totale = bcadd($totale, $importo, 2);
    $righe[] = $row + ['prezzo' => $prezzo, 'giorni' => $giorni, 'importo' => $importo];
}

$stat_ombrelloni = [];
$stat_tipologia = [
    'Standard' => ['count' => 0, 'importo' => 0],
    'Superior' => ['count' => 0, 'importo' => 0],
    'Premium' => ['count' => 0, 'importo' => 0],
];

// Recupera anche la tipologia per le statistiche
$sql_stat = "SELECT o.id_ombrellone, o.tipologia, o.prezzo, COUNT(p.id_prenotazioni) as num_pren, 
    SUM((DATEDIFF(p.data_fine, p.data_inizio)+1)*o.prezzo) as incasso
FROM ombrelloni o
LEFT JOIN prenotazioni p ON o.id_ombrellone = p.id_ombrellone";
if ($where) $sql_stat .= " AND ".implode(' AND ', $where);
$sql_stat .= " GROUP BY o.id_ombrellone, o.tipologia, o.prezzo ORDER BY incasso DESC, num_pren DESC";
$sql_stat = str_replace('WHERE  AND', 'WHERE', $sql_stat); // fix doppio AND
$res_stat = $conn->query($sql_stat);
while($row = $res_stat->fetch_assoc()) {
    // $row['prezzo'] e $row['incasso'] sono DECIMAL dal db
    $row['incasso'] = ($row['incasso'] !== null && $row['incasso'] !== '') ? $row['incasso'] : '0.00';
    $stat_ombrelloni[] = $row;
    $tipo = $row['tipologia'];
    if (isset($stat_tipologia[$tipo])) {
        $stat_tipologia[$tipo]['count'] += $row['num_pren'];
        $stat_tipologia[$tipo]['importo'] = bcadd($stat_tipologia[$tipo]['importo'], $row['incasso'], 2);
    }
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

<h3 style="text-align:center;margin-top:30px;">Statistiche Ombrelloni più richiesti e più venduti</h3>
<table class="fatturato-table" style="margin-bottom:20px;">
    <tr>
        <th>Ombrellone</th>
        <th>Tipologia</th>
        <th>Prenotazioni</th>
        <th>Fatturato (€)</th>
    </tr>
    <?php foreach($stat_ombrelloni as $row): ?>
    <tr>
        <td><?php echo $row['id_ombrellone']; ?></td>
        <td><?php echo $row['tipologia']; ?></td>
        <td><?php echo $row['num_pren']; ?></td>
        <td><?php echo number_format($row['incasso'] !== null ? $row['incasso'] : 0, 2, ',', '.'); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h3 style="text-align:center;margin-top:30px;">Riepilogo per Tipologia</h3>
<table class="fatturato-table" style="margin-bottom:40px;">
    <tr>
        <th>Tipologia</th>
        <th>Prenotazioni totali</th>
        <th>Fatturato totale (€)</th>
    </tr>
    <?php foreach($stat_tipologia as $tipo => $stat): ?>
    <tr>
        <td><?php echo $tipo; ?></td>
        <td><?php echo $stat['count']; ?></td>
        <td><?php echo number_format($stat['importo'] !== null ? $stat['importo'] : 0, 2, ',', '.'); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<table class="fatturato-table" id="tabella-fatturato">
    <tr>
        <th>#</th>
        <th>Ombrellone</th>
        <th>Cliente</th>
        <th>Dal</th>
        <th>Al</th>
        <th>Giorni</th>
        <th>Importo (€)</th>
    </tr>
    <?php $max = 5; $count = 0; foreach($righe as $row): $count++; ?>
    <tr class="fatturato-row" <?php if($count > $max) echo 'style="display:none"'; ?>>
        <td><?php echo $row['id_prenotazioni']; ?></td>
        <td><?php echo htmlspecialchars($row['id_ombrellone']); ?></td>
        <td><?php echo htmlspecialchars($row['nome'] . ' ' . $row['cognome']); ?></td>
        <td><?php echo htmlspecialchars($row['data_inizio']); ?></td>
        <td><?php echo htmlspecialchars($row['data_fine']); ?></td>
        <td><?php echo $row['giorni']; ?></td>
        <td><?php echo number_format($row['importo'] !== null ? $row['importo'] : 0, 2, ',', '.'); ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="totale-row">
        <td colspan="6">Totale Fatturato</td>
        <td><?php echo number_format($totale !== null ? $totale : 0, 2, ',', '.'); ?> €</td>
    </tr>
</table>
<?php if(count($righe) > $max): ?>
<div style="text-align:center;margin-bottom:30px;">
    <button id="vedi-di-piu" class="btn" style="width:auto;display:inline-block;">Vedi di più</button>
</div>
<script>
document.getElementById('vedi-di-piu').onclick = function() {
    var rows = document.querySelectorAll('.fatturato-row');
    for(let i=0; i<rows.length; i++) rows[i].style.display = '';
    this.style.display = 'none';
};
</script>
<?php endif; ?>
<div class="mare-striscia"></div>
</body>
</html>
