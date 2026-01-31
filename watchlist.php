<?php
    require_once __DIR__ . '/database.php';

    session_start();
    header('Content-Type: application/json');

    if(!isset($_SESSION['utente_id'])){
        echo json_encode(['success' => false, 'message' => 'Non loggato']);
        exit;
    }

    $userId = $_SESSION['utente_id'];
    $contentId = $_POST['id'] ?? '';
    $contentType = $_POST['type'] ?? '';
    $titolo = $_POST['titolo'] ?? 'Senza Titolo';
    $poster = $_POST['poster'] ?? '';

    $query = "INSERT INTO watchlist (user_id, content_id, tipo_content, titolo, poster_path) 
              VALUES ($1, $2, $3, $4, $5)";
              
    $prep = pg_prepare($db, "add_wl", $query);
    
    if(!$prep) {
        echo json_encode(['success' => false, 'message' => 'Errore tecnico (prepare)']);
        exit;
    }

    $result = @pg_execute($db, "add_wl", array($userId, $contentId, $contentType, $titolo, $poster));

    if($result){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Elemento già presente nella watchlist!']);
    }
?>