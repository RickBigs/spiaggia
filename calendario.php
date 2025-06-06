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
    <?php include 'header.php'; ?>

<style>

</style>
</head>
<body>
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
<br>
<br>
<div class="mare-striscia"></div>
</body>
</html>
