<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['username'])){
    echo json_encode([]);
    exit();
}

$username_session = $_SESSION['username'];

$query_user = "SELECT id FROM utente WHERE username = $1";
$res_user = pg_query_params($db, $query_user, array($username_session));

if (!$res_user || pg_num_rows($res_user) === 0) {
    echo json_encode([]);
    exit;
}

$row_user = pg_fetch_assoc($res_user);
$real_user_id = $row_user['id'];

$sql = "SELECT content_id, tipo_content, titolo, poster_path, data_aggiunta
        FROM watchlist
        WHERE user_id = $1
        ORDER BY data_aggiunta DESC";

$result = pg_query_params($db, $sql, array($real_user_id));

if(!$result){
    echo json_encode([]);
    exit;
}

$wlist = array();

while($row = pg_fetch_assoc($result)){
    $wlist[] = $row;
}

echo json_encode($wlist);
?>