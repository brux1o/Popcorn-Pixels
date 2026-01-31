<?php
require_once 'db.php'; 

session_start();
header('Content-Type: application/json'); 

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Devi essere loggato per eseguire questa azione.']);
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

$contentId = $_POST['id'] ?? null;
$contentType = $_POST['type'] ?? null;
$titolo = $_POST['titolo'] ?? 'Titolo Sconosciuto';
$poster = $_POST['poster'] ?? ''; 

$action = $_POST['action'] ?? 'add'; 
$titoloDaRimuovere = $_POST['titolo_da_rimuovere'] ?? '';

if (!$contentId || !$contentType) {
    echo json_encode(['success' => false, 'message' => 'Dati del film mancanti.']);
    exit;
}

if ($action === 'scambio') {

    if (empty($titoloDaRimuovere)) {
        echo json_encode(['success' => false, 'message' => 'Nessun titolo da rimuovere specificato.']);
        exit;
    }

    $query_delete = "DELETE FROM preferiti WHERE user_id = $1 AND titolo = $2";
    $result_del = pg_query_params($db, $query_delete, array($real_user_id, $titoloDaRimuovere));

    if (pg_affected_rows($result_del) == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Non ho trovato nessun film con questo titolo esatto nei tuoi preferiti.'
        ]);
        exit;
    }

    $query_insert = "INSERT INTO preferiti (user_id, content_id, tipo_content, titolo, poster_path) 
                     VALUES ($1, $2, $3, $4, $5)";
    
    $result_ins = pg_query_params($db, $query_insert, array($real_user_id, $contentId, $contentType, $titolo, $poster));

    if ($result_ins) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore durante l\'inserimento del nuovo film.']);
    }
    exit;
}

$query_count = "SELECT COUNT(*) FROM preferiti WHERE user_id = $1";
$result_count = pg_query_params($db, $query_count, array($real_user_id));
$num_preferiti = pg_fetch_result($result_count, 0, 0);

if ($num_preferiti < 5) {

    $query_insert = "INSERT INTO preferiti (user_id, content_id, tipo_content, titolo, poster_path) 
                     VALUES ($1, $2, $3, $4, $5)";
                     
    $result = @pg_query_params($db, $query_insert, array($real_user_id, $contentId, $contentType, $titolo, $poster));

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Questo film è già nei tuoi preferiti!']);
    }

} else {
    echo json_encode(['success' => false, 'code' => 'limite_raggiunto']);
}
?>