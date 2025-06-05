<?php
// modifica_ombrellone.php
$conn = new mysqli('localhost', 'root', '', 'spiaggia');
if ($conn->connect_error) {
    die('Connessione fallita: ' . $conn->connect_error);
}

$id_ombrellone = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$oggi = date('Y-m-d');
$prenotazione_passata = ($data < $oggi);

// Recupera dati ombrellone
$sql = "SELECT * FROM ombrelloni WHERE id_ombrellone = $id_ombrellone";
$res = $conn->query($sql);
$ombrellone = $res->fetch_assoc();

// Recupera prenotazione per la data
$sql2 = "SELECT p.*, c.nome, c.cognome, c.cellulare, c.email, c.note FROM prenotazioni p LEFT JOIN clienti c ON p.id_cliente = c.id_cliente WHERE p.id_ombrellone = $id_ombrellone AND p.data_inizio <= '$data' AND p.data_fine >= '$data'";
$res2 = $conn->query($sql2);
$prenotazione = $res2->fetch_assoc();

// Gestione cancellazione prenotazione
if (isset($_GET['action']) && $_GET['action'] == 'cancella' && $prenotazione) {
    $sql_del = "DELETE FROM prenotazioni WHERE id_prenotazioni = " . $prenotazione['id_prenotazioni'];
    $conn->query($sql_del);
    header("Location: modifica_ombrellone.php?id=$id_ombrellone&data=$data&msg=" . urlencode('Prenotazione cancellata!'));
    exit;
}

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

    // Validazione date
    if (strtotime($data_fine) < strtotime($data_inizio)) {
        $msg = 'ERRORE: La data di fine non può essere precedente alla data di inizio!';
    } else {
        // Verifica conflitti con altre prenotazioni
        $sql_check = "SELECT COUNT(*) as conflitti FROM prenotazioni WHERE id_ombrellone = $id_ombrellone AND ((data_inizio <= '$data_fine' AND data_fine >= '$data_inizio'))";
        if ($prenotazione) {
            $sql_check .= " AND id_prenotazioni != " . $prenotazione['id_prenotazioni'];
        }
        $res_check = $conn->query($sql_check);
        $check = $res_check->fetch_assoc();
        
        if ($check['conflitti'] > 0) {
            $msg = 'ERRORE: Esistono già prenotazioni in conflitto per queste date!';
        } else {
            // Cerca cliente esistente (migliorato)
            $sql_cli = "SELECT * FROM clienti WHERE LOWER(TRIM(nome)) = LOWER('".$conn->real_escape_string($nome)."') AND LOWER(TRIM(cognome)) = LOWER('".$conn->real_escape_string($cognome)."') LIMIT 1";
            $res_cli = $conn->query($sql_cli);
            
            if ($res_cli && $res_cli->num_rows > 0) {
                $cli = $res_cli->fetch_assoc();
                $id_cliente = $cli['id_cliente'];
                // Aggiorna dati cliente se diversi
                $sql_upd = "UPDATE clienti SET cellulare='".$conn->real_escape_string($cellulare)."', email='".$conn->real_escape_string($email)."', note='".$conn->real_escape_string($note)."' WHERE id_cliente=$id_cliente";
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
                $msg = 'Prenotazione aggiornata con successo!';
            } else {
                // Nuova prenotazione
                $sql_ins = "INSERT INTO prenotazioni (id_cliente, id_ombrellone, data_res, data_inizio, data_fine) VALUES ($id_cliente, $id_ombrellone, NOW(), '$data_inizio', '$data_fine')";
                $conn->query($sql_ins);
                $msg = 'Nuova prenotazione inserita con successo!';
            }
            header("Location: modifica_ombrellone.php?id=$id_ombrellone&data=$data&msg=" . urlencode($msg));
            exit;
        }
    }
}
if (isset($_GET['msg'])) $msg = $_GET['msg'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Ombrellone</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>

    </style>
</head>
<body>
    <div class="form-box">
        <h2>🏖️ Ombrellone n°<?php echo $ombrellone['id_ombrellone']; ?> (<?php echo $ombrellone['fila']; ?> fila)</h2>
        <?php if ($prenotazione_passata): ?>
            <div class="error-msg">Non è possibile inserire o modificare prenotazioni per date antecedenti a oggi (<?php echo date('d/m/Y'); ?>).</div>
            <p style="text-align:center;margin-top:30px;"><a href="piantina_ombrelloni.php?data=<?php echo urlencode($oggi); ?>">Torna alla data odierna</a></p>
        <?php else: ?>
        
        <?php if (strpos($msg, 'ERRORE') !== false): ?>
            <div class="error-msg"><?php echo htmlspecialchars($msg); ?></div>
        <?php elseif ($msg): ?>
            <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        
        <?php if ($prenotazione): ?>
            <div class="prenotazione-info">
                <h3>🏄‍♂️ Attualmente prenotato</h3>
                <div class="cliente-details">
                    <span><strong>Cliente:</strong> <?php echo htmlspecialchars($prenotazione['nome'] . ' ' . $prenotazione['cognome']); ?></span>
                    <span><strong>Cellulare:</strong> <?php echo htmlspecialchars($prenotazione['cellulare']); ?></span>
                    <span><strong>Email:</strong> <?php echo htmlspecialchars($prenotazione['email']); ?></span>
                    <span><strong>Note:</strong> <?php echo htmlspecialchars($prenotazione['note']); ?></span>
                    <span><strong>Dal:</strong> <?php echo htmlspecialchars($prenotazione['data_inizio']); ?></span>
                    <span><strong>Al:</strong> <?php echo htmlspecialchars($prenotazione['data_fine']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="post" autocomplete="off">
            <div class="autocomplete-box">
                <label>🧑‍🤝‍🧑 Nome cliente:</label>
                <input type="text" name="nome" id="nome" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['nome']) : ''; ?>" required placeholder="Inserisci il nome...">
                <div class="loading-spinner" id="loading-spinner"></div>
                
                <label>👤 Cognome cliente:</label>
                <input type="text" name="cognome" id="cognome" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['cognome']) : ''; ?>" required placeholder="Inserisci il cognome...">
                
                <div id="autocomplete-list" class="autocomplete-list" style="display:none;"></div>
                <div id="search-status" class="search-indicator" style="display:none;"></div>
            </div>
            
            <label>📱 Cellulare:</label>
            <input type="tel" name="cellulare" id="cellulare" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['cellulare']) : ''; ?>" placeholder="Es: 3331234567">
            
            <label>📧 Email:</label>
            <input type="email" name="email" id="email" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['email']) : ''; ?>" placeholder="Es: mario.rossi@email.com">
            
            <label>📝 Note:</label>
            <input type="text" name="note" id="note" value="<?php echo $prenotazione ? htmlspecialchars($prenotazione['note']) : ''; ?>" placeholder="Note aggiuntive (opzionale)">
            
            <label>📅 Data inizio:</label>
            <input type="date" name="data_inizio" value="<?php echo $prenotazione ? $prenotazione['data_inizio'] : $data; ?>" required>
            
            <label>📅 Data fine:</label>
            <input type="date" name="data_fine" value="<?php echo $prenotazione ? $prenotazione['data_fine'] : $data; ?>" required>
            
            <button class="btn" type="submit">
                <?php echo $prenotazione ? '✏️ Modifica Prenotazione' : '➕ Nuova Prenotazione'; ?>
            </button>
            
            <?php if ($prenotazione && $prenotazione['data_fine'] >= date('Y-m-d')): ?>
                <a href="modifica_ombrellone.php?id=
                <?php echo $id_ombrellone; ?>&data=<?php echo urlencode($data); ?>&action=cancella" 
                   class="btn btn-danger" 
                   onclick="return confirm('Sei sicuro di voler cancellare questa prenotazione?')">
                   🗑️ Cancella Prenotazione
                </a>
            <?php endif; ?>
        </form>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="piantina_ombrelloni.php?data=<?php echo urlencode($data); ?>">← Torna alla piantina</a>
        </p>
    </div>
    
    <script>
    $(function(){
        let searchTimeout;
        
        function searchClient() {
    const nome = $("#nome").val().trim();
    const cognome = $("#cognome").val().trim();

    if (nome.length < 1 && cognome.length < 1) {
        $("#autocomplete-list").hide(); 
        $("#search-status").hide();
        return;
    }

    $("#loading-spinner").show();
    $("#search-status").show().text("🔍 Ricerca in corso...");

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        $.ajax({
            url: "cerca_clienti.php",
            method: "POST",
            data: { nome, cognome },
            success: function(data) {
                $("#loading-spinner").hide();
                $("#autocomplete-list").html(data).show();
                $("#search-status").text("✅ Risultati trovati");
            },
            error: function() {
                $("#loading-spinner").hide();
                $("#autocomplete-list").hide();
                $("#search-status").text("❌ Errore nella ricerca");
            }
        });
    }, 300); // Debounce di 300ms
}

