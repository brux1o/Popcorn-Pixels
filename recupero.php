<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


if (isset($_GET['exit'])) {
    unset($_SESSION['rec_id']);
    unset($_SESSION['rec_domanda']);
    unset($_SESSION['rec_step']);
    
    if ($_GET['exit'] === 'home') {
        header("Location: struttura.html");
    } else {
        header("Location: accesso.php");
    }
    exit;
}


require_once 'db.php'; 

$step = 'search'; 
$error_msg = "";
$sticky_user = "";


if (isset($_SESSION['rec_step'])) {
    $step = $_SESSION['rec_step'];
}

if (isset($_POST['input_user'])) {
    $sticky_user = htmlspecialchars($_POST['input_user']);
}


if (isset($_POST['btn_search'])) {
    $input = trim($_POST['input_user']);
    
    if (empty($input)) {
        $error_msg = "Inserisci username o email.";
    } else {
        $query = "SELECT id, domanda_sicurezza FROM utente WHERE email=$1 OR username=$1";
        $res = pg_query_params($db, $query, array($input));
        
        if (pg_num_rows($res) > 0) {
            $row = pg_fetch_assoc($res);

            $_SESSION['rec_id'] = $row['id'];
            $_SESSION['rec_domanda'] = $row['domanda_sicurezza'];
            $_SESSION['rec_step'] = 'verify';
            
            header("Location: recupero.php");
            exit;
        } else {
            $error_msg = "Nessun utente trovato con questi dati.";
        }
    }
}


if (isset($_POST['btn_verify'])) {
    $codice_input = trim($_POST['code_backup']);
    $risposta_input = trim($_POST['security_ans']);
    $uid = $_SESSION['rec_id'];
    
    $query_risp = "SELECT risposta_sicurezza FROM utente WHERE id=$1";
    $res_risp = pg_query_params($db, $query_risp, array($uid));
    $hash_risposta = pg_fetch_result($res_risp, 0, 0);
    
    if (password_verify($risposta_input, $hash_risposta)) {
        

        $query_codes = "SELECT id, codice_hash FROM codici_backup WHERE utente_id=$1 AND usato=FALSE";
        $res_codes = pg_query_params($db, $query_codes, array($uid));
        
        $code_valid_id = null;
        
        while ($row_code = pg_fetch_assoc($res_codes)) {
            if (password_verify($codice_input, $row_code['codice_hash'])) {
                $code_valid_id = $row_code['id'];
                break; 
            }
        }
        
        if ($code_valid_id) {

            pg_query_params($db, "UPDATE codici_backup SET usato=TRUE WHERE id=$1", array($code_valid_id));
            
            $_SESSION['rec_step'] = 'reset';
            header("Location: recupero.php");
            exit;
        } else {
            $error_msg = "Codice di sicurezza non valido o già utilizzato.";
        }
        
    } else {
        $error_msg = "La risposta alla domanda di sicurezza è errata.";
    }
}

