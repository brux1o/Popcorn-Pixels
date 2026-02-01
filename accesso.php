<?php
session_start();

// --- 1. CONFIGURAZIONE DATABASE ---
require_once 'db.php'; 


// --- 2. GESTIONE MESSAGGI ---
$error_msg = "";
$success_msg = "";
if (isset($_SESSION['msg_flash'])) {
    $success_msg = $_SESSION['msg_flash'];
    unset($_SESSION['msg_flash']); 
}

// --- 3. VARIABILI STICKY ---
$nome_val = $cognome_val = $email_val = $username_val = $domanda_val = "";
$login_input_val = "";
$show_register = false; 

// --- 4. REGISTRAZIONE ---
if (isset($_POST['btn_register'])) {
    $show_register = true; 
    
    $nome_val = htmlspecialchars(trim($_POST['reg_nome']));
    $cognome_val = htmlspecialchars(trim($_POST['reg_cognome']));
    $email_val = trim($_POST['reg_email']);
    $username_val = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $domanda_val = $_POST['reg_domanda'];
    $risposta = trim($_POST['reg_risposta']);

    if (empty($nome_val) || empty($cognome_val) || empty($email_val) || empty($username_val) || empty($password) || empty($risposta)) {
        $error_msg = "Compila tutti i campi obbligatori.";
    } elseif (strlen($password) < 8) {
        $error_msg = "La password deve essere almeno di 8 caratteri.";
    } else {
        $check_query = "SELECT id FROM utente WHERE email=$1 OR username=$2";
        $res_check = pg_query_params($db, $check_query, array($email_val, $username_val));

        if (pg_num_rows($res_check) > 0) {
            $error_msg = "Username o Email già presenti nel sistema.";
        } else {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $risp_hash = password_hash($risposta, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO utente (nome, cognome, email, username, password, domanda_sicurezza, risposta_sicurezza) VALUES ($1,$2,$3,$4,$5,$6,$7)";
            $res_ins = pg_query_params($db, $insert_query, array($nome_val, $cognome_val, $email_val, $username_val, $pass_hash, $domanda_val, $risp_hash));
            
            if ($res_ins) {
                $success_msg = "Registrazione completata! Ora puoi accedere.";
                $show_register = false; 
                $nome_val = $cognome_val = $email_val = $username_val = $domanda_val = "";
            } else {
                $error_msg = "Errore durante la registrazione.";
            }
        }
    }
}

// --- 5. LOGIN (QUI AVVIENE IL "COLLEGAMENTO" DEL BOTTONE) ---
if (isset($_POST['btn_login'])) { // <--- Questo cattura il click sul bottone 'ACCEDI'
    $login_input_val = trim($_POST['login_input']);
    $password = $_POST['login_password'];
    
    if (empty($login_input_val) || empty($password)) {
        $error_msg = "Inserisci username/email e password.";
    } else {
        $login_query = "SELECT * FROM utente WHERE username=$1 OR email=$1";
        $res_login = pg_query_params($db, $login_query, array($login_input_val));
        $user_row = pg_fetch_assoc($res_login);
        
        // Se la password è corretta...
        if ($user_row && password_verify($password, $user_row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_row['id'];
            $_SESSION['username'] = $user_row['username'];
            $_SESSION['nome'] = $user_row['nome']; 
            $_SESSION['logged_in'] = true;
            
            // ... TI MANDA QUI:
            header("Location: paginapersonale.html");
            exit;
        } else {
            $error_msg = "Credenziali non corrette.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi - Popcorn&Pixels</title>
    <link rel="icon" type="image/png" href="resources/icona.png">
    
    <link rel="stylesheet" href="stile/accesso.css">
    <script src="validation.js" defer></script>
</head>
<body>

    <header class="main-header">
        <div class="logo-center">
            <a href="struttura.html" id="homepage" class="tasto-tondo" style="text-decoration:none;">
                <img src="resources/icona.png" alt="Homepage">
            </a>
        </div>
    </header>

    <main class="main-container auth-page">

    <?php if($error_msg): ?>
        <div class="alert error" style="max-width: 600px; margin: 0 auto 20px auto;">
            <?= $error_msg; ?>
        </div>
    <?php endif; ?>
    
    <?php if($success_msg): ?>
        <div class="alert success" style="max-width: 600px; margin: 0 auto 20px auto;">
            <?= $success_msg; ?>
        </div>
    <?php endif; ?>

    <div class="auth-wrapper">
        
        <div id="box-login" style="display: <?php echo $show_register ? 'none' : 'block'; ?>;">
            <h2 class="titolo-fuori">BENTORNATO</h2>
            <p class="form-subtitle">Accedi al tuo account</p>
            
            <div class="form-card">
                <form action="accesso.php" method="POST" onsubmit="return validateLogin()">
                    <label>Username o Email</label>
                    <input type="text" name="login_input" value="<?= htmlspecialchars($login_input_val); ?>" placeholder="Inserisci user o email" required>
                    
                    <label>Password</label>
                    <input type="password" name="login_password" placeholder="La tua password" required>
                    
                    <div style="margin-top: 10px; text-align: right;">
                        <a href="recupero.php">Password dimenticata?</a>
                    </div>
                    
                    <button type="submit" name="btn_login">ACCEDI</button>
                </form>
                <p class="switch-text">
                    Non hai un account? <span onclick="mostraRegistrazione()">Registrati</span>
                </p>
            </div>
        </div>

        <div id="box-register" style="display: <?php echo $show_register ? 'block' : 'none'; ?>;">
            <h2 class="titolo-fuori">NUOVO UTENTE</h2>
            <p class="form-subtitle">Crea il tuo profilo personale</p>
            
            <div class="form-card">
                <form action="accesso.php" method="POST" onsubmit="return validateRegister()">
                    
                    <div class="input-row">
                        <div>
                            <label>Nome</label>
                            <input type="text" name="reg_nome" placeholder="Nome" value="<?= htmlspecialchars($nome_val); ?>" required>
                        </div>
                        <div>
                            <label>Cognome</label>
                            <input type="text" name="reg_cognome" placeholder="Cognome" value="<?= htmlspecialchars($cognome_val); ?>" required>
                        </div>
                    </div>
                    
                    <div class="input-row">
                        <div style="flex: 1.5;">
                            <label>Email</label>
                            <input type="email" name="reg_email" placeholder="Email" value="<?= htmlspecialchars($email_val); ?>" required>
                        </div>
                        <div style="flex: 1;">
                            <label>Username</label>
                            <input type="text" name="reg_username" placeholder="Username" value="<?= htmlspecialchars($username_val); ?>" required>
                        </div>
                    </div>
                    
                    <label>Password</label>
                    <input type="password" name="reg_password" id="regPass" placeholder="Minimo 8 caratteri" required>
                    
                    <div class="input-row">
                        <div style="flex: 1;">
                            <label>Domanda Sicurezza</label>
                            <select name="reg_domanda" required>
                                <option value="" disabled <?= ($domanda_val=="")?'selected':''; ?>>Scegli...</option>
                                <option value="animale" <?= ($domanda_val=="animale")?'selected':''; ?>>Nome animale?</option>
                                <option value="madre" <?= ($domanda_val=="madre")?'selected':''; ?>>Cognome madre?</option>
                                <option value="citta" <?= ($domanda_val=="citta")?'selected':''; ?>>Città nascita?</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Risposta</label>
                            <input type="text" name="reg_risposta" placeholder="Risposta" required>
                        </div>
                    </div>

                    <button type="submit" name="btn_register">REGISTRATI</button>
                </form>
                <p class="switch-text">
                    Hai già un account? <span onclick="mostraLogin()">Accedi</span>
                </p>
            </div>
        </div>
    </div>

    <script>
        function mostraRegistrazione() {
            document.getElementById('box-login').style.display = 'none';
            document.getElementById('box-register').style.display = 'block';
        }
        function mostraLogin() {
            document.getElementById('box-register').style.display = 'none';
            document.getElementById('box-login').style.display = 'block';
        }
    </script>
    <script src="validation.js"></script>

<?php include 'footer.php'; ?>