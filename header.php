<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Popcorn & Pixels</title>
    <link rel="icon" type="image/png" href="resources/icona.png">
    
    <link rel="stylesheet" href="stile/accesso.css">
    
    <script src="validation.js" defer></script>
</head>
<body>
    <header>
        <div id="sinistra">
            <a href="accesso.php" id="btn-back" class="tasto <?php echo (isset($mostra_freccia) && $mostra_freccia) ? 'visible' : ''; ?>">←</a>
        </div>
        
        <a href="index.html" style="text-decoration: none;">
            <h1>Popcorn&Pixels</h1>
        </a>

        <div style="flex: 1;"></div>
    </header>

    <div class="main">