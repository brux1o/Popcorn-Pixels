<?php
require_once __DIR__ . '/db.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Devi effettuare il login.']);
    exit;
}

$username_session = $_SESSION['username'];
$query_user = "SELECT id FROM utente WHERE username = $1";
$res_user = pg_query_params($db, $query_user, array($username_session));

if (!$res_user || pg_num_rows($res_user) === 0) {
    echo json_encode(['success' => false, 'message' => 'Utente non trovato nel database.']);
    exit;
}

$row = pg_fetch_assoc($res_user);
$real_user_id = $row['id'];

$contentId = $_POST['id'] ?? '';
$contentType = $_POST['type'] ?? '';
$titolo = $_POST['titolo'] ?? 'Senza Titolo';
$poster = $_POST['poster'] ?? '';

$query = "INSERT INTO watchlist (user_id, content_id, tipo_content, titolo, poster_path) 
          VALUES ($1, $2, $3, $4, $5)";
          
$result = @pg_query_params($db, $query, array(
    $real_user_id, 
    $contentId, 
    $contentType, 
    $titolo, 
    $poster
));

if($result){
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Elemento già presente nella watchlist!']);
}
?>