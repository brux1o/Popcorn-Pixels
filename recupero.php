<?php
session_start();
require_once 'db.php';

// Controllo di sicurezza: se l'utente non ha superato la fase 2, lo cacciamo
if (!isset($_SESSION['autorizzato_reset']) || !isset($_SESSION['reset_user_id'])) {
    header("Location: recupero.php");
    exit();
}

$errore = "";
$successo = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pass1 = $_POST['n_password'] ?? '';
    $pass2 = $_POST['c_password'] ?? '';

    if ($pass1 !== $pass2) {
        $errore = "Le password non coincidono.";
    } elseif (strlen($pass1) < 6) {
        $errore = "La password deve contenere almeno 6 caratteri.";
    } else {
        // Hash della nuova password
        $password_hash = password_hash($pass1, PASSWORD_DEFAULT);
        $user_id = $_SESSION['reset_user_id'];

        // Aggiornamento sul database
        $sql = "UPDATE utenti SET password = $1 WHERE id = $2";
        $result = pg_query_params($db, $sql, array($password_hash, $user_id));

        if ($result) {
            $successo = true;
            // Puliamo la sessione per sicurezza: il processo è finito
            session_destroy(); 
        } else {
            $errore = "Errore durante l'aggiornamento. Riprova.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Nuova Password </title>
    <link rel="stylesheet" href="stile/accesso.css">
    <link rel="icon" type="image/png" href="sources/icona.png">
    <script src="stile/validation.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-center"><img src="sources/icona.png" class="nav-logo"></div>
            <div class="nav-right">
                <a href="login.php" class="nav-button">Indietro</a>
            </div>
        </nav>
    </header>

    <main>
    <div class="page-wrapper">
        <h1 class="page-title">Nuova Password</h1>
        
        <?php if ($successo): ?>
            <div style="text-align: center;">
                <h2 class="page-subtitle" style="color: #4CAF50;">✅ Password aggiornata!</h2>
                <p>Ora puoi tornare alla pagina di login e accedere con le tue nuove credenziali.</p>
                <br>
                <a href="login.php" class="nav-button">Torna al Login</a>
            </div>
        <?php else: ?>
            <h2 class="page-subtitle">Inserisci le nuove credenziali</h2>

            <?php if ($errore): ?>
                <div class="alert alert-error" style="color: #ff4d4d; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($errore) ?>
                </div>
            <?php endif; ?>

            <form action="reset_finale.php" method="POST" onsubmit="return validateReset()">
                <label>Nuova Password</label>
                <input type="password" name="n_password" required placeholder="Minimo 6 caratteri">

                <label>Conferma Nuova Password</label>
                <input type="password" name="c_password" required>

                <button type="submit">Aggiorna Password</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>

<?php include("footer.php"); ?>