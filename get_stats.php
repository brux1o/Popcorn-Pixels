<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['preferiti' => 0, 'watchlist' => 0, 'commenti' => 0]);
    exit();
}

$username_session = $_SESSION['username'];

$query_user = "SELECT id FROM utente WHERE username = $1";
$res_user = pg_query_params($db, $query_user, array($username_session));

if (!$res_user || pg_num_rows($res_user) === 0) {
    echo json_encode(['preferiti' => 0, 'watchlist' => 0, 'commenti' => 0]);
    exit();
}

$row_user = pg_fetch_assoc($res_user);
$real_user_id = $row_user['id'];

$sql_preferiti = "SELECT COUNT(*) AS totale FROM preferiti WHERE user_id = $1";
$res_pref = pg_query_params($db, $sql_preferiti, array($real_user_id));
$pref = pg_fetch_assoc($res_pref);

$sql_watchlist = "SELECT COUNT(*) AS totale FROM watchlist WHERE user_id = $1";
$res_watch = pg_query_params($db, $sql_watchlist, array($real_user_id));
$watch = pg_fetch_assoc($res_watch);

$sql_commenti = "SELECT COUNT(*) AS totale FROM commenti WHERE id_utente = $1";
$res_comm = pg_query_params($db, $sql_commenti, array($real_user_id));
$comm = pg_fetch_assoc($res_comm);

$stats = array(
    'preferiti' => (int)$pref['totale'],
    'watchlist' => (int)$watch['totale'],
    'commenti'  => (int)$comm['totale']
);

echo json_encode($stats);
?>