<?php
session_start();
// Se l'utente è già loggato, va alla dashboard
if (isset($_SESSION['user_id'])) { header("Location: struttura.html"); exit(); }
require_once 'db.php';

// Configurazione Tab Iniziale
$current_view = 'login'; 

// Variabili Errore e Sticky
$err_login = "";
$err_reg = "";
$backup_codes = [];

// Dati Sticky (Input)
$log_user = $_POST['log_user'] ?? '';
$reg_nome = $_POST['reg_nome'] ?? '';
$reg_cognome = $_POST['reg_cognome'] ?? '';
$reg_email = $_POST['reg_email'] ?? '';
$reg_user = $_POST['reg_user'] ?? '';
$reg_domanda = $_POST['reg_domanda'] ?? '';

// --- LOGICA LOGIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    $current_view = 'login';
    $pass = $_POST['log_pass'] ?? '';
    
    if (empty($log_user) || empty($pass)) {
        $err_login = "Inserisci username e password.";
    } else {
        $sql = "SELECT id, username, password, immagine_profilo FROM utente WHERE username = $1 OR email = $1";
        $res = @pg_query_params($db, $sql, array($log_user));
        if ($res && pg_num_rows($res) > 0) {
            $u = pg_fetch_assoc($res);
            if (password_verify($pass, $u['password'])) {
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['username'] = $u['username'];
                $_SESSION['immagine_profilo'] = $u['immagine_profilo'];
                header("Location: struttura.html");
                exit();
            } else { $err_login = "Password errata."; }
        } else { $err_login = "Utente non trovato."; }
    }
}

// --- LOGICA REGISTRAZIONE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'register') {
    $current_view = 'register'; 
    $pass = $_POST['reg_pass'] ?? '';
    $risp = $_POST['reg_risposta'] ?? '';
    $foto = 'resources/utente.png';

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO utente (nome, cognome, email, username, password, domanda_sicurezza, risposta_sicurezza, immagine_profilo) 
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id";
    $params = array($reg_nome, $reg_cognome, $reg_email, $reg_user, $hash, $reg_domanda, $risp, $foto);
    $res = @pg_query_params($db, $sql, $params);

    if ($res) {
        $uid = pg_fetch_row($res)[0];
        // Genera codici
        for ($i=0; $i<3; $i++) {
            $c = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $backup_codes[] = $c;
            pg_query_params($db, "INSERT INTO codici_backup (utente_id, codice_hash) VALUES ($1, $2)", array($uid, password_hash($c, PASSWORD_DEFAULT)));
        }
        $current_view = 'codes';
    } else {
        $err_reg = "Username o Email già esistenti.";
    }
}

include 'header.php';
?>

    <div id="view-login" class="auth-section <?php echo ($current_view === 'login') ? 'active' : ''; ?>">
        <div class="auth-header">
            <h1>BENTORNATO</h1>
            <h3>Accedi al tuo account</h3>
        </div>
        <div class="form-container">
            <?php if ($err_login): ?><div class="alert-error"><?= htmlspecialchars($err_login) ?></div><?php endif; ?>
            <?php if (isset($_GET['reset']) && $_GET['reset']=='ok'): ?><div class="alert" style="color:#0f0; border-color:#0f0;">Password aggiornata!</div><?php endif; ?>

            <form action="accesso.php" method="POST" onsubmit="return validateLogin()">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Username o Email</label>
                    <input type="text" name="log_user" value="<?= htmlspecialchars($log_user) ?>" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="log_pass" required>
                </div>
                <button type="submit" class="btn-submit">ACCEDI</button>
            </form>

            <div style="margin-top: 25px;">
                <p style="color: #ccc; margin-bottom:10px;">
                    Non hai un account? <span class="switch-link" onclick="switchView('register')">Registrati ora</span>
                </p>
                <p><a href="recupero.php" class="switch-link" style="color:#888; font-weight:normal;">Password dimenticata?</a></p>
                 
            </div>
        </div>
    </div>

    <div id="view-register" class="auth-section <?php echo ($current_view === 'register') ? 'active' : ''; ?>">
        <div class="auth-header">
            <h1>REGISTRAZIONE</h1>
            <h3>Crea un nuovo account</h3>
        </div>
        <div class="form-container">
            <?php if ($err_reg): ?><div class="alert-error"><?= htmlspecialchars($err_reg) ?></div><?php endif; ?>

            <form action="accesso.php" method="POST" enctype="multipart/form-data" onsubmit="return validateRegister()">
                <input type="hidden" name="action" value="register">
                <div class="form-group"><label>Nome</label><input type="text" name="reg_nome" value="<?= htmlspecialchars($reg_nome) ?>" required></div>
                <div class="form-group"><label>Cognome</label><input type="text" name="reg_cognome" value="<?= htmlspecialchars($reg_cognome) ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="reg_email" value="<?= htmlspecialchars($reg_email) ?>" required></div>
                <div class="form-group"><label>Username</label><input type="text" name="reg_user" value="<?= htmlspecialchars($reg_user) ?>" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="reg_pass" required></div>
                
                <div class="form-group">
                    <label>Domanda Sicurezza</label>
                    <select name="reg_domanda">
                        <option value="Città">Città di nascita?</option>
                        <option value="Animale">Nome animale domestico?</option>
                    </select>
                </div>
                <div class="form-group"><label>Risposta</label><input type="text" name="reg_risposta" required></div>

                <button type="submit" class="btn-submit">REGISTRATI</button>
            </form>
            <p style="margin-top:20px; color:#ccc;">Hai già un account? <span class="switch-link" onclick="switchView('login')">Accedi</span></p>
        </div>
    </div>

    <div id="view-codes" class="auth-section <?php echo ($current_view === 'codes') ? 'active' : ''; ?>">
        <div class="auth-header">
            <h1>COMPLETATO!</h1>
            <h3>Salva i tuoi codici</h3>
        </div>
        <div class="form-container">
            <div class="backup-box">
                <?php foreach($backup_codes as $c) echo "<span>$c</span>"; ?>
            </div>
            <button class="btn-submit" onclick="switchView('login')">VAI AL LOGIN</button>
        </div>
    </div>

    <script>
        function switchView(viewName) {
            document.querySelectorAll('.auth-section').forEach(el => el.classList.remove('active'));
            document.getElementById('view-' + viewName).classList.add('active');
            
            const btn = document.getElementById('btn-back');
            if(viewName === 'login') btn.classList.remove('visible');
            else btn.classList.add('visible');
        }
        <?php if ($current_view === 'register') echo "document.getElementById('btn-back').classList.add('visible');"; ?>
    </script>

<?php include 'footer.php'; ?>