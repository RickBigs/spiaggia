<?php
// elenco_prenotazioni.php
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

$sql = "SELECT p.id_prenotazioni, o.n_ombrellone, o.fila, c.nome, c.cognome, c.cellulare, c.email, p.data_inizio, p.data_fine
        FROM prenotazioni p
        LEFT JOIN clienti c ON p.id_cliente = c.id_cliente
        LEFT JOIN ombrelloni o ON p.id_ombrellone = o.id_ombrellone
        ORDER BY p.data_inizio DESC, o.n_ombrellone ASC";
$res = $conn->query($sql);
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
        </div>
    </nav>
    <style>
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
            <td><?php echo htmlspecialchars($row['n_ombrellone']); ?></td>
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
