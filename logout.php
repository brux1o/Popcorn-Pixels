<?php
session_start();

// Elimina tutte le variabili di sessione
session_unset();

// Distrugge la sessione
session_destroy();

// Reindirizza al login
header("Location: login.php");
exit;