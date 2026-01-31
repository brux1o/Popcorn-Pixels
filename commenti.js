document.addEventListener('DOMContentLoaded', async () => {

    const commentiSection = document.getElementById('commenti');
    if (!commentiSection) return;

    const response = await fetch('get_commenti.php'); 
    const commenti = await response.json();

    commenti.forEach(commento => {
        creaCommento(commento);
    });

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

            <p class="comment-text">${commento.testo}</p>

            <div class="action-icons">
                <button class="btn-edit" data-id="${commento.id}">✏️</button>
                <button class="btn-delete" data-id="${commento.id}">🗑️</button>
            </div>
        `;

        commentiSection.appendChild(card);
    }

    commentiSection.addEventListener('click', async (e) => {

        const btnDelete = e.target.closest('.btn-delete');
        if (btnDelete) {
            const commentId = btnDelete.dataset.id;

            if (!confirm('Vuoi eliminare questo commento?')) return;

            const response = await fetch('delete_commento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${commentId}`
            });

            const result = await response.text();

            if (result.trim() === 'OK') {
                btnDelete.closest('.comment-card').remove();
            } else {
                alert('Errore durante eliminazione commento');
            }
        }

        const btnEdit = e.target.closest('.btn-edit');
        if (btnEdit) {
            const commentId = btnEdit.dataset.id;
            const card = btnEdit.closest('.comment-card');
            const testoP = card.querySelector('.comment-text');

            const nuovoTesto = prompt('Modifica commento:', testoP.textContent);

            if (nuovoTesto === null || nuovoTesto.trim() === "") return;

            const response = await fetch('update_commento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${commentId}&testo=${encodeURIComponent(nuovoTesto)}`
            });

            const result = await response.text();

            if (result.trim() === 'OK') {
                testoP.textContent = nuovoTesto;
            } else {
                alert('Errore durante modifica commento');
            }
        }
    });

});