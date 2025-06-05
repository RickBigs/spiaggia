<?php
// calendario.php - Vista calendario mensile disponibilità ombrelloni
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

// Mese e anno selezionati (default: mese corrente)
$anno = isset($_GET['anno']) ? intval($_GET['anno']) : date('Y');
$mese = isset($_GET['mese']) ? intval($_GET['mese']) : date('n');
if ($mese < 1 || $mese > 12) $mese = date('n');
if ($anno < 2000 || $anno > 2100) $anno = date('Y');

// Calcola giorni del mese
$giorni_mese = cal_days_in_month(CAL_GREGORIAN, $mese, $anno);
$primo_giorno = "$anno-" . str_pad($mese, 2, '0', STR_PAD_LEFT) . "-01";
$ultimo_giorno = "$anno-" . str_pad($mese, 2, '0', STR_PAD_LEFT) . "-" . str_pad($giorni_mese, 2, '0', STR_PAD_LEFT);

// Recupera tutti gli ombrelloni
$ombrelloni = [];
$res = $conn->query("SELECT id_ombrellone, fila FROM ombrelloni ORDER BY id_ombrellone ASC");
while ($row = $res->fetch_assoc()) {
    $ombrelloni[$row['id_ombrellone']] = $row['fila'];
}

// Recupera tutte le prenotazioni che intersecano il mese
$prenotazioni = [];
$sql = "SELECT p.*, c.nome, c.cognome FROM prenotazioni p LEFT JOIN clienti c ON p.id_cliente = c.id_cliente WHERE p.data_fine >= '$primo_giorno' AND p.data_inizio <= '$ultimo_giorno'";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $id_ombrellone = $row['id_ombrellone'];
    $data_inizio = $row['data_inizio'];
    $data_fine = $row['data_fine'];
    for ($d = strtotime(max($data_inizio, $primo_giorno)); $d <= strtotime(min($data_fine, $ultimo_giorno)); $d += 86400) {
        $giorno = date('Y-m-d', $d);
        $prenotazioni[$id_ombrellone][$giorno] = $row;
    }
}

// Navigazione mese
function link_mese($anno, $mese, $label) {
    return '<a href="calendario.php?anno=' . $anno . '&mese=' . $mese . '" style="margin:0 10px;font-weight:bold;">' . $label . '</a>';
}
$prev_mese = $mese - 1; $prev_anno = $anno;
if ($prev_mese < 1) { $prev_mese = 12; $prev_anno--; }
$next_mese = $mese + 1; $next_anno = $anno;
if ($next_mese > 12) { $next_mese = 1; $next_anno++; }
$mesi_ita = [1=>'Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Calendario Ombrelloni</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .calendar-table { border-collapse: collapse; width: 99%; margin: 0 auto 40px auto; background: #fff; }
        .calendar-table th, .calendar-table td { border: 1px solid #bbb; padding: 4px 2px; text-align: center; font-size: 13px; }
        .calendar-table th { background: #1976D2; color: #fff; position: sticky; top: 0; z-index: 2; }
        .calendar-table td { min-width: 32px; height: 32px; position: relative; }
        .cell-libero { background: linear-gradient(135deg, #C8E6C9 60%, #A5D6A7 100%); cursor: pointer; }
        .cell-prenotato { background: linear-gradient(135deg, #FFCDD2 60%, #FF8A80 100%); cursor: pointer; }
        .cell-libero:hover, .cell-prenotato:hover { outline: 2px solid #1976D2; z-index: 10; }
        .calendar-table .ombrellone-label { background: #E3F2FD; color: #1976D2; font-weight: bold; position: sticky; left: 0; z-index: 1; }
        .calendar-tooltip { display:none; position:absolute; left:50%; top:110%; transform:translateX(-50%); background:#fff; border:2px solid #1976D2; border-radius:8px; padding:10px; min-width:180px; box-shadow:0 4px 16px rgba(0,0,0,0.13); color:#333; font-size:13px; z-index:100; }
        .calendar-table td:hover .calendar-tooltip { display:block; }
        .calendar-nav { text-align:center; margin: 25px 0 20px 0; }
        .calendar-nav a { color:#1976D2; font-size:18px; }
        .calendar-nav span { font-size:20px; font-weight:bold; color:#0D47A1; margin:0 18px; }
        @media (max-width:900px) { .calendar-table { font-size:10px; } .calendar-table th, .calendar-table td { padding:2px 1px; min-width:18px; } }
    </style>
</head>
<body>
<nav style="width:100%;background:#1976D2;padding:12px 0 10px 0;margin-bottom:30px;">
    <div style="text-align:center;">
        <a href="piantina_ombrelloni.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;text-decoration:none;">Piantina Ombrelloni</a>
        <a href="elenco_prenotazioni.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;text-decoration:none;">Elenco Prenotazioni</a>
        <a href="calendario.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;text-decoration:underline;">Calendario</a>
    </div>
</nav>
<h2>Calendario Ombrelloni - <?php echo $mesi_ita[$mese] . " $anno"; ?></h2>
<div class="calendar-nav">
    <?php echo link_mese($prev_anno, $prev_mese, '← Mese precedente'); ?>
    <span><?php echo $mesi_ita[$mese] . " $anno"; ?></span>
    <?php echo link_mese($next_anno, $next_mese, 'Mese successivo →'); ?>
</div>
<table class="calendar-table">
    <tr>
        <th>Ombrellone</th>
        <?php for($g=1; $g<=$giorni_mese; $g++): $giorno = sprintf('%02d', $g); ?>
            <th><?php echo $giorno; ?></th>
        <?php endfor; ?>
    </tr>
    <?php foreach($ombrelloni as $id_ombrellone => $fila): ?>
    <tr>
        <td class="ombrellone-label">#<?php echo $id_ombrellone; ?> <span style="font-size:11px;color:#888;">(<?php echo $fila; ?>ª fila)</span></td>
        <?php for($g=1; $g<=$giorni_mese; $g++): 
            $data_g = $anno . '-' . str_pad($mese,2,'0',STR_PAD_LEFT) . '-' . str_pad($g,2,'0',STR_PAD_LEFT);
            $pren = isset($prenotazioni[$id_ombrellone][$data_g]) ? $prenotazioni[$id_ombrellone][$data_g] : null;
            $class = $pren ? 'cell-prenotato' : 'cell-libero';
            $tooltip = $pren ? (
                'Prenotato da:<br><strong>' . htmlspecialchars($pren['nome'] . ' ' . $pren['cognome']) . '</strong><br>Dal: ' . htmlspecialchars($pren['data_inizio']) . '<br>Al: ' . htmlspecialchars($pren['data_fine'])
            ) : 'Libero';
            $link = 'modifica_ombrellone.php?id=' . $id_ombrellone . '&data=' . urlencode($data_g);
        ?>
        <td class="<?php echo $class; ?>">
            <a href="<?php echo $link; ?>" style="display:block;width:100%;height:100%;text-decoration:none;color:inherit;">
                &nbsp;
                <div class="calendar-tooltip"><?php echo $tooltip; ?></div>
            </a>
        </td>
        <?php endfor; ?>
    </tr>
    <?php endforeach; ?>
</table>
<p style="text-align:center;"><a href="piantina_ombrelloni.php" class="btn">Torna alla piantina</a></p>
<div class="mare-striscia"></div>
</body>
</html>
