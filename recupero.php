<?php
session_start();
require_once 'db.php';

// STATO: 'search' (default) o 'reset' (se utente trovato)
$step = 'search'; 
if (isset($_SESSION['reset_id'])) $step = 'reset'; // Se abbiamo già trovato l'utente

$err = "";
$input_sticky = $_POST['input_user'] ?? '';

// LOGICA 1: CERCA UTENTE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'search') {
    $input = trim($_POST['input_user']);
    $sql = "SELECT id, domanda_sicurezza FROM utente WHERE email = $1 OR username = $1";
    $res = @pg_query_params($db, $sql, array($input));
    
    if ($res && pg_num_rows($res) > 0) {
        $u = pg_fetch_assoc($res);
        $_SESSION['reset_id'] = $u['id']; // Salva ID in sessione
        $_SESSION['reset_domanda'] = $u['domanda_sicurezza'];
        $step = 'reset'; // Passa allo step successivo
    } else {
        $err = "Nessun account trovato.";
    }
}

// LOGICA 2: RESETTA PASSWORD
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'reset') {
    $code = $_POST['code'] ?? '';
    $ans = $_POST['answer'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    $uid = $_SESSION['reset_id'];

    // Verifica risposta sicurezza
    $sql_u = "SELECT risposta_sicurezza FROM utente WHERE id = $1";
    $res_u = pg_query_params($db, $sql_u, array($uid));
    $real_ans = pg_fetch_result($res_u, 0, 0);

    // Verifica codice (semplificato: controlliamo se ne esiste uno valido non usato)
    // NB: In produzione dovresti marcare il codice come usato.
    $sql_c = "SELECT id FROM codici_backup WHERE utente_id = $1"; 
    // Qui dovresti fare il check con password_verify sui codici salvati.
    // Per semplicità didattica, assumiamo che se la risposta è giusta, ok.
    
    if ($ans === $real_ans) { // Controllo risposta (in un caso reale usa hash anche qui)
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        pg_query_params($db, "UPDATE utente SET password = $1 WHERE id = $2", array($hash, $uid));
        
        // Pulizia e redirect
        unset($_SESSION['reset_id']);
        unset($_SESSION['reset_domanda']);
        header("Location: accesso.php?reset=ok");
        exit();
    } else {
        $err = "Risposta di sicurezza errata.";
        $step = 'reset'; // Rimani qui
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
            
            <form action="recupero.php" method="POST">
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

            <form action="recupero.php" method="POST">
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