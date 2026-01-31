<?php
    $stato_testo = "Offline";
    $stato_colore = "#FF3333"; 
    $nome_utente = "Ospite";
    $loggato = false;

    if(isset($_SESSION['utente_id'])){
        $stato_testo = "Online";
        $stato_colore = "#00FF00"; 
        $nome_utente = $_SESSION['username']; 
        $loggato = true;
    }
?>