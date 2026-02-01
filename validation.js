/* --- VALIDAZIONE LOGIN --- */
function validateLogin() {
    // Nota: I nomi devono combaciare con l'HTML di accesso.php
    const user = document.querySelector('input[name="username_email"]').value;
    const pass = document.querySelector('input[name="password"]').value;
    
    if (user.trim() === "" || pass.trim() === "") {
        alert("Inserisci sia username/email che password.");
        return false; // Blocca l'invio del form
    }
    return true; // Permette l'invio
}

/* --- VALIDAZIONE REGISTRAZIONE --- */
function validateRegister() {
    const nome = document.querySelector('input[name="nome"]').value;
    const cognome = document.querySelector('input[name="cognome"]').value;
    const email = document.querySelector('input[name="email"]').value;
    const user = document.querySelector('input[name="username"]').value;
    const pass = document.querySelector('input[name="password"]').value; // Nota: nel form register l'avevamo chiamata 'password'
    
    // 1. Controllo campi vuoti
    if (nome.trim() === "" || cognome.trim() === "" || email.trim() === "" || user.trim() === "" || pass.trim() === "") {
        alert("Tutti i campi sono obbligatori per la registrazione.");
        return false;
    }

    // 2. Validazione Email con Regex (Standard HTML5)
    // Questo controllo extra è ottimo per l'esame
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Inserisci un indirizzo email valido.");
        return false;
    }

    // 3. Controllo lunghezza Password
    if (pass.length < 8) { // Consiglio 8 invece di 4 per standard di sicurezza migliori
        alert("La password deve essere lunga almeno 8 caratteri.");
        return false;
    }

    return true;
}

/* --- VALIDAZIONE RECUPERO (Per recupero.php) --- */
function validateRecupero() {
    // Assicurati che l'input in recupero.php si chiami 'input_user'
    const input = document.querySelector('input[name="input_user"]'); 
    
    if (!input || input.value.trim() === "") {
        alert("Inserisci l'email o l'username per recuperare la password.");
        return false;
    }
    return true;
}

/* --- VALIDAZIONE RESET PASSWORD (Per recupero.php step 2) --- */
function validateReset() {
    const code = document.querySelector('input[name="code"]').value;
    const newPass = document.querySelector('input[name="new_pass"]').value;

    // Controllo codice (assunto che nel DB sia un codice breve, es. 3 cifre come hai scritto)
    if (code.trim().length === 0) {
        alert("Inserisci il codice di sicurezza.");
        return false;
    }
    
    if (newPass.trim() === "") {
        alert("Inserisci la nuova password.");
        return false;
    }

    if (newPass.length < 8) {
        alert("La nuova password deve essere di almeno 8 caratteri.");
        return false;
    }
    
    return true;
}