<?php 

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    exit();
}

$idutente = $_SESSION['username'];

/* controllo dati dal JS */
if (!isset($_POST['id'])) {
    exit();
}

$watchlist_id = $_POST['id'];

/* query delete */
$sql = "
    DELETE FROM watchlist
    WHERE id = $1 AND id_utente = $2
";

$result = pg_query_params(
    $db,
    $sql,
    array($watchlist_id, $idutente)
);

if ($result) {
    echo 'OK';
} else {
    echo 'ERRORE';
}