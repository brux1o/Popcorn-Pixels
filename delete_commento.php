<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['username'])) {
    exit();
}

$username_session = $_SESSION['username'];

$query_user = "SELECT id FROM utente WHERE username = $1";
$res_user = pg_query_params($db, $query_user, array($username_session));

if (!$res_user || pg_num_rows($res_user) === 0) {
    exit();
}

$row_user = pg_fetch_assoc($res_user);
$real_user_id = $row_user['id'];

if (!isset($_POST['id'])) {
    exit();
}

$id_commento = $_POST['id'];

$sql = "DELETE FROM commenti WHERE id = $1 AND id_utente = $2";

$result = pg_query_params($db, $sql, array($id_commento, $real_user_id));

if ($result && pg_affected_rows($result) > 0) {
    echo 'OK';
} else {
    echo 'ERRORE';
}
?>