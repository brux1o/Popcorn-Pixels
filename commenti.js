document.addEventListener('DOMContentLoaded', async () => {

    const commentiSection = document.getElementById('commenti');

    // PRENDO I COMMENTI DAL PHP
    const response = await fetch('get_commenti.php');
    const commenti = await response.json();

    // CREO TUTTE LE CARD DEI COMMENTI
    commenti.forEach(commento => {
        creaCommento(commento);
    });

    // ==========================
    // CREA SINGOLO COMMENTO
    // ==========================
    function creaCommento(commento) {

        const card = document.createElement('div');
        card.classList.add('comment-card');

        card.innerHTML = `
            <div class="comment-header">
                <span class="comment-film">
                    TITOLO: ${commento.titolo}
                </span>
                <span class="comment-date">
                    ${commento.data_inserimento}
                </span>
            </div>

            <p>${commento.testo}</p>

            <div class="action-icons">
                <button class="btn-edit" data-id="${commento.id}">✏️</button>
                <button class="btn-delete" data-id="${commento.id}">🗑️</button>
            </div>
        `;

        commentiSection.appendChild(card);
    }

    // ==========================
    // EVENTI MODIFICA / ELIMINA
    // ==========================
    commentiSection.addEventListener('click', async (e) => {

        // ===== ELIMINA COMMENTO =====
        if (e.target.classList.contains('btn-delete')) {

            const commentId = e.target.dataset.id;

            if (!confirm('Vuoi eliminare questo commento?')) return;

            const response = await fetch('delete_commento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${commentId}`
            });

            const result = await response.text();

            if (result === 'OK') {
                e.target.closest('.comment-card').remove();
            } else {
                alert('Errore durante eliminazione commento');
            }
        }

        // ===== MODIFICA COMMENTO =====
        if (e.target.classList.contains('btn-edit')) {

            const commentId = e.target.dataset.id;
            const card = e.target.closest('.comment-card');
            const testoP = card.querySelector('p');

            const nuovoTesto = prompt('Modifica commento:', testoP.textContent);

            if (!nuovoTesto) return;

            const response = await fetch('update_commento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${commentId}&testo=${encodeURIComponent(nuovoTesto)}`
            });

            const result = await response.text();

            if (result === 'OK') {
                testoP.textContent = nuovoTesto;
            } else {
                alert('Errore durante modifica commento');
            }
        }

    });

});