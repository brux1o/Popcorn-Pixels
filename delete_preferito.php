<?php 

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$idutente = $_SESSION['user_id'];

/* controllo dati dal JS */
if (!isset($_POST['id'])) {
    exit();
}

$preferito_id = $_POST['id'];

/* query delete */
$sql = "
    DELETE FROM preferiti
    WHERE id = $1 AND id_utente = $2
";

$result = pg_query_params(
    $db,
    $sql,
    array($preferito_id, $idutente)
);

if ($result) {
    echo 'OK';
} else {
    echo 'ERRORE';
}