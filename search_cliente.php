<?php
// search_cliente.php
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) die();
$nome = isset($_GET['nome']) ? $conn->real_escape_string($_GET['nome']) : '';
$cognome = isset($_GET['cognome']) ? $conn->real_escape_string($_GET['cognome']) : '';
$where = [];
if ($nome) $where[] = "nome LIKE '".$nome."%'";
if ($cognome) $where[] = "cognome LIKE '".$cognome."%'";
$sql = "SELECT * FROM clienti";
if ($where) $sql .= " WHERE ".implode(' AND ', $where);
$sql .= " ORDER BY cognome, nome LIMIT 10";
$res = $conn->query($sql);
$out = [];
while($row = $res && $row->fetch_assoc()) {
    $out[] = [
        'id_cliente' => $row['id_cliente'],
        'nome' => $row['nome'],
        'cognome' => $row['cognome'],
        'cellulare' => $row['cellulare'],
        'email' => $row['email'],
        'note' => $row['note']
    ];
}
echo json_encode($out);
