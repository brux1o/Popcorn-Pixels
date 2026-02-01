<?php
session_start();
require_once 'db.php';

// SICUREZZA: Se l'utente non ha passato lo step 1 (recupero.php), lo buttiamo fuori.
if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_domanda'])) {
    header("Location: recupero.php");
    exit();
}

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $risposta_utente = trim($_POST['risposta_sicurezza'] ?? '');
    $nuova_password = $_POST['nuova_password'] ?? '';
    $user_id = $_SESSION['reset_user_id'];

    if (empty($risposta_utente) || empty($nuova_password)) {
        $errore = "Compila tutti i campi.";
    } else {
        // 1. Recuperiamo la risposta VERA dal DB
        $sql = "SELECT risposta_sicurezza FROM utente WHERE id = $1";
        $result = pg_query_params($db, $sql, array($user_id));
        
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            
            // 2. Confrontiamo la risposta data con quella nel DB
            // (Case insensitive: 'Fido' è uguale a 'fido')
            if (strtolower($risposta_utente) === strtolower($row['risposta_sicurezza'])) {
                
                // 3. Risposta corretta: Aggiorniamo la Password
                $hash_new_pass = password_hash($nuova_password, PASSWORD_DEFAULT);
                $update = "UPDATE utente SET password = $1 WHERE id = $2";
                
                if (pg_query_params($db, $update, array($hash_new_pass, $user_id))) {
                    // Pulizia sessione e redirect
                    unset($_SESSION['reset_user_id']);
                    unset($_SESSION['reset_domanda']);
                    header("Location: login.php?status=password_reset");
                    exit();
                } else {
                    $errore = "Errore tecnico nell'aggiornamento.";
                }
            } else {
                $errore = "Risposta di sicurezza errata.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Resetta Password</title>
    <link rel="stylesheet" href="stile/accesso.css">
    <link rel="icon" type="image/png" href="resources/icona.png">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="recupero.php" class="nav-button">Indietro</a>
            </div>
            <div class="nav-center">
                <a href="struttura.html"><img src="resources/icona.png" class="nav-logo" alt="Logo"></a>
            </div>
            <div class="nav-right"></div>
        </nav>
    </header>

    <main>
        <div class="page-wrapper">
            <h1 class="page-title">Sicurezza</h1>
            <h2 class="page-subtitle">Rispondi per cambiare password</h2>
            
            <?php if ($errore): ?>
                <div class="alert alert-error" style="color: #ff4d4d; border: 1px solid #ff4d4d; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?= htmlspecialchars($errore) ?>
                </div>
            <?php endif; ?>

            <form action="reset_password.php" method="POST">
                <div style="background: #333; padding: 15px; border-left: 4px solid #d4a017; margin-bottom: 20px; color: #fff;">
                    <label style="color: #d4a017; font-size: 12px;">Domanda di Sicurezza:</label>
                    <div style="font-size: 18px; font-style: italic;">
                        <?= htmlspecialchars($_SESSION['reset_domanda']) ?>
                    </div>
                </div>

                <label>La tua Risposta</label>
                <input type="text" name="risposta_sicurezza" required autocomplete="off">
                
                <label>Nuova Password</label>
                <input type="password" name="nuova_password" required minlength="4">
                
                <button type="submit">Cambia Password</button>
            </form>
        </div>
    </main>

    <?php include("footer.php"); ?>
</body>
</html>