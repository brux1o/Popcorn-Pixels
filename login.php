<?php
include("header.php");
include("db.php");

$errore = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recupero e pulizia dei dati
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";

    // --- CONTROLLO SU ENTRAMBI I CAMPI ---
    if (empty($username) && empty($password)) {
        $errore = "Inserisci username e password.";
    } elseif (empty($username)) {
        $errore = "Il campo username è obbligatorio.";
    } elseif (empty($password)) {
        $errore = "Il campo password è obbligatorio.";
    } else {
        // Procediamo con la ricerca nel database solo se entrambi i campi sono compilati
        $stmt = $conn->prepare("SELECT * FROM utenti WHERE username = :username");
        $stmt->execute([":username" => $username]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$utente) {
            $errore = "Username non trovato.";
        } else {
            if (password_verify($password, $utente["password"])) {
                $_SESSION["user_id"] = $utente["id"];
                $_SESSION["user_id"] = $utente["id"];
                $_SESSION["username"] = $utente["username"];
                $_SESSION["domanda"] = $utente["domanda"];
                $_SESSION["risposta"] = $utente["risposta"]; // Carichiamo la nuova colonna
                header("Location: account.php");
                exit;
                header("Location: account.php");
                exit;
            } else {
                $errore = "Password errata.";
            }
        }
    }
}
?>

<div class="page-wrapper">
    <h1 class="page-title">Login</h1>
    <h2 class="page-subtitle">Accedi al tuo account</h2>
    
    <?php if ($errore): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" onsubmit="return validateLogin()">
        <label>Username</label>
        <input type="text" name="username" id="login_username" value="<?= htmlspecialchars($username ?? '') ?>">

        <label>Password</label>
        <input type="password" name="password" id="login_password" required>

        <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
            <a href="recupero.php" class="forgot-link">Password dimenticata?</a>
        </div>

        <button type="submit">Accedi</button>
        
        <div class="form-footer">
            <p>Non hai un account? <a href="register.php">Registrati</a></p>
        </div>
    </form>
</div>

