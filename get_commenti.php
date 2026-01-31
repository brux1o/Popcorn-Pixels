<?php

session_start();

require 'db.php';

if(!isset($_SESSION['username'])){
    exit();
}
$idutente=$_SESSION['username'];

$sql="
    SELECT id,id_contenuto,titolo,tipo_contenuto,testo,data_inserimento
    FROM commenti
    WHERE id_utente= $1
    ORDER BY data_inserimento DESC
    
";

$result=pg_query_params($db,$sql,array($idutente));
if(!$result){
    exit;
}
//creo array dei commenti
$commenti=array();

while($row=pg_fetch_assoc($result)){
    $commenti[]=$row;
}

header('Content-Type: application/json');
echo json_encode($commenti);