// Attiva la ricerca al cambio di nome o cognome
$("#nome, #cognome").on("input", searchClient);

// Click su un risultato
$(document).on("click", ".autocomplete-item", function() {
    const nome = $(this).data("nome");
    const cognome = $(this).data("cognome");
    const cellulare = $(this).data("cellulare");
    const email = $(this).data("email");
    const note = $(this).data("note");

    $("#nome").val(nome);
    $("#cognome").val(cognome);
    $("#cellulare").val(cellulare);
    $("#email").val(email);
    $("#note").val(note);

    $("#autocomplete-list").hide();
    $("#search-status").text("✔️ Cliente selezionato");
});
        
        // Ricerca con debounce per evitare troppe chiamate
        $("#nome, #cognome").on('input', function(){
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchClient, 300);
        });
        
        // Seleziona cliente dalla lista
        $(document).on('click', '.autocomplete-item', function(){
            $("#nome").val($(this).data('nome'));
            $("#cognome").val($(this).data('cognome'));
            $("#cellulare").val($(this).data('cellulare'));
            $("#email").val($(this).data('email'));
            $("#note").val($(this).data('note'));
            $("#autocomplete-list").hide();
            $("#search-status").text("✅ Cliente selezionato");
            
            // Aggiungi un effetto visivo
            $(".autocomplete-box").addClass("selected");
            setTimeout(function() {
                $(".autocomplete-box").removeClass("selected");
            }, 1000);
        });
        
        // Chiudi lista quando si clicca fuori
        $(document).click(function(e){
            if(!$(e.target).closest('.autocomplete-box').length) {
                $("#autocomplete-list").hide();
            }
        });
        
        // Validazione form
        $("form").on('submit', function(e) {
            const dataInizio = new Date($("input[name='data_inizio']").val());
            const dataFine = new Date($("input[name='data_fine']").val());
            
            if (dataFine < dataInizio) {
                e.preventDefault();
                alert("⚠️ La data di fine non può essere precedente alla data di inizio!");
                return false;
            }
        });
    });
    </script>
    
</body>
</html>