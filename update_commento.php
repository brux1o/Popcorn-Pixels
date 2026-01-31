
<?php 

session_start();

require 'db.php';

if(!isset($_SESSION['user_id'])){
    exit();
}
$idutente=$_SESSION['user_id'];

//CONTROLLO CHE JAVASCRIPT ABBIA MANDATO DEI DATI
if(!isset($_POST['id']) || !isset($_POST['testo']))
    exit();

$id_commento=$_POST['id'];
$nuovo_testo=$_POST['testo'];

$sql="
   UPDATE commenti
   SET testo=$1
   WHERE id=$2 AND id_utente=$3
  
";
$result=pg_query_params($db,$sql,array($nuovo_testo,$id_commento,$idutente));
if(!$result){
    echo'ERRORE';
}
else echo 'OK';