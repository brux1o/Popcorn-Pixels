<?php
    require_once '/db.php';
    
   
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once '/aggiornamentoStato.php';

    $logger = isset($_SESSION['username']);
    $idContent = isset($_GET['id']) ? $_GET['id'] : '';
    $typeContent = isset($_GET['type']) ? $_GET['type'] : 'movie';

    if(empty($idContent)) {
        header("Location: ricerca.php");
        exit;
    }

    // Query per i commenti 
    $query_commenti = "SELECT c.*, u.username 
                       FROM commenti c 
                       JOIN utente u ON c.id_utente = u.id 
                       WHERE c.id_contenuto = $1 AND c.tipo_contenuto = $2 
                       ORDER BY c.data_inserimento DESC";

    $prep_c = pg_prepare($db, "get_comments", $query_commenti);
    $res_c = pg_execute($db, "get_comments", array($idContent, $typeContent));
    $commenti = pg_fetch_all($res_c);

    // Chiamata API TMDB
    $endpoint = ($typeContent == 'tv') ? "/tv/" : "/movie/";
    $url = TMDB_BASE_URL . $endpoint . $idContent . "?api_key=" . api_key . "&language=it-IT&append_to_response=credits,videos";

    $response = file_get_contents($url);

    if($response === FALSE){
        echo "Errore nel caricamento dei dati";
        exit;
    }

    $dettagli = json_decode($response,true);

    $descrizione = !empty($dettagli['overview']) ? $dettagli['overview'] : "Descrizione non disponibile";
    $voto = $dettagli['vote_average'] ?? 0;
    $posterPath = $dettagli['poster_path'] ?? '';
    $bgPath = $dettagli['backdrop_path'] ?? '';
    
    if($posterPath){
        $posterImg = "https://image.tmdb.org/t/p/w500" . $posterPath;
    } else{
        $posterImg = "https://placehold.jp/300x450.png?text=Immagine+non+disponibile";
    }

    if($bgPath){
      $bgStyle = "linear-gradient(to bottom, rgba(20,20,20,0.6), rgba(20,20,20,1)), url('https://image.tmdb.org/t/p/original$bgPath')";  
    }
    else{
        $bgStyle = "#1a1a1a";
    }


    $generi = [];
    if(!empty($dettagli['genres'])){
        foreach ($dettagli['genres'] as $g){
            $generi[] = $g['name'];
        }
    }

    $cast = []; 
    if(!empty($dettagli['credits']['cast'])){
        $cast = array_slice($dettagli['credits']['cast'],0,6);
    }

    $trailerKey='';
    if(!empty($dettagli['videos']['results'])){
        foreach($dettagli['videos']['results'] as $video){
            if($video['site'] === 'YouTube' && $video['type'] === 'Trailer'){
                $trailerKey = $video['key'];
                break;
            }
        }
    }

    $titolo = '';
    $titoloOriginale = '';
    $tagline = '';
    $stato = '';
    $infoExtra = [];
    
    if($typeContent == 'movie'){
        $titolo = $dettagli['title'] ?? 'Senza Titolo';
        $titoloOriginale = $dettagli['original_title'] ?? '';
        $tagline = $dettagli['tagline'] ?? '';
        $stato = $dettagli['status'] ?? 'N/D';
        $dataUscita = $dettagli['release_date'] ?? 'N/D'; 

        $minuti = $dettagli['runtime'] ?? 0;

        $regista = "Sconosciuto";
        if(!empty($dettagli['credits']['crew'])) {
            foreach ($dettagli['credits']['crew'] as $membro) {
                if ($membro['job'] === 'Director') {
                    $regista = $membro['name'];
                    break;
                }
            }
        }

        $castNomi = [];
        if(!empty($dettagli['credits']['cast'])) {
            $primiAttori = array_slice($dettagli['credits']['cast'], 0, 4);
            foreach ($primiAttori as $attore) {
                $castNomi[] = $attore['name'];
            }
        }
        $castStringa = !empty($castNomi) ? implode(", ", $castNomi) : "N/D";

        $infoExtra = [
            "Tipo" => "Film",
            "Uscita" => $dataUscita,
            "Durata" => $minuti . " min",
            "Regia" => $regista,
            "Cast" => $castStringa
        ];
    } elseif ($typeContent == 'tv'){
        $titolo = $dettagli['name'] ?? 'Senza Nome';
        $titoloOriginale = $dettagli['original_name'] ?? '';
        $tagline = $dettagli['tagline'] ?? '';
        $stato = $dettagli['status'] ?? 'N/D';
        
        $dataInizio = $dettagli['first_air_date'] ?? 'N/D';
        $dataFine = $dettagli['last_air_date'] ?? 'In corso';
        $numStagioni = $dettagli['number_of_seasons'] ?? 0;
        $numEpisodi = $dettagli['number_of_episodes'] ?? 0; 

        $listaCreatori = [];
        if (!empty($dettagli['created_by'])) {
            foreach ($dettagli['created_by'] as $c) {
                $listaCreatori[] = $c['name'];
            }
        }
        $creatoDa = !empty($listaCreatori) ? implode(", ", $listaCreatori) : "N/D";

        $listaNetwork = [];
        if (!empty($dettagli['networks'])) {
            foreach ($dettagli['networks'] as $n) {
                $listaNetwork[] = $n['name'];
            }
        }
        $networks = !empty($listaNetwork) ? implode(", ", $listaNetwork) : "N/D";

        $infoExtra = [
            "Tipo" => "Serie TV",
            "Primo Ep." => $dataInizio,
            "Ultimo Ep." => $dataFine,
            "Stagioni" => $numStagioni,
            "Episodi" => $numEpisodi,
            "Creato da" => $creatoDa,
            "Network" => $networks
         ];
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="resources/icona.png">
    <title><?php echo $titolo; ?> - Dettagli</title>
    <link rel="stylesheet" href="dettaglio_css.css">
</head>
<body style="background-image: <?php echo $bgStyle; ?>;">

    <header>
        <div id="nav-sinistra">
            <a href="javascript:history.back()" class="tasto-tondo" id="btn-back">
                ←
            </a>
        </div>

        <div id="nav-centro">
            <a href="struttura.html" id="homepage-logo">
                <img src="resources/icona.png" alt="Homepage">
            </a>
        </div>

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
                        <a href="profilo.php">👤 Il mio Profilo</a>
                        <a href="../backend/logout.php" style="color: #ff5555;">🚪 Logout</a>
                    </div>
                </div>

            <?php else: ?>

                <a href="login.php" class="tasto-tondo"> 
                    <img src="resources/utente.png" alt="Login">
                </a>

            <?php endif; ?>
        </div>  
    </header>

    <main>

    <div class="main-wrapper">
        
        <div class="header-content">
            
            <div class="poster-side">
                <div class="poster-container">
                    <img src="<?php echo $posterImg; ?>" alt="Poster">
                </div>
                
                <div class="action-buttons">
                    <button class="btn-action heart" title="Aggiungi ai Preferiti" 
                        onclick="aggiungiPreferiti(<?php echo $idContent; ?>, '<?php echo $typeContent; ?>', '<?php echo addslashes($titolo); ?>', '<?php echo $posterPath; ?>')">
                        <span class="icon">❤️</span>
                    </button>
                    
                    <button class="btn-action plus" title="Aggiungi alla Watchlist" 
                        onclick="aggiungiWatchlist(<?php echo $idContent; ?>, '<?php echo $typeContent; ?>', '<?php echo addslashes($titolo); ?>', '<?php echo $posterPath; ?>')">
                        <span class="icon">➕</span>
                    </button>
                </div>
            </div>

            <div class="text-content">
                <h1 class="title"><?php echo $titolo; ?></h1>
                
                <?php if($tagline): ?>
                    <p class="tagline">"<?php echo $tagline; ?>"</p>
                <?php endif; ?>

                <div class="meta-row">
                    <span class="voto-box">⭐ <?php echo number_format($voto, 1); ?></span>
                    <span class="info-pill"><?php echo $stato; ?></span>
                </div>

                <div class="genres-list">
                    <?php foreach($generi as $genere): ?>
                        <span class="genre-tag"><?php echo $genere; ?></span>
                    <?php endforeach; ?>
                </div>

                <h3>Trama</h3>
                <p class="description"><?php echo $descrizione; ?></p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">Titolo Originale</span>
                <span class="value"><?php echo $titoloOriginale; ?></span>
            </div>
            <?php foreach($infoExtra as $label => $value): ?>
                <div class="info-item">
                    <span class="label"><?php echo $label; ?></span>
                    <span class="value"><?php echo $value; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if(!empty($cast)): ?>
            <div class="section-title">Cast</div>
            <div class="cast-container">
                <?php foreach($cast as $attore): 
                    $foto = $attore['profile_path'] ? "https://image.tmdb.org/t/p/w185".$attore['profile_path'] : "https://placehold.jp/300x450.png?text=Immagine+non+disponibile";
                ?>
                    <div class="cast-card">
                        <img src="<?php echo $foto; ?>" alt="<?php echo $attore['name']; ?>">
                        <p class="actor-name"><?php echo $attore['name']; ?></p>
                        <p class="character-name"><?php echo $attore['character']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if($trailerKey): ?>
            <div class="section-title">Trailer Ufficiale</div>
            <div class="video-container">
                <iframe 
                    width="100%" 
                    height="500" 
                    src="https://www.youtube.com/embed/<?php echo $trailerKey; ?>" 
                    frameborder="0" 
                    allowfullscreen>
                </iframe>
            </div>
        <?php endif; ?>

    </div>

    <div class="comment-form-container">
        <?php if(isset($_SESSION['username'])): ?>
            <form action="/salva_commento.php" method="POST" class="comment-form">
                <input type="hidden" name="id_contenuto" value="<?php echo $idContent; ?>">
                <input type="hidden" name="tipo" value="<?php echo $typeContent; ?>">
                
                <input type="hidden" name="titolo" value="<?php echo htmlspecialchars($titolo); ?>">

                <textarea name="testo_commento" placeholder="Scrivi un commento..." required></textarea>
                <button type="submit" class="btn-invia-commento">Invia</button>
            </form>
        <?php else: ?>
            <div class="login-alert">
                <p>Devi essere <a href="login.php" style="color:#00ff00;">loggato</a> per commentare.</p>
            </div>
        <?php endif; ?>
    </div>

        <div class="comments-list">
            <?php if($commenti): ?>
                <?php foreach($commenti as $c): ?>
                    <div class="comment-card">
                        <div class="comment-header">
                            <img src="resources/utente.png" class="comment-avatar">
                            <span class="comment-author"><?php echo htmlspecialchars($c['username']); ?></span>
                            <span class="comment-date"><?php echo date('d/m/Y H:i' , strtotime($c['data_inserimento'])); ?></span>
                        </div>
                        <div class="comment-body">
                            <p><?php echo htmlspecialchars($c['testo']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #888;">Nessun commento per questo film. Scrivi il primo!</p>
            <?php endif; ?>
        </div>
    </div>

    </main>

    <footer>
        <? include '/footer.php'; ?>
    </footer>

    <?php include 'aggiungiPreferiti.php'; ?>
    <?php include 'aggiungiWatchlist.php'; ?>
    
</body>
</html>