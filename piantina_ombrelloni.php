<?php
// connessione al database
$conn = new mysqli('localhost', 'root', '', 'spiaggia'); 
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

// Data selezionata (oggi di default)
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Recupera lo stato degli ombrelloni per la data selezionata
$sql = "SELECT o.id_ombrellone, o.fila, o.tipologia, p.id_prenotazioni, c.nome, c.cognome, p.data_inizio, p.data_fine
        FROM ombrelloni o
        LEFT JOIN prenotazioni p ON o.id_ombrellone = p.id_ombrellone AND p.data_inizio <= '$data' AND p.data_fine >= '$data'
        LEFT JOIN clienti c ON p.id_cliente = c.id_cliente
        ORDER BY o.id_ombrellone ASC";
$result = $conn->query($sql);

$ombrelloni = [];
while ($row = $result->fetch_assoc()) {
    $ombrelloni[] = $row;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Piantina Ombrelloni</title>
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
</head>
<body>
    <h2>Piantina Ombrelloni - Data: <?php echo htmlspecialchars($data); ?></h2>

    <form method="get" id="dateForm">
    <label>Seleziona data:</label> 
    <input style="width: 150px" type="date" name="data" id="dataInput" value="<?php echo htmlspecialchars($data); ?>">
    <button type="submit">Cerca</button>
    <button type="button" onclick="cambiaGiorno(-1)">← Giorno precedente</button>
    <button type="button" onclick="cambiaGiorno(1)">Giorno successivo →</button>
</form>

    <div class="piantina-container">
        <div class="fila fila-4">
            <?php for ($i = 10; $i >= 8; $i--): $omb = $ombrelloni[10-$i];
                $prenotato = !empty($omb['id_prenotazioni']);
                $class = $prenotato ? 'prenotato' : 'libero';
            ?>
            <div class="ombrellone <?php echo $class; ?>" onclick="modificaOmbrellone(<?php echo $omb['id_ombrellone']; ?>)">
                <div class="ombrellone-numero"><?php echo $omb['id_ombrellone']; ?></div>
                <?php if ($prenotato): ?>
                    <div class="tooltip">
                        Prenotato da:<br>
                        <strong><?php echo htmlspecialchars($omb['nome'] . ' ' . $omb['cognome']); ?></strong><br>
                        Dal: <?php echo htmlspecialchars($omb['data_inizio']); ?><br>
                        Al: <?php echo htmlspecialchars($omb['data_fine']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
        <div class="fila fila-3">
            <?php for ($i = 7; $i >= 6; $i--): $omb = $ombrelloni[10-$i];
                $prenotato = !empty($omb['id_prenotazioni']);
                $class = $prenotato ? 'prenotato' : 'libero';
            ?>
            <div class="ombrellone <?php echo $class; ?>" onclick="modificaOmbrellone(<?php echo $omb['id_ombrellone']; ?>)">
                <div class="ombrellone-numero"><?php echo $omb['id_ombrellone']; ?></div>
                <?php if ($prenotato): ?>
                    <div class="tooltip">
                        Prenotato da:<br>
                        <strong><?php echo htmlspecialchars($omb['nome'] . ' ' . $omb['cognome']); ?></strong><br>
                        Dal: <?php echo htmlspecialchars($omb['data_inizio']); ?><br>
                        Al: <?php echo htmlspecialchars($omb['data_fine']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
        <div class="fila fila-2">
            <?php for ($i = 5; $i >= 3; $i--): $omb = $ombrelloni[10-$i];
                $prenotato = !empty($omb['id_prenotazioni']);
                $class = $prenotato ? 'prenotato' : 'libero';
            ?>
            <div class="ombrellone <?php echo $class; ?>" onclick="modificaOmbrellone(<?php echo $omb['id_ombrellone']; ?>)">
                <div class="ombrellone-numero"><?php echo $omb['id_ombrellone']; ?></div>
                <?php if ($prenotato): ?>
                    <div class="tooltip">
                        Prenotato da:<br>
                        <strong><?php echo htmlspecialchars($omb['nome'] . ' ' . $omb['cognome']); ?></strong><br>
                        Dal: <?php echo htmlspecialchars($omb['data_inizio']); ?><br>
                        Al: <?php echo htmlspecialchars($omb['data_fine']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
        <div class="fila fila-1">
            <?php for ($i = 2; $i >= 1; $i--): $omb = $ombrelloni[10-$i];
                $prenotato = !empty($omb['id_prenotazioni']);
                $class = $prenotato ? 'prenotato' : 'libero';
            ?>
            <div class="ombrellone <?php echo $class; ?>" onclick="modificaOmbrellone(<?php echo $omb['id_ombrellone']; ?>)">
                <div class="ombrellone-numero"><?php echo $omb['id_ombrellone']; ?></div>
                <?php if ($prenotato): ?>
                    <div class="tooltip">
                        Prenotato da:<br>
                        <strong><?php echo htmlspecialchars($omb['nome'] . ' ' . $omb['cognome']); ?></strong><br>
                        Dal: <?php echo htmlspecialchars($omb['data_inizio']); ?><br>
                        Al: <?php echo htmlspecialchars($omb['data_fine']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
        <div class="mare"></div>
    </div>
<script>

function cambiaGiorno(giorni) {
    var input = document.getElementById('dataInput');
    var data = new Date(input.value);
    data.setDate(data.getDate() + giorni);
    input.value = data.toISOString().split('T')[0];
    document.getElementById('dateForm').submit();
}
function modificaOmbrellone(id) {
    window.location.href = 'modifica_ombrellone.php?id=' + id + '&data=<?php echo urlencode($data); ?>';
}

function showTooltip(event, content) {
    const tooltip = document.querySelector('.tooltip');
    tooltip.innerHTML = content;
    tooltip.style.display = 'block';
    tooltip.style.left = event.pageX + 'px';
    tooltip.style.top = (event.pageY - 60) + 'px';
}

function hideTooltip() {
    document.querySelector('.tooltip').style.display = 'none';
}
</script>

<footer>
    
</footer>
<div class="mare-striscia"></div>
</body>
</html>
