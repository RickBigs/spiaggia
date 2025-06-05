<?php
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

$nome = strtolower(trim($_POST['nome'] ?? ''));
$cognome = strtolower(trim($_POST['cognome'] ?? ''));

$sql = "SELECT * FROM clienti WHERE 1=1";
if ($nome) $sql .= " AND LOWER(nome) LIKE '%" . $conn->real_escape_string($nome) . "%'";
if ($cognome) $sql .= " AND LOWER(cognome) LIKE '%" . $conn->real_escape_string($cognome) . "%'";
$sql .= " ORDER BY cognome ASC LIMIT 10";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        echo '<div class="autocomplete-item" data-nome="'.htmlspecialchars($r['nome']).'" data-cognome="'.htmlspecialchars($r['cognome']).'" data-cellulare="'.htmlspecialchars($r['cellulare']).'" data-email="'.htmlspecialchars($r['email']).'" data-note="'.htmlspecialchars($r['note']).'">';
        echo '<strong>' . htmlspecialchars($r['cognome']) . '</strong> ' . htmlspecialchars($r['nome']);
        echo '<small>' . htmlspecialchars($r['email']) . ' | ' . htmlspecialchars($r['cellulare']) . '</small>';
        echo '</div>';
    }
} else {
    echo '<div class="autocomplete-item">Nessun cliente trovato.</div>';
}
?>