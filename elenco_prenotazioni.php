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
    <nav style="width:100%;background:#1976D2;padding:12px 0 10px 0;margin-bottom:30px;">
        <div style="text-align:center;">
            <a href="piantina_ombrelloni.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;text-decoration:none;">Piantina Ombrelloni</a>
            <a href="elenco_prenotazioni.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;text-decoration:none;">Elenco Prenotazioni</a>
            <a href="calendario.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;text-decoration:none;">Calendario</a>
        </div>
    </nav>
    <style>
        .filter-bar { background: #E3F2FD; padding: 18px 20px 10px 20px; border-radius: 12px; margin: 20px auto 30px auto; width: 98%; max-width: 1100px; box-shadow: 0 2px 10px rgba(25,118,210,0.07); }
        .filter-bar form { display: flex; flex-wrap: wrap; gap: 18px; align-items: center; justify-content: center; }
        .filter-bar label { font-weight: bold; color: #1976D2; margin-right: 6px; }
        .filter-bar input, .filter-bar select { padding: 7px 10px; border: 1.5px solid #1976D2; border-radius: 6px; font-size: 15px; }
        .filter-bar button { padding: 7px 18px; background: #1976D2; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: 10px; }
        .filter-bar button:hover { background: #1565C0; }
        table { width: 98%; margin: 30px auto; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px 8px; border: 1px solid #bbb; text-align: center; }
        th { background: #1976D2; color: #fff; }
        tr:nth-child(even) { background: #f5f5f5; }
        .btn-small { padding: 4px 12px; border-radius: 6px; background: #1976D2; color: #fff; border: none; cursor: pointer; font-size: 14px; }
        .btn-small:hover { background: #1565C0; }
        h2 { text-align: center; margin-top: 30px; }
    </style>
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
