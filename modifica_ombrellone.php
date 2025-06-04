<?php
// modifica_ombrellone.php
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

$id_ombrellone = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Recupera dati ombrellone
$sql = "SELECT * FROM ombrelloni WHERE id_ombrellone = $id_ombrellone";
$res = $conn->query($sql);
$ombrellone = $res->fetch_assoc();

// Recupera prenotazione per la data
$sql2 = "SELECT p.*, c.nome, c.cognome, c.cellulare, c.email, c.note FROM prenotazioni p LEFT JOIN clienti c ON p.id_cliente = c.id_cliente WHERE p.id_ombrellone = $id_ombrellone AND p.data_inizio <= '$data' AND p.data_fine >= '$data'";
$res2 = $conn->query($sql2);
$prenotazione = $res2->fetch_assoc();

// Recupera tutti i clienti per la select
$clienti = [];
$res3 = $conn->query("SELECT * FROM clienti ORDER BY cognome, nome");
while ($row = $res3->fetch_assoc()) {
    $clienti[] = $row;
}

// Gestione inserimento/modifica prenotazione
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente']);
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    if ($prenotazione) {
        // Modifica prenotazione esistente
        $sql_upd = "UPDATE prenotazioni SET id_cliente=$id_cliente, data_inizio='$data_inizio', data_fine='$data_fine' WHERE id_prenotazioni=" . $prenotazione['id_prenotazioni'];
        $conn->query($sql_upd);
        $msg = 'Prenotazione aggiornata!';
    } else {
        // Nuova prenotazione
        $sql_ins = "INSERT INTO prenotazioni (id_cliente, id_ombrellone, data_res, data_inizio, data_fine) VALUES ($id_cliente, $id_ombrellone, NOW(), '$data_inizio', '$data_fine')";
        $conn->query($sql_ins);
        $msg = 'Prenotazione inserita!';
    }
    // Ricarica dati aggiornati
    header("Location: modifica_ombrellone.php?id=$id_ombrellone&data=$data&msg=" . urlencode($msg));
    exit;
}
if (isset($_GET['msg'])) $msg = $_GET['msg'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Ombrellone</title>
    <link rel="stylesheet" href="style.css">
    <style>

    </style>
</head>
<body>
    <div class="form-box">
        <h2>Ombrellone n°<?php echo $ombrellone['n_ombrellone']; ?> (<?php echo $ombrellone['fila']; ?> fila)</h2>
        <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <?php if ($prenotazione): ?>
            <p><strong>Prenotato da:</strong> <?php echo htmlspecialchars($prenotazione['nome'] . ' ' . $prenotazione['cognome']); ?><br>
            Cell: <?php echo htmlspecialchars($prenotazione['cellulare']); ?><br>
            Email: <?php echo htmlspecialchars($prenotazione['email']); ?><br>
            Note: <?php echo htmlspecialchars($prenotazione['note']); ?><br>
            Dal: <?php echo htmlspecialchars($prenotazione['data_inizio']); ?> al <?php echo htmlspecialchars($prenotazione['data_fine']); ?></p>
        <?php else: ?>
            <p><strong>Ombrellone libero per questa data.</strong></p>
        <?php endif; ?>
        <form method="post">
            <label>Cliente:
                <select name="id_cliente" required>
                    <option value="">Seleziona cliente</option>
                    <?php foreach ($clienti as $cli): ?>
                        <option value="<?php echo $cli['id_cliente']; ?>" <?php if ($prenotazione && $cli['id_cliente'] == $prenotazione['id_cliente']) echo 'selected'; ?>><?php echo htmlspecialchars($cli['cognome'] . ' ' . $cli['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Data inizio:
                <input type="date" name="data_inizio" value="<?php echo $prenotazione ? $prenotazione['data_inizio'] : $data; ?>" required>
            </label>
            <label>Data fine:
                <input type="date" name="data_fine" value="<?php echo $prenotazione ? $prenotazione['data_fine'] : $data; ?>" required>
            </label>
            <button class="btn" type="submit"><?php echo $prenotazione ? 'Modifica' : 'Prenota'; ?></button>
        </form>
        <p><a href="piantina_ombrelloni.php?data=<?php echo urlencode($data); ?>">&larr; Torna alla piantina</a></p>
    </div>
</body>
</html>
