<?php
include("header.php");
include("db.php");

// Recuperiamo i valori per rendere il form "sticky"
$nome = $_POST['nome'] ?? '';
$cognome = $_POST['cognome'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$telegram_id = $_POST['telegram_id'] ?? '';
?>

<main class="page-wrapper">
    <header>
        <h1 class="page-title">Registrazione</h1>
        <h2 class="page-subtitle">Crea un account</h2>
    </header>

    <section class="form-container">
        <form action="register.php" method="POST" enctype="multipart/form-data" onsubmit="return validateRegister()">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="reg_nome">Nome</label>
                    <input type="text" name="nome" id="reg_nome" value="<?= htmlspecialchars($nome) ?>" required>
                </div>
                <div class="form-group">
                    <label for="reg_cognome">Cognome</label>
                    <input type="text" name="cognome" id="reg_cognome" value="<?= htmlspecialchars($cognome) ?>" required>
                </div>
            </div>

            <label for="reg_email">Email</label>
            <input type="email" name="email" id="reg_email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="reg_username">Username</label>
            <input type="text" name="username" id="reg_username" value="<?= htmlspecialchars($username) ?>" required>

            <label for="reg_password">Password</label>
            <input type="password" name="password" id="reg_password" required>

            <label for="reg_domanda">Domanda di Sicurezza</label>
            <select name="domanda_scelta" id="reg_domanda" required>
                <option value="Qual è il tuo film preferito?">Qual è il tuo film preferito?</option>
                <option value="Nome del tuo primo animale?">Nome del tuo primo animale?</option>
                <option value="In che città sei nato?">In che città sei nato?</option>
            </select>

            <label for="reg_risposta">Risposta</label>
            <input type="text" name="risposta_utente" id="reg_risposta" required>

            <label for="reg_telegram">ID Telegram (opzionale)</label>
            <input type="text" name="telegram_id" id="reg_telegram" value="<?= htmlspecialchars($telegram_id) ?>">

            <button type="submit">Registrati</button>
        </form>
    </section>
</main>

<?php include("footer.php"); ?>