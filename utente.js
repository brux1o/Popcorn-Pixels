
document.addEventListener('DOMContentLoaded', async () => {

    const response = await fetch('get_user.php');

    if (!response.ok) { //CONTROLLO CHE IL PHP ABBIA RISPOSTO CORRETTAMENTE
        console.error('Errore nel recupero utente');
        return;
    }

    const utente = await response.json();

    document.getElementById('user-username').textContent = '@' + utente.username;
    document.getElementById('user-nome').textContent = utente.nome;
    document.getElementById('user-cognome').textContent = utente.cognome;
    document.getElementById('user-email').textContent = utente.email;

});