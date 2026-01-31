<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: struttura.html");
    exit();
}
require_once 'db.php';

// Variabili sticky
$nome = $_POST['nome'] ?? '';
$cognome = $_POST['cognome'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$domanda = $_POST['domanda_sicurezza'] ?? '';
$errore = "";
$backup_codes = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_raw = $_POST['password'] ?? '';
    $risposta = $_POST['risposta_sicurezza'] ?? '';
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO utenti (nome, cognome, email, username, password, domanda_sicurezza, risposta_sicurezza) 
            VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id";
    
    $result = pg_query_params($db, $sql, array($nome, $cognome, $email, $username, $password_hash, $domanda, $risposta));

    if ($result) {
        $utente_id = pg_fetch_row($result)[0];
        for ($i = 0; $i < 3; $i++) {
            $code = bin2hex(random_bytes(4)); 
            $backup_codes[] = $code;
            pg_query_params($db, "INSERT INTO codici_backup (utente_id, codice_hash) VALUES ($1, $2)", 
                           array($utente_id, password_hash($code, PASSWORD_DEFAULT)));
        }
    } else {
        $errore = "Username o Email già esistenti.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
    <link rel="stylesheet" href="stile/accesso.css">
    <link rel="icon" type="image/png" href="resources/icona.png">
    <script src="stile/validation.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-center"><img src="resources/icona.png" class="nav-logo"></div>
            <div class="nav-right"><a href="reset_password.php" class="nav-button">Indietro</a></div>
        </nav>
    </header>

    <main>
        <div class="page-wrapper">
            <?php if (!empty($backup_codes)): ?>
                <div class="backup-section" style="text-align: center;">
                    <h1 class="page-title">Fatto!</h1>
                    <p>Copia questi codici di emergenza:</p>
                    <div style="background: #222; padding: 20px; border: 1px solid #d4a017; margin: 20px 0; color: #d4a017; font-family: monospace; font-size: 20px;">
                        <?php foreach($backup_codes as $c) echo "<div>$c</div>"; ?>
                    </div>
                    <a href="login.php" class="nav-button">Vai al Login</a>
                </div>
            <?php else: ?>
                <h1 class="page-title">Registrazione</h1>
                <h2 class="page-subtitle">Crea un account</h2>

                <?php if ($errore): ?>
                    <div class="alert alert-error" style="color: #ff4d4d; text-align: center;"><?= $errore ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST" onsubmit="return validateRegister()">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Cognome</label>
                            <input type="text" name="cognome" value="<?= htmlspecialchars($cognome) ?>" required>
                        </div>
                    </div>

                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>

                    <label>Password</label>
                    <input type="password" name="password" required>

                    <label>Domanda di Sicurezza</label>
                    <select name="domanda_sicurezza">
                        <option value="Città di nascita" <?= $domanda == 'Città di nascita' ? 'selected' : '' ?>>Città di nascita?</option>
                        <option value="Nome animale" <?= $domanda == 'Nome animale' ? 'selected' : '' ?>>Nome del tuo primo animale?</option>
                    </select>

                    <label>Risposta</label>
                    <input type="text" name="risposta_sicurezza" required>

                    <button type="submit">Registrati</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    <?php include("footer.php"); ?>
</body>
</html>