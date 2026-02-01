/* ==============================================
   VALIDAZIONE CLIENT-SIDE (validation.js)
   ============================================== */

// 1. Validazione LOGIN (login.php)
function validateLogin() {
    // NOTA: In login.php il campo si chiama 'username', non 'identificativo'
    const userField = document.getElementsByName('username')[0];
    const passField = document.getElementsByName('password')[0];

    // Controllo esistenza elementi (per evitare errori in console se il campo manca)
    if (!userField || !passField) return true;

    const username = userField.value.trim();
    const password = passField.value.trim();

    if (username === "" || password === "") {
        alert("Attenzione: Inserisci sia l'Username/Email che la Password.");
        return false; // Blocca l'invio del form
    }
    return true; // Procede
}

// 2. Validazione REGISTRAZIONE (register.php)
function validateRegister() {
    const nome = document.getElementsByName('nome')[0].value.trim();
    const cognome = document.getElementsByName('cognome')[0].value.trim();
    const email = document.getElementsByName('email')[0].value.trim();
    const password = document.getElementsByName('password')[0].value;
    const risposta = document.getElementsByName('risposta_sicurezza')[0].value.trim();
    
    // Controllo campi vuoti generici
    if (nome === "" || cognome === "" || risposta === "") {
        alert("Tutti i campi (Nome, Cognome, Risposta di sicurezza) sono obbligatori.");
        return false;
    }

    // Controllo Email (RegEx base)
    const emailReg = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailReg.test(email)) {
        alert("Inserisci un indirizzo Email valido.");
        return false;
    }

    // Controllo Lunghezza Password
    if (password.length < 6) {
        alert("La password deve essere di almeno 6 caratteri.");
        return false;
    }

    return true;
}

// 3. Validazione RESET PASSWORD (reset_password.php)
function validateReset() {
    // In reset_password.php il campo si chiama 'nuova_password'
    const p1 = document.getElementsByName('nuova_password')[0].value;

    if (p1.length < 6) {
        alert("La nuova password deve avere almeno 6 caratteri.");
        return false;
    }

    // Nota: Ho rimosso il controllo su 'c_password' perché nel file PHP 
    // reset_password.php abbiamo messo solo un campo password per semplicità.
    // Se vuoi la conferma, dobbiamo aggiungere l'input nell'HTML.
    
    return true;
}