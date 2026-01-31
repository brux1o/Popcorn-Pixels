
// 1. Validazione LOGIN
function validateLogin() {
    const identificativo = document.getElementsByName('identificativo')[0].value.trim();
    const password = document.getElementsByName('password')[0].value.trim();

    if (identificativo === "" || password === "") {
        alert("Attenzione: Inserisci sia l'Username/Email che la Password.");
        return false;
    }
    return true;
}

// 2. Validazione REGISTRAZIONE
function validateRegister() {
    const nome = document.getElementsByName('nome')[0].value.trim();
    const cognome = document.getElementsByName('cognome')[0].value.trim();
    const email = document.getElementsByName('email')[0].value.trim();
    const password = document.getElementsByName('password')[0].value;
    
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

    if (nome === "" || cognome === "") {
        alert("Nome e Cognome sono obbligatori.");
        return false;
    }

    return true;
}

// 3. Validazione RESET FINALE
function validateReset() {
    const p1 = document.getElementsByName('n_password')[0].value;
    const p2 = document.getElementsByName('c_password')[0].value;

    if (p1.length < 6) {
        alert("La nuova password deve avere almeno 6 caratteri.");
        return false;
    }

    if (p1 !== p2) {
        alert("Le password inserite non coincidono.");
        return false;
    }
    return true;
}