<?php

session_start();

require 'db.php';

if(!isset($_SESSION['username'])){
    exit();
}
$idutente=$_SESSION['username'];

$sql="
    SELECT content_id,tipo_content,titolo, poster_path, data_aggiunta
    FROM preferiti
    WHERE user_id = $1
    
";

//faccio una query al database 
$result=pg_query_params($db,$sql,array($idutente));
if(!$result){
    exit;
}
//creo array dei preferiti 
$preferiti=array();

while($row=pg_fetch_assoc($result)){
    $preferiti[]=$row;
}

header('Content-Type: application/json');
echo json_encode($preferiti);