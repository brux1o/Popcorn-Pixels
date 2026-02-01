<?php
session_start();

// Se l'utente è già loggato, lo mandiamo alla home
if (isset($_SESSION['user_id'])) {
    header("Location: struttura.html");
    exit();
}

require_once 'db.php';

$errore = "";
$username_input = ""; // Per sticky form

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = trim($_POST['username'] ?? ''); // Può essere username o email
    $password_input = $_POST['password'] ?? '';

    if (empty($username_input) || empty($password_input)) {
        $errore = "Inserisci credenziali valide.";
    } else {
        // 1. Cerchiamo l'utente per Username OPPURE Email
        // Recuperiamo anche l'immagine_profilo per la sessione
        $sql = "SELECT id, username, password, nome, cognome, immagine_profilo 
                FROM utente 
                WHERE username = $1 OR email = $1";
        
        $result = pg_query_params($db, $sql, array($username_input));

        if ($result && pg_num_rows($result) > 0) {
            $user = pg_fetch_assoc($result);

            // 2. Verifichiamo la password (l'hash salvato nel DB)
            if (password_verify($password_input, $user['password'])) {
                // PASSWORD CORRETTA: Avvio Sessione
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nome_reale'] = $user['nome'] . " " . $user['cognome'];
                $_SESSION['foto_profilo'] = $user['immagine_profilo']; // Salviamo il path della foto

                // Redirect alla Homepage
                header("Location: struttura.html");
                exit();
            } else {
                $errore = "Password non corretta.";
            }
        } else {
            $errore = "Nessun account trovato con questo nome utente/email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Accedi - Popcorn & Pixels</title>
    <link rel="stylesheet" href="stile/accesso.css">
    <link rel="icon" type="image/png" href="resources/icona.png">
    <script src="stile/validation.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left"></div>

            <div class="nav-center">
                <a href="struttura.html" title="Torna alla Homepage">
                    <img src="resources/icona.png" class="nav-logo" alt="Logo">
                </a>
            </div>

            <div class="nav-right"></div>
        </nav>
    </header>

    <main>
        <div class="page-wrapper">
            <h1 class="page-title">Bentornato</h1>
            <h2 class="page-subtitle">Accedi al tuo account</h2>

            <?php if ($errore): ?>
                <div class="alert alert-error" style="color: #ff4d4d; border: 1px solid #ff4d4d; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                    <?= htmlspecialchars($errore) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" onsubmit="return validateLogin()"> <label>Username o Email</label>
                <input type="text" name="username" value="<?= htmlspecialchars($username_input) ?>" required placeholder="Inserisci username o email">

                <label>Password</label>
                <input type="password" name="password" required placeholder="La tua password">

                <button type="submit">Accedi</button>
            </form>

            <div style="margin-top: 20px; text-align: center; font-size: 14px;">
                <p style="margin-bottom: 10px;">
                    Non hai un account? <a href="register.php" style="color: #d4a017;">Registrati ora</a>
                </p>
                <p>
                    <a href="recupero.php" style="color: #888; text-decoration: none;">Hai dimenticato la password?</a>
                </p>
            </div>
        </div>
    </main>

    <?php include("footer.php"); ?>
</body>
</html>