<?php
/* ==========================================
   SEZIONE 1: CONFIGURAZIONE E DEBUG
   ========================================== */
// Attiva la segnalazione degli errori: utile in fase di sviluppo per vedere se qualcosa si rompe.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Avvia la sessione: serve per memorizzare i dati dell'utente (ID, domanda) tra una pagina e l'altra.
session_start();

/* ==========================================
   SEZIONE 2: CONNESSIONE AL DATABASE
   ========================================== */
// Controllo di sicurezza: se il file di connessione non c'è, blocca tutto per evitare errori fatali.
if (!file_exists('db.php')) {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>ERRORE GRAVE: Manca il file 'db.php' nella cartella!</h2>");
}

require_once 'db.php'; // Carica la connessione effettiva al database ($db)

$errore = ""; // Variabile per memorizzare eventuali messaggi di errore da mostrare all'utente

/* ==========================================
   SEZIONE 3: LOGICA DEL FORM (BACKEND)
   ========================================== */
// Questo blocco si attiva SOLO quando l'utente preme il pulsante "Cerca Account" (metodo POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recupera l'input e rimuove spazi vuoti all'inizio/fine (trim)
    // L'operatore ?? '' serve a evitare errori se il campo è vuoto.
    $input = trim($_POST['input_utente'] ?? '');

    // Verifica se la connessione al DB è attiva
    if (!$db) {
        $errore = "Impossibile connettersi al Database. Controlla db.php.";
    } else {
        // Query SQL per cercare l'utente.
        // NOTA: 'ILIKE' è specifico di PostgreSQL per una ricerca "case-insensitive" (ignora maiuscole/minuscole).
        // $1 è un placeholder per la sicurezza (previene SQL Injection).
        $sql = "SELECT id, domanda_sicurezza FROM utente WHERE email ILIKE $1 OR username ILIKE $1";
        
        // Esegue la query passando l'input dell'utente in modo sicuro
        $result = @pg_query_params($db, $sql, array($input)); 

        // Se la query ha successo e trova almeno una riga (> 0)
        if ($result && pg_num_rows($result) > 0) {
            $user = pg_fetch_assoc($result); // Converte il risultato in un array associativo
            
            // ==========================================
            // SUCCESSO: Trovato l'utente
            // ==========================================
            // Salviamo l'ID e la domanda nella sessione per usarli nella pagina successiva (verifica_domanda.php)
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_domanda'] = $user['domanda_sicurezza'];
            
            // Reindirizza l'utente alla pagina della domanda segreta
            header("Location: verifica_domanda.php");
            exit(); // Termina lo script per assicurarsi che il redirect avvenga subito
        } else {
            // FALLIMENTO: Nessun utente trovato
            $errore = "Nessun account trovato con questo nome o email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recupero Password</title>
    <link rel="icon" type="image/png" href="resources/icona.png">

    <style>
        /* Reset di base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #1a1a1a;
            /* Sfondo con gradiente radiale */
            background: radial-gradient(circle at center, #222222 0%, #1a1a1a 100%);
            color: #e8e8e8;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Occupa almeno tutta l'altezza dello schermo */
        }
        
        /* Navbar superiore */
        header { width: 100%; background: rgba(0, 0, 0, 0.4); border-bottom: 1px solid rgba(212, 160, 23, 0.2); }
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 40px; max-width: 1200px; margin: 0 auto;
        }
        .nav-center img { height: 50px; } /* Logo */
        
        /* Bottone Annulla */
        .nav-button {
            color: #d4a017; text-decoration: none; font-weight: bold;
            padding: 8px 20px; border: 1px solid #d4a017; border-radius: 25px; transition: 0.3s;
        }
        .nav-button:hover { background: #d4a017; color: #000; } /* Effetto hover */
        
        /* Layout centrale */
        main { flex: 1; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .page-wrapper { width: 100%; max-width: 420px; text-align: center; }
        .page-title { color: #d4a017; font-size: 32px; margin-bottom: 10px; text-transform: uppercase; }
        .page-subtitle { color: #888; margin-bottom: 30px; }
        
        /* Stile del Form (Contenitore scuro) */
        form {
            background: #262626; padding: 35px; border-radius: 15px;
            border: 1px solid #333; box-shadow: 0 15px 45px rgba(0,0,0,0.5);
        }
        label { display: block; text-align: left; color: #d4a017; margin-bottom: 8px; font-weight: bold; font-size: 13px; text-transform: uppercase;}
        
        /* Input testuale */
        input {
            width: 100%; padding: 12px; margin-bottom: 20px; background: #1e1e1e;
            border: 1px solid #444; color: white; border-radius: 8px; outline: none;
        }
        input:focus { border-color: #d4a017; } /* Bordo dorato quando selezionato */
        
        /* Bottone di invio */
        button {
            width: 100%; padding: 15px; background: #d4a017; color: #1a1a1a; border: none;
            border-radius: 10px; font-weight: bold; font-size: 16px; cursor: pointer; text-transform: uppercase;
        }
        button:hover { background: #f1c40f; transform: translateY(-2px); }
        
        /* Box per i messaggi di errore */
        .alert { 
            background: rgba(255, 77, 77, 0.15); color: #ff4d4d; 
            padding: 10px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ff4d4d;
        }
        
        footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>

    <header>
        <nav class="navbar">
            <div style="flex:1;"></div> 
            <div class="nav-center" style="flex:0;">
                <img src="resources/icona.png" alt="Logo">
            </div>
            <div style="flex:1; display:flex; justify-content:flex-end;">
                <a href="login.php" class="nav-button">Annulla</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="page-wrapper">
            <h1 class="page-title">Recupero</h1>
            <h2 class="page-subtitle">Inserisci i dati per trovare l'account</h2>
            
            <?php if ($errore): ?>
                <div class="alert"><?= htmlspecialchars($errore) ?></div>
            <?php endif; ?>

            <form action="recupero.php" method="POST">
                <label>Username o Email</label>
                <input type="text" name="input_utente" required placeholder="Es. mariorossi o mario@email.it">
                
                <button type="submit">Cerca Account</button>
            </form>
        </div>
    </main>

    <?php include("footer.php"); ?>
</body>
</html>