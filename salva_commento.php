<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['username'])) {

    $username_session = $_SESSION['username'];
    
    $query_user = "SELECT id FROM utente WHERE username = $1";
    $res_user = pg_query_params($db, $query_user, array($username_session));
    
    if (!$res_user || pg_num_rows($res_user) === 0) {
        die("Errore critico: Utente non trovato nel database.");
    }
    
    $row = pg_fetch_assoc($res_user);
    $id_utente_reale = $row['id'];

    $id_contenuto = $_POST['id_contenuto'];
    $tipo = $_POST['tipo'];
    $titolo = isset($_POST['titolo']) ? $_POST['titolo'] : 'Titolo Sconosciuto';
    $testo = trim($_POST['testo_commento']);

    if (!empty($testo)) {
        
        $sql = "INSERT INTO commenti (id_utente, id_contenuto, tipo_contenuto, titolo, testo) 
                VALUES ($1, $2, $3, $4, $5)";
        
        $res = pg_query_params($db, $sql, array(
            $id_utente_reale, 
            $id_contenuto, 
            $tipo, 
            $titolo, 
            $testo
        ));

        if ($res) {
            header("Location: dettaglio.php?id=$id_contenuto&type=$tipo");
            exit;
        } else {
            echo "Errore nel salvataggio: " . pg_last_error($db);
        }
    } else {
        echo "Errore: Il commento non può essere vuoto.";
    }
} else {
    header("Location: ricerca.php");
    exit;
}
?>