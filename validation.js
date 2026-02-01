/* CONTROLLI LATO CLIENT */

function validateLogin() {
    const user = document.querySelector('input[name="log_user"]').value;
    const pass = document.querySelector('input[name="log_pass"]').value;
    if (user.trim() === "" || pass.trim() === "") {
        alert("Inserisci tutti i campi.");
        return false;
    }
    return true;
}

function validateRegister() {
    const nome = document.querySelector('input[name="reg_nome"]').value;
    const email = document.querySelector('input[name="reg_email"]').value;
    const pass = document.querySelector('input[name="reg_pass"]').value;
    if (nome === "" || email === "" || pass === "") {
        alert("Compila tutti i campi obbligatori.");
        return false;
    }
    if (pass.length < 4) {
        alert("La password è troppo corta (min 4 caratteri).");
        return false;
    }
    return true;
}

function validateRecupero() {
    const input = document.querySelector('input[name="input_user"]').value;
    if (input.trim() === "") {
        alert("Inserisci email o username.");
        return false;
    }
    return true;
}

function validateReset() {
    const code = document.querySelector('input[name="code"]').value;
    const newPass = document.querySelector('input[name="new_pass"]').value;
    if (code.length !== 3) {
        alert("Il codice deve essere di 3 cifre.");
        return false;
    }
    if (newPass === "") {
        alert("Inserisci la nuova password.");
        return false;
    }
    return true;
}