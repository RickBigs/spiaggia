<?php
// connessione al database
$conn = new mysqli('localhost', 'root', '', 'spiaggia'); // Sostituisci nome_database
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

// Data selezionata (oggi di default)
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Recupera lo stato degli ombrelloni per la data selezionata
$sql = "SELECT o.id_ombrellone, o.n_ombrellone, o.fila, o.tipologia, p.id_prenotazioni, c.nome, c.cognome, p.data_inizio, p.data_fine
        FROM ombrelloni o
        LEFT JOIN prenotazioni p ON o.id_ombrellone = p.id_ombrellone AND p.data_inizio <= '$data' AND p.data_fine >= '$data'
        LEFT JOIN clienti c ON p.id_cliente = c.id_cliente
        ORDER BY o.n_ombrellone ASC";
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
</head>
<body>
    <h2>Piantina Ombrelloni - Data: <?php echo htmlspecialchars($data); ?></h2>

<form method="get">
<label>Seleziona data:</label> 
<input style="width: 120px"type="date" name="data" value="<?php echo htmlspecialchars($data); ?>"></label>
    <button type="submit">Cerca</button>
    
        <form method="get" style="display: inline;">
            <input type="hidden" name="data" value="<?php echo date('Y-m-d', strtotime($data.' -1 day')); ?>">
            <button type="submit">← Giorno precedente</button>
        </form>
        <form method="get" style="display: inline;">
            <input type="hidden" name="data" value="<?php echo date('Y-m-d', strtotime($data.' +1 day')); ?>">
            <button type="submit">Giorno successivo →</button>
        </form>      
</form>

    <div class="piantina-container">
        <div class="fila fila-4">
            <?php for ($i = 10; $i >= 8; $i--): $omb = $ombrelloni[10-$i];
                $prenotato = !empty($omb['id_prenotazioni']);
                $class = $prenotato ? 'prenotato' : 'libero';
            ?>
            <div class="ombrellone <?php echo $class; ?>" onclick="modificaOmbrellone(<?php echo $omb['id_ombrellone']; ?>)">
                <?php echo $omb['n_ombrellone']; ?><br><small><?php echo $omb['fila']; ?></small>
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
                <?php echo $omb['n_ombrellone']; ?><br><small><?php echo $omb['fila']; ?></small>
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
                <?php echo $omb['n_ombrellone']; ?><br><small><?php echo $omb['fila']; ?></small>
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
                <?php echo $omb['n_ombrellone']; ?><br><small><?php echo $omb['fila']; ?></small>
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
        function modificaOmbrellone(id) {
            window.location.href = 'modifica_ombrellone.php?id=' + id + '&data=<?php echo urlencode($data); ?>';
        }
    </script>
</body>
</html>
