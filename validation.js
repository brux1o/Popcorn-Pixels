/* --- 1. VALIDAZIONE LOGIN --- */
function validateLogin() {
    // Selezioniamo gli input tramite il loro attributo 'name'
    const userInput = document.querySelector('input[name="login_input"]');
    const passInput = document.querySelector('input[name="login_password"]');

    // Controllo di sicurezza: se l'input non esiste nella pagina, permetti l'invio (errore server)
    if (!userInput || !passInput) {
        return true; 
    }

    const userVal = userInput.value.trim();
    const passVal = passInput.value.trim();

    // Se uno dei due è vuoto, blocca e avvisa
    if (userVal === "" || passVal === "") {
        alert("Per accedere devi inserire sia username/email che password.");
        return false; // Blocca il form
    }

    // Se tutto ok, invia
    return true; 
}

/* --- 2. VALIDAZIONE REGISTRAZIONE --- */
function validateRegister() {
    // Raccogliamo i valori. Usa 'reg_' perché così li hai chiamati in PHP
    const nome = document.querySelector('input[name="reg_nome"]').value.trim();
    const cognome = document.querySelector('input[name="reg_cognome"]').value.trim();
    const email = document.querySelector('input[name="reg_email"]').value.trim();
    const user = document.querySelector('input[name="reg_username"]').value.trim();
    const pass = document.querySelector('input[name="reg_password"]').value; // Password non si trimma
    const domanda = document.querySelector('select[name="reg_domanda"]').value;
    const risposta = document.querySelector('input[name="reg_risposta"]').value.trim();

    // 1. Controllo campi vuoti
    if (!nome || !cognome || !email || !user || !pass || domanda === "" || !risposta) {
        alert("Tutti i campi sono obbligatori per la registrazione.");
        return false;
    }

    // 2. Controllo Email (Regex standard)
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Inserisci un indirizzo email valido (es: nome@mail.com).");
        return false;
    }

    // 3. Lunghezza Password
    if (pass.length < 8) {
        alert("La password deve essere di almeno 8 caratteri.");
        return false;
    }

    return true;
}

/* --- 3. VALIDAZIONE RECUPERO --- */
function validateRecupero() {
    // Cerca un input testuale generico nel form di recupero
    // (Assicurati che in recupero.php ci sia un input type="text" o "email")
    const input = document.querySelector('form input[type="text"], form input[type="email"]');
    
    if (input && input.value.trim() === "") {
        alert("Inserisci l'email o l'username.");
        return false;
    }
    return true;
}