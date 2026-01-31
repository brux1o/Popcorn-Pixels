<?php
require_once __DIR__ . '/database.php'; 

session_start();
header('Content-Type: application/json'); 

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Devi essere loggato per eseguire questa azione.']);
    exit;
}

$userId = $_SESSION['username'];

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
    

    $stmt_del = pg_prepare($db, "delete_swap", $query_delete);
    $result_del = pg_execute($db, "delete_swap", array($userId, $titoloDaRimuovere));


    if (pg_affected_rows($result_del) == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Non ho trovato nessun film con questo titolo esatto nei tuoi preferiti.'
        ]);
        exit;
    }

    $query_insert = "INSERT INTO preferiti (username, content_id, tipo_content, titolo, poster_path) 
                     VALUES ($1, $2, $3, $4, $5)";
    
    $stmt_ins = pg_prepare($db, "insert_swap", $query_insert);
    $result_ins = pg_execute($db, "insert_swap", array($userId, $contentId, $contentType, $titolo, $poster));

    if ($result_ins) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore durante l\'inserimento del nuovo film.']);
    }
    exit;
}


$query_count = "SELECT COUNT(*) FROM preferiti WHERE user_id = $1";
$stmt_count = pg_prepare($db, "check_count", $query_count);
$result_count = pg_execute($db, "check_count", array($userId));


$num_preferiti = pg_fetch_result($result_count, 0, 0);


if ($num_preferiti < 5) {

    $query_insert = "INSERT INTO preferiti (username, content_id, tipo_content, titolo, poster_path) 
                     VALUES ($1, $2, $3, $4, $5)";
                     
    $stmt_ins = pg_prepare($db, "insert_fav", $query_insert);
    
    $result = @pg_execute($db, "insert_fav", array($userId, $contentId, $contentType, $titolo, $poster));

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Questo film è già nei tuoi preferiti!']);
    }

} else {
    echo json_encode(['success' => false, 'code' => 'limite_raggiunto']);
}
?>