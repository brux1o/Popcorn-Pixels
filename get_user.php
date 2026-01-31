
<?php



session_start();
//connessione al database 
require 'db.php';
//controllo il login
if(!isset($_SESSION['user_id']))
    {
       exit;
    }
$idutente=$_SESSION['user_id'];

$sql = "
    SELECT  nome, cognome, email, username
    FROM utente
    WHERE id = $1
";
//faccio una query al database 
$result=pg_query_params($db,$sql,array($idutente));
if(!$result){
    exit;
}
//recupero la riga della tabella 
$utente=pg_fetch_assoc($result);

if(!$utente){
    exit;
}

header('Content-Type: application/json');
echo json_encode($utente);