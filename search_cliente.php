<?php
// search_cliente.php - Ricerca clienti migliorata
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connessione database fallita']);
    exit;
}

// Imposta charset per evitare problemi con caratteri speciali
$conn->set_charset("utf8");

$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$cognome = isset($_GET['cognome']) ? trim($_GET['cognome']) : '';

// Costruisci query di ricerca più flessibile
$where = [];
$params = [];

if (!empty($nome)) {
    $where[] = "(nome LIKE ? OR nome LIKE ?)";
    $params[] = $nome . '%';  // Inizia con
    $params[] = '%' . $nome . '%';  // Contiene
}

if (!empty($cognome)) {
    $where[] = "(cognome LIKE ? OR cognome LIKE ?)";
    $params[] = $cognome . '%';  // Inizia con
    $params[] = '%' . $cognome . '%';  // Contiene
}

if (empty($where)) {
    echo json_encode([]);
    exit;
}

// Query con prepared statement per sicurezza
$sql = "SELECT id_cliente, nome, cognome, cellulare, email, note, 
               DATE(MAX(p.data_res)) as ultima_prenotazione,
               COUNT(p.id_prenotazioni) as num_prenotazioni
        FROM clienti c 
        LEFT JOIN prenotazioni p ON c.id_cliente = p.id_cliente
        WHERE " . implode(' AND ', $where) . "
        GROUP BY c.id_cliente 
        ORDER BY 
            -- Priorità: corrispondenza esatta nome e cognome
            CASE 
                WHEN LOWER(nome) = LOWER(?) AND LOWER(cognome) = LOWER(?) THEN 1
                WHEN LOWER(nome) LIKE LOWER(?) AND LOWER(cognome) LIKE LOWER(?) THEN 2
                ELSE 3
            END,
            -- Poi per numero di prenotazioni (clienti più frequenti)
            num_prenotazioni DESC,
            -- Infine per ultima prenotazione
            ultima_prenotazione DESC,
            cognome, nome
        LIMIT 8";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Errore preparazione query']);
    exit;
}

// Aggiungi parametri per l'ordinamento
$params[] = $nome;
$params[] = $cognome;
$params[] = $nome . '%';
$params[] = $cognome . '%';

// Crea stringa dei tipi per bind_param
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$clienti = [];
while ($row = $result->fetch_assoc()) {
    // Formatta i dati per la risposta
    $cliente = [
        'id_cliente' => $row['id_cliente'],
        'nome' => $row['nome'],
        'cognome' => $row['cognome'],
        'cellulare' => $row['cellulare'] ?: '',
        'email' => $row['email'] ?: '',
        'note' => $row['note'] ?: '',
        'num_prenotazioni' => intval($row['num_prenotazioni']),
        'ultima_prenotazione' => $row['ultima_prenotazione']
    ];
    
    $clienti[] = $cliente;
}

$stmt->close();
$conn->close();

// Restituisci risultati in JSON
echo json_encode($clienti, JSON_UNESCAPED_UNICODE);
?>