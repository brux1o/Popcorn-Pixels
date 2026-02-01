<?php
// --- ABILITA VISUALIZZAZIONE ERRORI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// --- 1. CONFIGURAZIONE DATABASE ---
require_once 'db.php'; 

// FIX DATABASE: Collega $db a $conn
if (isset($db)) {
    $conn = $db;
} elseif (!isset($conn)) {
    die("Errore Critico: Nessuna variabile di connessione (\$db o \$conn) trovata in db.php");
}

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
    
    // Raccolta dati
    $nome_val = htmlspecialchars(trim($_POST['reg_nome']));
    $cognome_val = htmlspecialchars(trim($_POST['reg_cognome']));
    $email_val = trim($_POST['reg_email']);
    $username_val = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $domanda_val = $_POST['reg_domanda'];
    $risposta = trim($_POST['reg_risposta']);

    // --- GESTIONE CARICAMENTO FOTO (PER VARCHAR) ---
    // Valore di default come da tuo SQL
    $percorso_immagine = 'resources/utente.png'; 
    
    // Se l'utente ha caricato un file valido
    if (isset($_FILES['reg_foto']) && $_FILES['reg_foto']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['reg_foto']['name'];
        $filetype = $_FILES['reg_foto']['type'];
        $filesize = $_FILES['reg_foto']['size'];
        
        // Estensione del file
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Creiamo un nome unico per evitare sovrascritture
            $new_filename = uniqid() . "_" . $username_val . "." . $ext;
            $upload_path = "uploads/" . $new_filename;
            
            // Spostiamo il file dalla temp alla cartella uploads
            if (move_uploaded_file($_FILES['reg_foto']['tmp_name'], $upload_path)) {
                $percorso_immagine = $upload_path; // Questo andrà nel DB
            } else {
                $error_msg = "Errore nel salvataggio dell'immagine sul server.";
            }
        } else {
            $error_msg = "Formato immagine non valido. Usa JPG, PNG o GIF.";
        }
    }

    // --- VALIDAZIONE ---
    // Procediamo solo se non ci sono stati errori col file
    if (empty($error_msg)) {
        if (empty($nome_val) || empty($cognome_val) || empty($email_val) || empty($username_val) || empty($password) || empty($risposta)) {
            $error_msg = "Compila tutti i campi obbligatori.";
        } elseif (strlen($password) < 8) {
            $error_msg = "La password deve essere almeno di 8 caratteri.";
        } else {
            // Controllo esistenza utente
            $check_query = "SELECT id FROM utente WHERE email=$1 OR username=$2";
            $res_check = pg_query_params($conn, $check_query, array($email_val, $username_val));

            if (pg_num_rows($res_check) > 0) {
                $error_msg = "Username o Email già presenti nel sistema.";
            } else {
                // Hashing
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                $risp_hash = password_hash($risposta, PASSWORD_DEFAULT);
                
                // --- QUERY DI INSERIMENTO (AGGIORNATA PER IL TUO DB) ---
                // La colonna è 'immagine_profilo'
                $insert_query = "INSERT INTO utente (nome, cognome, email, username, password, domanda_sicurezza, risposta_sicurezza, immagine_profilo) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)";
                
                $res_ins = pg_query_params($conn, $insert_query, array(
                    $nome_val, 
                    $cognome_val, 
                    $email_val, 
                    $username_val, 
                    $pass_hash, 
                    $domanda_val, 
                    $risp_hash, 
                    $percorso_immagine // Qui passiamo la stringa del percorso (es. 'uploads/foto.jpg')
                ));
                
                if ($res_ins) {
                    $success_msg = "Registrazione completata! Ora puoi accedere.";
                    $show_register = false; 
                    $nome_val = $cognome_val = $email_val = $username_val = $domanda_val = "";
                } else {
                    $error_msg = "Errore database: " . pg_last_error($conn);
                }
            }
        }
    }
}

// --- 5. LOGIN ---
if (isset($_POST['btn_login'])) {
    $login_input_val = trim($_POST['login_input']);
    $password = $_POST['login_password'];
    
    if (empty($login_input_val) || empty($password)) {
        $error_msg = "Inserisci username/email e password.";
    } else {
        $login_query = "SELECT * FROM utente WHERE username=$1 OR email=$1";
        $res_login = pg_query_params($conn, $login_query, array($login_input_val));
        
        if (!$res_login) {
            $error_msg = "Errore tecnico Login: " . pg_last_error($conn);
        } else {
            $user_row = pg_fetch_assoc($res_login);
            
            if ($user_row && password_verify($password, $user_row['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user_row['id'];
                $_SESSION['username'] = $user_row['username'];
                $_SESSION['nome'] = $user_row['nome']; 
                $_SESSION['logged_in'] = true;
                
                header("Location: paginapersonale.html");
                exit;
            } else {
                $error_msg = "Credenziali non corrette.";
            }
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

    <div class="auth-wrapper">
        
        <?php if($error_msg): ?>
            <div class="alert error"><?= $error_msg; ?></div>
        <?php endif; ?>
        
        <?php if($success_msg): ?>
            <div class="alert success"><?= $success_msg; ?></div>
        <?php endif; ?>

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
                <form action="accesso.php" method="POST" enctype="multipart/form-data" onsubmit="return validateRegister()">
                    
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

                    <label style="margin-top: 15px;">Foto Profilo (Opzionale)</label>
                    <input type="file" name="reg_foto" accept="image/*" style="padding: 10px; background: #48494B; border-radius: 10px;">

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