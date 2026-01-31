function validateLogin() {
    let user = document.getElementById("login_username").value.trim();
    let pass = document.getElementById("login_password").value;

    if (user === "" && pass === "") {
        alert("Inserisci sia username che password per accedere.");
        return false;
    }
    if (user === "") {
        alert("Il campo username non può essere vuoto.");
        return false;
    }
    if (pass === "") {
        alert("Inserisci la password.");
        return false;
    }
    return true;
}

function validateRegister() {
    let user = document.getElementById("reg_username").value.trim();
    let email = document.getElementById("reg_email").value.trim();
    let pass = document.getElementById("reg_password").value;
    let answer = document.getElementById("reg_answer").value.trim();

    if (user.length < 3) {
        alert("L'username deve contenere almeno 3 caratteri");
        return false;
    }

    if (!email.includes("@")) {
        alert("Inserisci un'email valida");
        return false;
    }

    if (pass.length < 6) {
        alert("La password deve contenere almeno 6 caratteri");
        return false;
    }

    if (answer.length < 2) {
        alert("La risposta alla domanda di sicurezza è troppo corta");
        return false;
    }

    return true;
}

function validateReset() {
    let pass = document.getElementById("new_password").value;

    if (pass.length < 6) {
        alert("La nuova password deve contenere almeno 6 caratteri");
        return false;
    }
    return true;
}