if (isset($_POST['btn_reset'])) {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    $uid = $_SESSION['rec_id'];
    
    if (strlen($pass1) < 8) {
        $error_msg = "La password deve essere di almeno 8 caratteri.";
    } elseif ($pass1 !== $pass2) {
        $error_msg = "Le password non coincidono.";
    } else {
        // Aggiorna Password
        $new_hash = password_hash($pass1, PASSWORD_DEFAULT);
        $query_upd = "UPDATE utente SET password=$1 WHERE id=$2";
        $res_upd = pg_query_params($db, $query_upd, array($new_hash, $uid));
        
        if ($res_upd) {

            unset($_SESSION['rec_id']);
            unset($_SESSION['rec_domanda']);
            unset($_SESSION['rec_step']);
            
            $_SESSION['msg_flash'] = "Password reimpostata con successo! Usa la nuova password.";
            
            header("Location: accesso.php");
            exit;
        } else {
            $error_msg = "Errore durante l'aggiornamento della password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recupero Password - Popcorn&Pixels</title>
    <link rel="icon" type="image/png" href="resources/icona.png">
    
    <link rel="stylesheet" href="stile/accesso.css">
    
    <style>
        /* Stile specifico per la freccia indietro */
        .back-arrow {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #FFD700; 
            font-size: 24px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .back-arrow:hover {
            color: #fff;
        }
        .main-header {
            position: relative; 
        }
    </style>

    <script>
        // Funzione per chiedere conferma prima di uscire
        function confermaUscita(event) {
            if (!confirm("Sei sicuro di voler interrompere il recupero? I progressi andranno persi.")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>

    <header class="main-header">
        <a href="recupero.php?exit=login" class="back-arrow" onclick="confermaUscita(event)">&#8592;</a>
        
        <div class="logo-center">
            <a href="recupero.php?exit=home" class="tasto-tondo" style="text-decoration:none;" onclick="confermaUscita(event)">
                <img src="resources/icona.png" alt="Homepage">
            </a>
        </div>
    </header>

    <main class="main-container auth-page">

    <div class="auth-wrapper" style="max-width: 500px;"> 
        
        <?php if($error_msg): ?>
            <div class="alert error"><?= $error_msg; ?></div>
        <?php endif; ?>

        <div id="step-search" style="display: <?php echo ($step === 'search') ? 'block' : 'none'; ?>;">
            <h2 class="titolo-fuori">RECUPERO</h2>
            <p class="form-subtitle">Inserisci i tuoi dati per iniziare</p>
            
            <div class="form-card">
                <form action="recupero.php" method="POST">
                    <label>Username o Email</label>
                    <input type="text" name="input_user" value="<?= $sticky_user; ?>" placeholder="user o email" required>
                    
                    <button type="submit" name="btn_search">CERCA UTENTE</button>
                </form>
            </div>
        </div>

        <div id="step-verify" style="display: <?php echo ($step === 'verify') ? 'block' : 'none'; ?>;">
            <h2 class="titolo-fuori">SICUREZZA</h2>
            <p class="form-subtitle">Dimostra che sei tu</p>
            
            <div class="form-card">
                <form action="recupero.php" method="POST">
                    
                    <div style="background: rgba(255, 215, 0, 0.1); padding: 10px; border-radius: 5px; border: 1px solid #FFD700; margin-bottom: 15px; text-align: center;">
                        <span style="color: #FFD700; font-size: 0.9rem;">DOMANDA DI SICUREZZA:</span><br>
                        <strong style="color: white; font-size: 1.1rem;">
                            <?php 
                                $d = $_SESSION['rec_domanda'] ?? '';
                                // Traduzione visuale domanda
                                if($d == 'animale') echo "Qual è il nome del tuo primo animale?";
                                elseif($d == 'madre') echo "Qual è il cognome di tua madre?";
                                elseif($d == 'citta') echo "In quale città sei nato?";
                                else echo htmlspecialchars($d); 
                            ?>
                        </strong>
                    </div>

                    <label>Risposta</label>
                    <input type="text" name="security_ans" placeholder="La tua risposta..." required>
                    
                    <label style="margin-top: 15px;">Codice di Backup</label>
                    <p style="font-size: 0.8rem; color: #aaa; margin-top: 0;">Inserisci uno dei codici salvati durante la registrazione.</p>
                    <input type="text" name="code_backup" placeholder="Es. A1B2C3D4" required style="letter-spacing: 2px; text-transform: uppercase;">

                    <button type="submit" name="btn_verify">VERIFICA IDENTITÀ</button>
                </form>
            </div>
        </div>

        <div id="step-reset" style="display: <?php echo ($step === 'reset') ? 'block' : 'none'; ?>;">
            <h2 class="titolo-fuori">NUOVA PASSWORD</h2>
            <p class="form-subtitle">Scegli una nuova password sicura</p>
            
            <div class="form-card">
                <form action="recupero.php" method="POST">
                    <label>Nuova Password</label>
                    <input type="password" name="pass1" placeholder="Minimo 8 caratteri" required>
                    
                    <label>Conferma Password</label>
                    <input type="password" name="pass2" placeholder="Ripeti password" required>

                    <button type="submit" name="btn_reset">AGGIORNA PASSWORD</button>
                </form>
            </div>
        </div>

    </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>