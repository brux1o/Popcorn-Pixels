<?php
session_start();
// Includiamo la connessione centralizzata
require_once 'db.php';

// Variabili di stato
$step = 'search'; 
$error_msg = "";
$success_msg = "";
$input_sticky = ""; // Per riempire il campo se c'è un errore

// Se l'utente ha già superato il primo step, lo recuperiamo dalla sessione
if (isset($_SESSION['reset_id']) && isset($_SESSION['reset_domanda'])) {
    $step = 'reset';
}

// --- LOGICA STEP 1: CERCA UTENTE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'search') {
    $input_sticky = trim($_POST['input_user']);
    
    if (empty($input_sticky)) {
        $error_msg = "Inserisci username o email.";
    } else {
        // Usa $conn che arriva da db.php
        $sql = "SELECT id, domanda_sicurezza FROM utente WHERE email = $1 OR username = $1";
        $res = pg_query_params($conn, $sql, array($input_sticky));
        
        if ($res && pg_num_rows($res) > 0) {
            $u = pg_fetch_assoc($res);
            $_SESSION['reset_id'] = $u['id'];
            $_SESSION['reset_domanda'] = $u['domanda_sicurezza'];
            
            // Ricarica la pagina per passare allo step successivo pulito
            header("Location: recupero.php");
            exit;
        } else {
            $error_msg = "Nessun account trovato.";
        }
    }
}

// --- LOGICA STEP 2: RESET PASSWORD ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'reset') {
    $ans = trim($_POST['answer']);
    $new_pass = $_POST['new_pass'];
    $uid = $_SESSION['reset_id'];

    // Validazione base
    if (empty($ans) || empty($new_pass)) {
        $error_msg = "Compila tutti i campi.";
    } elseif (strlen($new_pass) < 8) {
        $error_msg = "La password deve essere di almeno 8 caratteri.";
    } else {
        // Recupera la risposta hashata dal DB
        $sql_u = "SELECT risposta_sicurezza FROM utente WHERE id = $1";
        $res_u = pg_query_params($conn, $sql_u, array($uid));
        $real_ans_hash = pg_fetch_result($res_u, 0, 0);

        // VERIFICA LA RISPOSTA (Usa password_verify!)
        if (password_verify($ans, $real_ans_hash)) {
            // Crea il nuovo hash per la password
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            
            // Aggiorna
            $sql_upd = "UPDATE utente SET password = $1 WHERE id = $2";
            if (pg_query_params($conn, $sql_upd, array($new_hash, $uid))) {
                // Pulizia e redirect
                unset($_SESSION['reset_id']);
                unset($_SESSION['reset_domanda']);
                $_SESSION['msg_flash'] = "Password aggiornata! Accedi ora.";
                header("Location: accesso.php");
                exit();
            } else {
                $error_msg = "Errore nell'aggiornamento del database.";
            }
        } else {
            $error_msg = "Risposta di sicurezza errata.";
        }
    }
}
?>

<?php include 'header.php'; ?>

<main class="main-container auth-page">
    
    <?php if ($error_msg): ?>
        <div class="alert error" style="background:#ffcccc; color:#900; padding:10px; margin-bottom:15px; text-align:center;">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <div class="auth-wrapper" style="max-width: 500px; margin: 0 auto;">
        
        <div id="step-search" style="display: <?php echo ($step === 'search') ? 'block' : 'none'; ?>;">
            <div class="auth-header" style="text-align: center; margin-bottom: 20px;">
                <h2>Recupero Password</h2>
                <p>Inserisci i tuoi dati per iniziare</p>
            </div>
            
            <form action="recupero.php" method="POST" onsubmit="return validateRecupero()">
                <input type="hidden" name="action" value="search">
                
                <label>Username o Email</label>
                <input type="text" name="input_user" value="<?= htmlspecialchars($input_sticky) ?>" required>
                
                <button type="submit" class="btn-submit" style="margin-top: 15px;">CERCA</button>
            </form>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="accesso.php">Annulla</a>
            </div>
        </div>

        <div id="step-reset" style="display: <?php echo ($step === 'reset') ? 'block' : 'none'; ?>;">
            <div class="auth-header" style="text-align: center; margin-bottom: 20px;">
                <h2>Verifica Sicurezza</h2>
            </div>
            
            <form action="recupero.php" method="POST" onsubmit="return validateReset()">
                <input type="hidden" name="action" value="reset">
                
                <div class="form-group" style="background: #eee; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <strong>Domanda:</strong> 
                    <?php 
                        $d = $_SESSION['reset_domanda'] ?? '';
                        // Mappatura leggibile delle domande (coerente con accesso.php)
                        if($d == 'animale') echo "Nome del primo animale domestico?";
                        elseif($d == 'madre') echo "Cognome da nubile di tua madre?";
                        elseif($d == 'citta') echo "Città di nascita?";
                        elseif($d == 'scuola') echo "Nome scuola elementare?";
                        else echo htmlspecialchars($d); 
                    ?>
                </div>

                <label>La tua risposta</label>
                <input type="text" name="answer" placeholder="Rispondi qui..." required>
                
                <label>Nuova Password</label>
                <input type="password" name="new_pass" placeholder="Minimo 8 caratteri" required>

                <button type="submit" class="btn-submit" style="margin-top: 15px;">SALVA NUOVA PASSWORD</button>
            </form>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="accesso.php">Annulla</a>
            </div>
        </div>

    </div>
</main>

<script src="validation.js"></script>
<?php include 'footer.php'; ?>