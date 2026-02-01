<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popcorn&Pixels</title>
    
    <link rel="icon" type="image/png" href="resources/icona.png">
    
    <link rel="stylesheet" href="stile/accesso.css">
    
    <script src="validation.js" defer></script>
</head>
<body>
    <header class="main-header">
        
        <?php if (!isset($nascondi_freccia) || $nascondi_freccia === false): ?>
            <a href="index.php" class="back-btn" title="Torna alla Home">
                &#8592; 
            </a>
        <?php endif; ?>

        <div class="logo-center">
            <a href="struttura.html">
                <h1>Popcorn&Pixels</h1>
            </a>
        </div>
        
    </header>

    <main class="main-container">