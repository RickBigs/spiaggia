<?php
// header.php - Navbar principale
$pagina = basename($_SERVER['PHP_SELF']);
?>
<nav style="width:100%;background:#1976D2;padding:12px 0 10px 0;margin-bottom:30px;">
    <div style="text-align:center;">
        <a href="piantina_ombrelloni.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;<?php if($pagina=='piantina_ombrelloni.php') echo 'text-decoration:underline;'; else echo 'text-decoration:none;'; ?>">Piantina Ombrelloni</a>
        <a href="elenco_prenotazioni.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;<?php if($pagina=='elenco_prenotazioni.php') echo 'text-decoration:underline;'; else echo 'text-decoration:none;'; ?>">Elenco Prenotazioni</a>
        <a href="calendario.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;<?php if($pagina=='calendario.php') echo 'text-decoration:underline;'; else echo 'text-decoration:none;'; ?>">Calendario</a>
        <a href="fatturato.php" style="color:#fff;font-weight:bold;font-size:18px;margin:0 20px;<?php if($pagina=='fatturato.php') echo 'text-decoration:underline;'; else echo 'text-decoration:none;'; ?>">Fatturato & Statistiche</a>
    </div>
</nav>
