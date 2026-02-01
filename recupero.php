<?php
session_start();
require_once 'db.php';

$step = 'search'; 
if (isset($_SESSION['reset_id'])) $step = 'reset'; 

$err = "";
$input_sticky = $_POST['input_user'] ?? '';

// 1. CERCA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'search') {
    $input = trim($_POST['input_user']);
    $sql = "SELECT id, domanda_sicurezza FROM utente WHERE email = $1 OR username = $1";
    $res = @pg_query_params($db, $sql, array($input));
    
    if ($res && pg_num_rows($res) > 0) {
        $u = pg_fetch_assoc($res);
        $_SESSION['reset_id'] = $u['id'];
        $_SESSION['reset_domanda'] = $u['domanda_sicurezza'];
        $step = 'reset'; 
    } else {
        $err = "Nessun account trovato.";
    }
}

// 2. RESET
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'reset') {
    $code = $_POST['code'] ?? '';
    $ans = $_POST['answer'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    $uid = $_SESSION['reset_id'];

    $sql_u = "SELECT risposta_sicurezza FROM utente WHERE id = $1";
    $res_u = pg_query_params($db, $sql_u, array($uid));
    $real_ans = pg_fetch_result($res_u, 0, 0);

    if ($ans === $real_ans) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        pg_query_params($db, "UPDATE utente SET password = $1 WHERE id = $2", array($hash, $uid));
        unset($_SESSION['reset_id']);
        unset($_SESSION['reset_domanda']);
        header("Location: accesso.php?reset=ok");
        exit();
    } else {
        $err = "Risposta di sicurezza errata.";
        $step = 'reset';
    }
}

$mostra_freccia = true;
include 'header.php';
?>

    <div id="step-search" class="auth-section <?php echo ($step === 'search') ? 'active' : ''; ?>">
        <div class="auth-header">
            <h1>RECUPERO</h1>
            <h3>Trova il tuo account</h3>
        </div>
        <div class="form-container">
            <?php if ($err && $step==='search'): ?><div class="alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
            
            <form action="recupero.php" method="POST" onsubmit="return validateRecupero()">
                <input type="hidden" name="action" value="search">
                <div class="form-group">
                    <label>Username o Email</label>
                    <input type="text" name="input_user" value="<?= htmlspecialchars($input_sticky) ?>" required>
                </div>
                <button type="submit" class="btn-submit">CERCA</button>
            </form>
        </div>
    </div>

    <div id="step-reset" class="auth-section <?php echo ($step === 'reset') ? 'active' : ''; ?>">
        <div class="auth-header">
            <h1>SICUREZZA</h1>
            <h3>Verifica identità</h3>
        </div>
        <div class="form-container">
            <?php if ($err && $step==='reset'): ?><div class="alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

            <form action="recupero.php" method="POST" onsubmit="return validateReset()">
                <input type="hidden" name="action" value="reset">
                
                <div class="form-group">
                    <label>Codice Backup (3 cifre)</label>
                    <input type="text" name="code" required maxlength="3">
                </div>
                <div class="form-group">
                    <label>Domanda: <?= htmlspecialchars($_SESSION['reset_domanda'] ?? '') ?></label>
                    <input type="text" name="answer" placeholder="La tua risposta" required>
                </div>
                <div class="form-group">
                    <label>Nuova Password</label>
                    <input type="password" name="new_pass" required>
                </div>

                <button type="submit" class="btn-submit">CAMBIA PASSWORD</button>
            </form>
            <a href="accesso.php" style="display:block; margin-top:20px; font-size:0.9rem;">Annulla</a>
        </div>
    </div>

<?php include 'footer.php'; ?>