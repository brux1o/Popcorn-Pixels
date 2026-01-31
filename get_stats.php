

<?php

session_start();
require 'db.php';

/* controllo login */
if (!isset($_SESSION['user_id'])) {
    exit();
}

$idutente = $_SESSION['user_id'];

/* ===== CONTA PREFERITI ===== */
$sql_preferiti = "
    SELECT COUNT(*) AS totale
    FROM preferiti
    WHERE user_id = $1
";
$res_pref = pg_query_params($db, $sql_preferiti, array($idutente));
$pref = pg_fetch_assoc($res_pref);

/* ===== CONTA WATCHLIST ===== */
$sql_watchlist = "
    SELECT COUNT(*) AS totale
    FROM watchlist
    WHERE user_id = $1
";
$res_watch = pg_query_params($db, $sql_watchlist, array($idutente));
$watch = pg_fetch_assoc($res_watch);

/* ===== CONTA COMMENTI ===== */
$sql_commenti = "
    SELECT COUNT(*) AS totale
    FROM commenti
    WHERE id_utente = $1
";
$res_comm = pg_query_params($db, $sql_commenti, array($idutente));
$comm = pg_fetch_assoc($res_comm);

/* ===== RISPOSTA JSON ===== */
$stats = array(
    'preferiti' => (int)$pref['totale'],
    'watchlist' => (int)$watch['totale'],
    'commenti'  => (int)$comm['totale']
);

header('Content-Type: application/json');
echo json_encode($stats);