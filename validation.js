function validateLogin() {
    const userInput = document.querySelector('input[name="login_input"]');
    const passInput = document.querySelector('input[name="login_password"]');

    if (!userInput || !passInput) {
        return true; 
    }

    const userVal = userInput.value.trim();
    const passVal = passInput.value.trim();

    // Se uno dei due è vuoto, blocca e avvisa
    if (userVal === "" || passVal === "") {
        alert("Per accedere devi inserire sia username/email che password.");
        return false; 
    }

    return true; 
}

function validateRegister() {
    const nome = document.querySelector('input[name="reg_nome"]').value.trim();
    const cognome = document.querySelector('input[name="reg_cognome"]').value.trim();
    const email = document.querySelector('input[name="reg_email"]').value.trim();
    const user = document.querySelector('input[name="reg_username"]').value.trim();
    const pass = document.querySelector('input[name="reg_password"]').value; 
    const domanda = document.querySelector('select[name="reg_domanda"]').value;
    const risposta = document.querySelector('input[name="reg_risposta"]').value.trim();

    if (!nome || !cognome || !email || !user || !pass || domanda === "" || !risposta) {
        alert("Tutti i campi sono obbligatori per la registrazione.");
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Inserisci un indirizzo email valido (es: nome@mail.com).");
        return false;
    }

    if (pass.length < 8) {
        alert("La password deve essere di almeno 8 caratteri.");
        return false;
    }

    return true;
}

function validateRecupero() {
    const input = document.querySelector('form input[type="text"], form input[type="email"]');
    
    if (input && input.value.trim() === "") {
        alert("Inserisci l'email o l'username.");
        return false;
    }
    return true;
}