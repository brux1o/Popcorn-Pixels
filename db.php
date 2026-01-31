
<?php


require_once 'config.php';

$conn_string = "host=" . DB_HOST .
               " port=" . DB_PORT .
               " dbname=" . DB_NAME .
               " user=" . DB_USER .
               " password=" . DB_PASS;

$db = pg_connect($conn_string);

if (!$db) {
    die('Errore di connessione al database');
}