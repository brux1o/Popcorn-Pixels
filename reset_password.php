<?php
session_start();
require_once 'db.php';

// Se l'utente non è passato da recupero.php, lo rimandiamo indietro
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: recupero.php");
    exit();
}

$errore = "";
$domanda = $_SESSION['reset_domanda']; // Recuperata nella fase precedente

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $risposta_utente = $_POST['risposta'] ?? '';
    $codice_backup = $_POST['codice_backup'] ?? '';
    $user_id = $_SESSION['reset_user_id'];

    // 1. Controllo tramite Risposta alla Domanda
    if (!empty($risposta_utente)) {
        $sql = "SELECT risposta_sicurezza FROM utenti WHERE id = $1";
        $result = pg_query_params($db, $sql, array($user_id));
        $db_risposta = pg_fetch_result($result, 0, 0);

        // Confronto case-insensitive (ignorando maiuscole/minuscole) per UX
        if (strtolower(trim($risposta_utente)) === strtolower(trim($db_risposta))) {
            $_SESSION['autorizzato_reset'] = true;
            header("Location: reset_finale.php");
            exit();
        } else {
            $errore = "Risposta errata.";
        }
    } 
    // 2. Controllo tramite Codice di Backup (Il tuo tocco originale)
    elseif (!empty($codice_backup)) {
        $sql = "SELECT id, codice_hash FROM codici_backup WHERE utente_id = $1 AND usato = FALSE";
        $result = pg_query_params($db, $sql, array($user_id));
        
        $valid_code = false;
        while ($row = pg_fetch_assoc($result)) {
            if (password_verify($codice_backup, $row['codice_hash'])) {
                // "Bruciamo" il codice: un codice di backup si usa una sola volta!
                pg_query_params($db, "UPDATE codici_backup SET usato = TRUE WHERE id = $1", array($row['id']));
                $valid_code = true;
                break;
            }
        }

        if ($valid_code) {
            $_SESSION['autorizzato_reset'] = true;
            header("Location: reset_finale.php");
            exit();
        } else {
            $errore = "Codice di backup non valido o già utilizzato.";
        }
    } else {
        $errore = "Compila almeno uno dei due campi.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Recupero - Cinema Project</title>
    <link rel="stylesheet" href="stile/accesso.css">
    <link rel="icon" type="image/png" href="resources/icona.png">
    <script src="stile/validation.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-center"><img src="resources/icona.png" class="nav-logo"></div>
            <div class="nav-right"><a href="login.php" class="nav-button">Indietro</a></div>
        </nav>
    </header>

    <main>
        <div class="page-wrapper">
            <h1 class="page-title">Sicurezza</h1>
            <h2 class="page-subtitle">Ultimo passaggio di verifica</h2>

            <?php if ($errore): ?>
                <div class="alert alert-error" style="color: #ff4d4d; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($errore) ?></div>
            <?php endif; ?>

            <form action="verifica_domanda.php" method="POST" onsubmit="return validateSecurity()">
                <label style="color: #d4a017;">La tua domanda:</label>
                <p style="margin-bottom: 10px; font-style: italic;">"<?= htmlspecialchars($domanda) ?>?"</p>
                <input type="text" name="risposta" placeholder="Inserisci la risposta...">

                <div style="text-align: center; margin: 20px 0; color: #555;">— OPPURE —</div>

                <label>Usa un codice di backup</label>
                <input type="text" name="codice_backup" placeholder="Codice a 8 caratteri">

                <button type="submit">Verifica e Continua</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php ("footer.php"); ?>