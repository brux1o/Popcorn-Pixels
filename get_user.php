<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode([]);
    exit();
}

$username_session = $_SESSION['username'];

$sql = "SELECT nome, cognome, email, username, immagine_profilo
        FROM utente
        WHERE username = $1";

$result = pg_query_params($db, $sql, array($username_session));

if (!$result) {
    echo json_encode([]);
    exit;
}

$utente = pg_fetch_assoc($result);

if (!$utente) {
    echo json_encode([]);
    exit;
}

echo json_encode($utente);
?>