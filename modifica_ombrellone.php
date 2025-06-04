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

// Gestione inserimento/modifica prenotazione
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $cellulare = trim($_POST['cellulare']);
    $email = trim($_POST['email']);
    $note = trim($_POST['note']);
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];

    // Cerca cliente esistente
    $sql_cli = "SELECT * FROM clienti WHERE nome = '".$conn->real_escape_string($nome)."' AND cognome = '".$conn->real_escape_string($cognome)."' LIMIT 1";
    $res_cli = $conn->query($sql_cli);
    if ($res_cli && $res_cli->num_rows > 0) {
        $cli = $res_cli->fetch_assoc();
        $id_cliente = $cli['id_cliente'];
        // Aggiorna dati se diversi
        $sql_upd = "UPDATE clienti SET cellulare='$cellulare', email='$email', note='$note' WHERE id_cliente=$id_cliente";
        $conn->query($sql_upd);
    } else {
        // Inserisci nuovo cliente
        $sql_ins = "INSERT INTO clienti (nome, cognome, cellulare, email, note) VALUES ('".$conn->real_escape_string($nome)."', '".$conn->real_escape_string($cognome)."', '".$conn->real_escape_string($cellulare)."', '".$conn->real_escape_string($email)."', '".$conn->real_escape_string($note)."')";
        $conn->query($sql_ins);
        $id_cliente = $conn->insert_id;
    }
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .autocomplete-box { position: relative; }
        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #1976D2;
            z-index: 10000;
            max-height: 180px;
            overflow-y: auto;
            border-radius: 0 0 8px 8px;
        }
        .autocomplete-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        .autocomplete-item:hover {
            background: #E3F2FD;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Ombrellone n°<?php echo $ombrellone['id_ombrellone']; ?> (<?php echo $ombrellone['fila']; ?> fila)</h2>
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
        <form method="post" autocomplete="off">
            <div class="autocomplete-box">
                <label>Nome cliente:</label>
                <input type="text" name="nome" id="nome" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['nome']) : ''; ?>" required>
                <label>Cognome cliente:</label>
                <input type="text" name="cognome" id="cognome" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['cognome']) : ''; ?>" required>
                <div id="autocomplete-list" class="autocomplete-list" style="display:none;"></div>
            </div>
            <label>Cellulare:
                <input type="text" name="cellulare" id="cellulare" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['cellulare']) : ''; ?>">
            </label>
            <label>Email:
                <input type="email" name="email" id="email" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['email']) : ''; ?>">
            </label>
            <label>Note:
                <input type="text" name="note" id="note" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['note']) : ''; ?>">
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
    <script>
    $(function(){
        function searchClient() {
            var nome = $("#nome").val();
            var cognome = $("#cognome").val();
            if(nome.length < 1 && cognome.length < 1) { $("#autocomplete-list").hide(); return; }
            $.get('search_cliente.php', { nome: nome, cognome: cognome }, function(data) {
                if(data && data.length > 0) {
                    var list = JSON.parse(data);
                    if(list.length > 0) {
                        var html = '';
                        list.forEach(function(cli) {
                            html += '<div class="autocomplete-item" data-nome="'+cli.nome+'" data-cognome="'+cli.cognome+'" data-cellulare="'+cli.cellulare+'" data-email="'+cli.email+'" data-note="'+cli.note+'">'+cli.nome+' '+cli.cognome+'</div>';
                        });
                        $("#autocomplete-list").html(html).show();
                    } else {
                        $("#autocomplete-list").hide();
                    }
                } else {
                    $("#autocomplete-list").hide();
                }
            });
        }
        $("#nome, #cognome").on('input', searchClient);
        $(document).on('click', '.autocomplete-item', function(){
            $("#nome").val($(this).data('nome'));
            $("#cognome").val($(this).data('cognome'));
            $("#cellulare").val($(this).data('cellulare'));
            $("#email").val($(this).data('email'));
            $("#note").val($(this).data('note'));
            $("#autocomplete-list").hide();
        });
        $(document).click(function(e){
            if(!$(e.target).closest('.autocomplete-box').length) {
                $("#autocomplete-list").hide();
            }
        });
    });
    </script>
</body>
</html>
