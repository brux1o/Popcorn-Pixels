<!DOCTYPE html>

<?php
    include '/richiesta.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once '/aggiornamentoStato.php';
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Popcorn & Pixels</title>
        <link rel="stylesheet" href="stile/ricerca_css.css">
    </head>
    <body>
        <header>
            <div id="ricerca">
                    <form method="get" action="" class="search-bar">
                    <input id="textfield-cerca" type="text" name="query" placeholder="Cerca Contenuto" autocomplete="off" required>
                    <button id="btn-cerca" type="submit" class="tasto-tondo"> 
                        <img src="resources/ricerca.png" alt="Cerca">
                    </button>
                </form>
            </div>
                <a href="struttura.html" id="homepage" class="tasto-tondo"style="text-decoration:none;" >
                <img src="resources/icona.png" alt="Homepage"></a>

             <div id="zona-utente">
            <div class="info-testo-utente">
                <label>Stato: <span id="stato-utente" style="color: <?php echo $stato_colore; ?>; font-weight:bold;">
                    <?php echo $stato_testo; ?>
                </span></label>
                <label>Utente: <span id="nome-utente"><?php echo $nome_utente; ?></span></label> 
            </div> 

            <?php if($loggato): ?>
                
                <div class="dropdown">
                    <div class="tasto-tondo" style="cursor: pointer;"> 
                        <img src="resources/utente.png" alt="Profilo">
                    </div>
                    
                    <div class="dropdown-content">
                        <a href="/paginapersona.html">👤 Il mio Profilo</a>
                        <a href="/login.php" style="color: #ff5555;">🚪 Logout</a>
                    </div>
                </div>
                
            <?php else: ?>
                <a href="login.php" class="tasto-tondo"> 
                    <img src="resources/utente.png" alt="Login">
                </a>
            <?php endif; ?>
        </div>  
    </header>
        </div>
        </header>
        <main>
            <div id="contenitore-film">

            </div>

        </main>
        <footer>
                <?php include '/footer.php'; ?>
        </footer>
        <script>

            const tmdbData = <?php echo $jsonPerJavascript; ?>
        </script>
        <script src="visualizzazione_elemento.js"></script>
        <script src="visualizzazione_ricerca.js"></script>
        
    </body>
</html>