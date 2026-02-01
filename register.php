<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: index.php"); // O struttura.html
    exit();
}
require_once 'db.php';

// Variabili sticky
$nome = $_POST['nome'] ?? '';
$cognome = $_POST['cognome'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$domanda = $_POST['domanda_sicurezza'] ?? '';
$errore = "";
$backup_codes = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_raw = $_POST['password'] ?? '';
    $risposta = $_POST['risposta_sicurezza'] ?? '';
    
    // GESTIONE IMMAGINE PROFILO
    $percorso_immagine = 'resources/utente.png'; // Default richiesto dalle specifiche

    // Controllo se è stato caricato un file senza errori
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $fileName = $_FILES['foto']['name'];
        $fileTmp = $_FILES['foto']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Estensioni ammesse
        $allowed = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileExt, $allowed)) {
            // Creo un nome unico per evitare conflitti
            $newFileName = time() . '_' . rand(1000,9999) . '.' . $fileExt;
            $destinazione = 'uploads/' . $newFileName;

            if (move_uploaded_file($fileTmp, $destinazione)) {
                $percorso_immagine = $destinazione;
            } else {
                $errore = "Errore nel salvataggio dell'immagine.";
            }
        } else {
            $errore = "Formato immagine non valido. Usa JPG, PNG o GIF.";
        }
    }

    // Procediamo solo se non ci sono errori con l'immagine
    if (empty($errore)) {
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
        // Hashiamo anche la risposta di sicurezza per privacy
        // $risposta_hash = password_hash(strtolower($risposta), PASSWORD_DEFAULT); 
        // Nota: se vuoi hashare la risposta, usa la riga sopra. Qui la salvo in chiaro come da tuo vecchio codice, ma hashare è meglio.

        // Query aggiornata con immagine_profilo
        $sql = "INSERT INTO utente (nome, cognome, email, username, password, domanda_sicurezza, risposta_sicurezza, immagine_profilo) 
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id";
        
        $params = array(
            $nome, 
            $cognome, 
            $email, 
            $username, 
            $password_hash, 
            $domanda, 
            $risposta, // O $risposta_hash
            $percorso_immagine
        );

        $result = @pg_query_params($db, $sql, $params);

        if ($result) {
            $utente_id = pg_fetch_row($result)[0];
            
            // Generazione codici backup
            for ($i = 0; $i < 3; $i++) {
                $code = bin2hex(random_bytes(4)); 
                $backup_codes[] = $code;
                pg_query_params($db, "INSERT INTO codici_backup (utente_id, codice_hash) VALUES ($1, $2)", 
                               array($utente_id, password_hash($code, PASSWORD_DEFAULT)));
            }
        } else {
            // Verifica errore duplicato (Username o Email)
            $pg_err = pg_last_error($db);
            if (strpos($pg_err, 'unique') !== false) {
                $errore = "Username o Email già esistenti.";
            } else {
                $errore = "Errore tecnico durante la registrazione.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
    <link rel="stylesheet" href="stile/accesso.css">
    <link rel="icon" type="image/png" href="resources/icona.png">
    <script src="stile/validation.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="login.php" class="nav-button">Indietro</a>
            </div>
            
            <div class="nav-center">
                <img src="resources/icona.png" class="nav-logo" alt="Logo">
            </div>
            
            <div class="nav-right" style="width: 100px;"></div> 
        </nav>
    </header>

    <main>
        <div class="page-wrapper">
            <?php if (!empty($backup_codes)): ?>
                <div class="backup-section" style="text-align: center;">
                    <h1 class="page-title">Registrazione Completata!</h1>
                    <p>Salva questi codici di recupero in un luogo sicuro.<br>Ti serviranno se dimentichi la password.</p>
                    <div style="background: #222; padding: 20px; border: 1px solid #d4a017; margin: 20px auto; max-width: 300px; color: #d4a017; font-family: monospace; font-size: 18px; border-radius: 8px;">
                        <?php foreach($backup_codes as $c) echo "<div style='margin: 5px 0;'>$c</div>"; ?>
                    </div>
                    <a href="login.php" class="nav-button">Vai al Login</a>
                </div>
            <?php else: ?>
                <h1 class="page-title">Registrazione</h1>
                <h2 class="page-subtitle">Crea un nuovo account</h2>

                <?php if ($errore): ?>
                    <div class="alert alert-error" style="color: #ff4d4d; border: 1px solid #ff4d4d; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?= htmlspecialchars($errore) ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" enctype="multipart/form-data" onsubmit="return validateRegister()">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Cognome</label>
                            <input type="text" name="cognome" value="<?= htmlspecialchars($cognome) ?>" required>
                        </div>
                    </div>

                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>

                    <label>Password</label>
                    <input type="password" name="password" required>

                    <label>Immagine Profilo (Opzionale)</label>
                    <input type="file" name="foto" accept="image/png, image/jpeg, image/gif">
                    <small style="color: #888; display: block; margin-top: -15px; margin-bottom: 15px;">Se non carichi nulla, verrà usata l'immagine di default.</small>

                    <label>Domanda di Sicurezza</label>
                    <select name="domanda_sicurezza">
                        <option value="Città di nascita" <?= $domanda == 'Città di nascita' ? 'selected' : '' ?>>Città di nascita?</option>
                        <option value="Nome animale" <?= $domanda == 'Nome animale' ? 'selected' : '' ?>>Nome del tuo primo animale?</option>
                        <option value="Cognome madre" <?= $domanda == 'Cognome madre' ? 'selected' : '' ?>>Cognome da nubile di tua madre?</option>
                    </select>

                    <label>Risposta</label>
                    <input type="text" name="risposta_sicurezza" required>

                    <button type="submit">Registrati</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include("footer.php"); ?>
</body>
</html>