<?php
include("header.php");
include("db.php");

$step = 1;
$errore = "";
$user_confermato = "";
$domanda_testo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["verifica_user"])) {
        $u = trim($_POST["username_input"]);
        $stmt = $conn->prepare("SELECT username, domanda FROM utenti WHERE username = :u");
        $stmt->execute([':u' => $u]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            $user_confermato = $res['username'];
            $domanda_testo = $res['domanda'];
            $step = 2;
        } else { $errore = "Utente non trovato."; }
    }

    if (isset($_POST["verifica_risp"])) {
        $user_confermato = $_POST["user_hidden"];
        $domanda_testo = $_POST["domanda_hidden"]; // Passata via hidden per non perderla
        $risposta = trim($_POST["risposta_input"]);

        $stmt = $conn->prepare("SELECT risposta FROM utenti WHERE username = :u");
        $stmt->execute([':u' => $user_confermato]);
        $db_risp = $stmt->fetchColumn();

        if ($db_risp === $risposta) {
            $_SESSION['auth_reset'] = $user_confermato;
            header("Location: reset_password.php");
            exit;
        } else {
            $errore = "Risposta errata.";
            $step = 2;
        }
    }
}
?>

<main class="page-wrapper">
    <h1 class="page-title">Recupero</h1>

    <?php if ($step == 1): ?>
        <form method="POST">
            <label>Inserisci Username</label>
            <input type="text" name="username_input" required>
            <button type="submit" name="verifica_user">Trova Account</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" value="<?= htmlspecialchars($user_confermato) ?>" readonly style="background:#222; color:#888;">
            <input type="hidden" name="user_hidden" value="<?= htmlspecialchars($user_confermato) ?>">
            <input type="hidden" name="domanda_hidden" value="<?= htmlspecialchars($domanda_testo) ?>">

            <p style="margin: 20px 0; color: #d4a017;">Domanda: <strong><?= htmlspecialchars($domanda_testo) ?></strong></p>

            <label>Risposta</label>
            <input type="text" name="risposta_input" required autofocus>
            <button type="submit" name="verifica_risp">Verifica</button>
        </form>
    <?php endif; ?>
</main>

<?php include("footer.php"); ?>