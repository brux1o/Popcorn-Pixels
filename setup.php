<?php
require_once 'config.php';

echo "<h1>Setup Blog Film</h1>";

// Connessione a PostgreSQL
$conn_string = "host=" . DB_HOST .
               " port=" . DB_PORT .
               " dbname=" . DB_NAME .
               " user=" . DB_USER .
               " password=" . DB_PASS;

$db = pg_connect($conn_string);

if (!$db) {
    die("<p style='color:red;'>Errore di connessione al database</p>");
}

// Carica il file SQL
$sql = file_get_contents('database.sql');
if ($sql === false) {
    die("<p style='color:red;'>Impossibile leggere database.sql</p>");
}

// Esegue lo script SQL
$result = pg_query($db, $sql);
if (!$result) {
    die("<p style='color:red;'>Errore durante l'esecuzione dello script SQL</p>");
}

echo "<p style='color:green; font-weight:bold;'>
        ✅ Database inizializzato correttamente
      </p>";

echo "<p>
        <a href='index.php'>Vai al sito</a>
      </p>";