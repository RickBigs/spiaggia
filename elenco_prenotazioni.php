<?php
// elenco_prenotazioni.php
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

// Gestione filtri
$filtro_data_inizio = isset($_GET['data_inizio']) ? $_GET['data_inizio'] : '';
$filtro_data_fine = isset($_GET['data_fine']) ? $_GET['data_fine'] : '';
$filtro_ombrellone = isset($_GET['ombrellone']) ? $_GET['ombrellone'] : '';
$filtro_cliente = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';
$filtro_testo = isset($_GET['q']) ? trim($_GET['q']) : '';

$where = [];
if ($filtro_data_inizio) $where[] = "p.data_inizio >= '".$conn->real_escape_string($filtro_data_inizio)."'";
if ($filtro_data_fine) $where[] = "p.data_fine <= '".$conn->real_escape_string($filtro_data_fine)."'";
if ($filtro_ombrellone) $where[] = "o.id_ombrellone = '".$conn->real_escape_string($filtro_ombrellone)."'";
if ($filtro_cliente) $where[] = "(c.nome LIKE '%".$conn->real_escape_string($filtro_cliente)."%' OR c.cognome LIKE '%".$conn->real_escape_string($filtro_cliente)."%')";
if ($filtro_testo) $where[] = "(
    c.nome LIKE '%".$conn->real_escape_string($filtro_testo)."%' OR
    c.cognome LIKE '%".$conn->real_escape_string($filtro_testo)."%' OR
    c.cellulare LIKE '%".$conn->real_escape_string($filtro_testo)."%' OR
    c.email LIKE '%".$conn->real_escape_string($filtro_testo)."%' OR
    o.id_ombrellone LIKE '%".$conn->real_escape_string($filtro_testo)."%' OR
    o.fila LIKE '%".$conn->real_escape_string($filtro_testo)."%' OR
    p.note LIKE '%".$conn->real_escape_string($filtro_testo)."%'
)";

$sql = "SELECT p.id_prenotazioni, o.id_ombrellone, o.fila, c.nome, c.cognome, c.cellulare, c.email, p.data_inizio, p.data_fine
        FROM prenotazioni p
        LEFT JOIN clienti c ON p.id_cliente = c.id_cliente
        LEFT JOIN ombrelloni o ON p.id_ombrellone = o.id_ombrellone";
if ($where) $sql .= " WHERE ".implode(' AND ', $where);
$sql .= " ORDER BY p.data_inizio DESC, o.id_ombrellone ASC";
$res = $conn->query($sql);

// Recupera lista ombrelloni per select
$ombrelloni_list = $conn->query("SELECT id_ombrellone FROM ombrelloni ORDER BY id_ombrellone ASC");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco Prenotazioni</title>
    <link rel="stylesheet" href="style.css">
    <?php include 'header.php'; ?>
 
</head>
<body>
    <h2>Elenco Prenotazioni</h2>
    <div class="filter-bar">
        <form method="get">
            <label>Dal <input type="date" name="data_inizio" value="<?php echo htmlspecialchars($filtro_data_inizio); ?>"></label>
            <label>Al <input type="date" name="data_fine" value="<?php echo htmlspecialchars($filtro_data_fine); ?>"></label>
            <label>Ombrellone
                <select name="ombrellone">
                    <option value="">Tutti</option>
                    <?php while($omb = $ombrelloni_list->fetch_assoc()): ?>
                        <option value="<?php echo $omb['id_ombrellone']; ?>" <?php if($filtro_ombrellone == $omb['id_ombrellone']) echo 'selected'; ?>><?php echo $omb['id_ombrellone']; ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>Cliente <input type="text" name="cliente" value="<?php echo htmlspecialchars($filtro_cliente); ?>" placeholder="Nome o Cognome"></label>
            <label>Testo <input type="text" name="q" value="<?php echo htmlspecialchars($filtro_testo); ?>" placeholder="Cerca..." style="min-width:120px;"></label>
            <button type="submit">Filtra</button>
            <a href="elenco_prenotazioni.php" style="margin-left:10px;color:#1976D2;font-weight:bold;text-decoration:underline;">Reset</a>
        </form>
    </div>
    <table>
        <tr>
            <th>#</th>
            <th>Ombrellone</th>
            <th>Fila</th>
            <th>Cliente</th>
            <th>Cellulare</th>
            <th>Email</th>
            <th>Dal</th>
            <th>Al</th>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id_prenotazioni']; ?></td>
            <td><?php echo htmlspecialchars($row['id_ombrellone']); ?></td>
            <td><?php echo htmlspecialchars($row['fila']); ?></td>
            <td><?php echo htmlspecialchars($row['nome'] . ' ' . $row['cognome']); ?></td>
            <td><?php echo htmlspecialchars($row['cellulare']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['data_inizio']); ?></td>
            <td><?php echo htmlspecialchars($row['data_fine']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p style="text-align:center;"><a href="piantina_ombrelloni.php" class="btn">Torna alla piantina</a></p>
</body>
</html>
