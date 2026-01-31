<?php
include("header.php");
include("db.php");

$errore = "";
$successo = "";
$mostra_form_reset = false;

// 1. Verifica della risposta alla domanda di sicurezza
if (isset($_POST['risposta_sicurezza']) && isset($_SESSION['recupero_user'])) {
    $risposta_utente = trim($_POST['risposta_sicurezza']);
    $username = $_SESSION['recupero_user'];

    $stmt = $conn->prepare("SELECT domanda FROM utenti WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Confronto della risposta (colonna 'domanda' nel tuo DB)
    if ($utente && $utente['domanda'] === $risposta_utente) {
        $mostra_form_reset = true;
    } else {
        $errore = "Risposta alla domanda di sicurezza errata.";
    }
}

// 2. Elaborazione dell'aggiornamento password
if (isset($_POST['nuova_password']) && isset($_SESSION['recupero_user'])) {
    $nuova_pass = password_hash($_POST['nuova_password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE utenti SET password = :p WHERE username = :u");
    if ($stmt->execute([':p' => $nuova_pass, ':u' => $_SESSION['recupero_user']])) {
        $successo = "Password aggiornata correttamente!";
        unset($_SESSION['recupero_user']);
        unset($_SESSION['recupero_domanda']);
    } else {
        $errore = "Si è verificato un errore durante l'aggiornamento.";
    }
}
?>

<div class="page-wrapper">
    <h1 class="page-title">Reset Password</h1>

    <?php if ($errore): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errore) ?></div>
        <div class="form-footer"><a href="recupero.php">Torna al recupero</a></div>
    <?php endif; ?>

    <?php if ($successo): ?>
        <div class="alert alert-success"><?= $successo ?></div>
        <div class="form-footer"><a href="login.php">Accedi ora</a></div>
    <?php endif; ?>

    <?php if ($mostra_form_reset && !$successo): ?>
        <form method="POST" onsubmit="return validateReset()">
            <label>Nuova Password</label>
            <input type="password" name="nuova_password" id="new_password" required>
            <button type="submit">Conferma Nuova Password</button>
        </form>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>