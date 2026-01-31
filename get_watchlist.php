
<?php
session start();
require 'db.php';



if(!isset($_SESSION['user_id'])){
    exit();
}
$idutente=$_SESSION['user_id'];

$sql="
    SELECT content_id,tipo_content,titolo, poster_path, data_aggiunta
    FROM watchlist
    WHERE user_id = $1
    
";

//faccio una query al database 
$result=pg_query_params($db,$sql,array($idutente));
if(!$result){
    exit;
}
//creo array dei film della watch list 
$wlist=array();
 //riempio l'array dei film della watchlist sulla base degli elementi contenuti nell'array result
while($row=pg_fetch_asoc($result)){
    $wlist[]=$row;
}

header('Content-Type: application/json');
echo json_encode($wlist);