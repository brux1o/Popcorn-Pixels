
<?php 
session_start(); 

require 'config.php';

function fetchFromTMDb($endpoint){
    $url=TMDB_BASE_URL.'/'.$endpoint.'?api_key=' . api_key . '&language=' . TMDB_LANGUAGE . '&page=1';
    $risposta=file_get_contents($url); //prendo le cose che si trovano a quell'url
    return json_decode($risposta,true); //ritorno i dati presi
    }

$recenti=fetchFromTMDb('movie/now_playing'); //uscite recenti 
$filmPopolari=fetchFromTMDb('movie/popular'); //film più popolari 
$seriePopolari=fetchFromTMDb('tv/popular'); //serie tv più popolari 

header('Content-Type: application/json'); //cambia l'intestazione della risposta HTTP così che il browser sa che il contenuto che segue è JSON
echo json_encode([
'recenti'=>$recenti['results'],
'filmPopolari'=>$filmPopolari['results'],
'seriePopolari'=>$seriePopolari['results'],
'user'=>isset($_SESSION['username'])? $_SESSION['username']:null
//controllo se l'utente ha fatto il login ,se sì resitituisce il nome utente sennò restituisce null
]);