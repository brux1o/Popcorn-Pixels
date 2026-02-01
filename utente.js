

document.addEventListener('DOMContentLoaded', async () => {

    const response = await fetch('get_user.php');

    if (!response.ok) {
        console.error('Errore nel recupero utente');
        return;
    }

    const utente = await response.json();

    // Controllo se l'oggetto utente non è vuoto
    if (Object.keys(utente).length === 0) {
        console.error('Nessun dato utente trovato');
        return;
    }

 
    const elUsername = document.getElementById('user-username');
    const elNome = document.getElementById('user-nome');
    const elCognome = document.getElementById('user-cognome');
    const elEmail = document.getElementById('user-email');

    if (elUsername) elUsername.textContent = utente.username;
    if (elNome) elNome.textContent = utente.nome;
    if (elCognome) elCognome.textContent = utente.cognome;
    if (elEmail) elEmail.textContent = utente.email;

   
    const avatar = document.querySelector('.profile-avatar');
    if (avatar && utente.immagine_profilo) {
        avatar.src = utente.immagine_profilo;
    }

})