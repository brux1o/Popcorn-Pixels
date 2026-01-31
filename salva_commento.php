<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['username'])) {
    
    $id_utente = $_SESSION['username'];
    $id_contenuto = $_POST['id_contenuto'];
    $tipo = $_POST['tipo'];

    $titolo = isset($_POST['titolo']) ? $_POST['titolo'] : 'Titolo Sconosciuto';
    
    $testo = trim($_POST['testo_commento']);

    if (!empty($testo)) {
        $sql = "INSERT INTO commenti (username, id_contenuto, tipo_contenuto, titolo, testo) 
                VALUES ($1, $2, $3, $4, $5)";
        
        $prep = pg_prepare($db, "insert_comm", $sql);
        
        $res = pg_execute($db, "insert_comm", array($id_utente, $id_contenuto, $tipo, $titolo, $testo));

        if ($res) {
            header("Location: /dettaglio.php?id=$id_contenuto&type=$tipo");
            exit;
        } else {
            echo "Errore nel salvataggio: " . pg_last_error($db);
        }
    }
} else {
    header("Location: /ricerca.php");
}
?>