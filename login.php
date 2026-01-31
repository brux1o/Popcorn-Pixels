<?php
session_start();

// 1. Il "Buttafuori": se sei già loggato, vai alla home
if (isset($_SESSION['user_id'])) {
    header("Location: struttura.html");
    exit();
}

require_once 'db.php';

// Gestione form sticky per l'identificativo (username o email)
$identificativo = $_POST['identificativo'] ?? '';
$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_raw = $_POST['password'] ?? '';

    // 2. Accesso Ibrido: cerchiamo sia per username che per email
    $sql = "SELECT id, username, password FROM utenti WHERE username = $1 OR email = $1";
    $result = pg_query_params($db, $sql, array($identificativo));

    if ($result && pg_num_rows($result) > 0) {
        $user_data = pg_fetch_assoc($result);
        
        if (password_verify($password_raw, $user_data['password'])) {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            
            header("Location: struttura.html"); 
            exit();
        } else {
            $errore = "Password errata. Riprova.";
        }
    } else {
        $errore = "Account non trovato.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - Cinema Project</title>
    <link rel="stylesheet" href="stile/accesso.css">
    <link rel="icon" type="image/png" href="sources/icona.png">
    <script src="stile/validation.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="struttura.html" class="nav-button">Home</a>
            </div>
            
            <div class="nav-center">
                <img src="sources/icona.png" alt="Logo" class="nav-logo">
            </div>

        </nav>
    </header>

    <main>
        <div class="page-wrapper">
            <h1 class="page-title">Login</h1>
            <h2 class="page-subtitle">Accedi al tuo account</h2>
            
            <?php if ($errore): ?>
                <div class="alert alert-error" style="color: #ff4d4d; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($errore) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" onsubmit="return validateLogin()">
                <label>Username o Email</label>
                <input type="text" name="identificativo" id="login_username" value="<?= htmlspecialchars($identificativo) ?>" required>

                <label>Password</label>
                <input type="password" name="password" id="login_password" required>

                <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
                    <a href="recupero.php" class="forgot-link" style="color: #888; font-size: 13px; text-decoration: none;">Password dimenticata?</a>
                </div>

                <button type="submit">Accedi</button>
                
                <div class="form-footer">
                    <p>Non hai un account? <a href="register.php" style="color: #d4a017; text-decoration: none; font-weight: bold;">Registrati</a></p>
                </div>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>