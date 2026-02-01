

document.addEventListener('DOMContentLoaded', async () => {

    const commentiSection = document.getElementById('commenti');
    if (!commentiSection) return;

    const response = await fetch('get_commenti.php');
    const commenti = await response.json();

    commenti.forEach(c => creaCommento(c));

    // ✅ DRAG & DROP (solo asse Y)
    abilitaDragAndDrop('#commenti', '.comment-card');

    function creaCommento(commento) {
        const card = document.createElement('div');
        card.classList.add('comment-card');
        card.setAttribute('draggable', true);

        card.innerHTML = `
            <div class="comment-header">
                <span>${commento.titolo}</span>
                <span>${commento.data_inserimento}</span>
            </div>

            <p class="comment-text">${commento.testo}</p>

            <div class="action-icons">
                <button class="btn-edit" data-id="${commento.id}">✏️</button>
                <button class="btn-delete" data-id="${commento.id}">🗑️</button>
            </div>
        `;

        // ✅ DISABILITA IL DRAG SOLO QUANDO CLICCHI I BOTTONI
        card.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('mousedown', () => {
                card.setAttribute('draggable', false);
            });

            btn.addEventListener('mouseup', () => {
                card.setAttribute('draggable', true);
            });
        });

        commentiSection.appendChild(card);
    }

    /* =========================
       CLICK EDIT / DELETE
       ========================= */

    commentiSection.addEventListener('click', async (e) => {

        // 🗑️ DELETE
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            if (!confirm('Vuoi eliminare il commento?')) return;

            const id = deleteBtn.dataset.id;

            const response = await fetch('delete_commento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            });

            if ((await response.text()).trim() === 'OK') {
                deleteBtn.closest('.comment-card').remove();
                 if (typeof aggiornaStats === 'function') {
                        aggiornaStats();}
            } else {
                alert('Errore eliminazione commento');
            }
            return;
        }

        // ✏️ EDIT
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            const id = editBtn.dataset.id;
            const nuovoTesto = prompt('Modifica commento:');
            if (!nuovoTesto) return;

            const response = await fetch('update_commento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&testo=${encodeURIComponent(nuovoTesto)}`
            });

            if ((await response.text()).trim() === 'OK') {
                editBtn
                    .closest('.comment-card')
                    .querySelector('.comment-text')
                    .textContent = nuovoTesto;
            } else {
                alert('Errore modifica commento');
            }
        }
    });

});


/* =========================
   DRAG & DROP – FIX DEFINITIVO
   ========================= */

function abilitaDragAndDrop(containerSelector, itemSelector) {

    const container = document.querySelector(containerSelector);
    if (!container) return;

    let draggedItem = null;

    container.addEventListener('dragstart', (e) => {
        // 🔒 BLOCCA DRAG SE PARTI DA UN BOTTONE
        if (e.target.closest('button')) return;

        const item = e.target.closest(itemSelector);
        if (!item) return;

        draggedItem = item;
        item.classList.add('dragging');
    });

    container.addEventListener('dragend', () => {
        document
            .querySelectorAll('.dragging')
            .forEach(el => el.classList.remove('dragging'));

        draggedItem = null;
    });

    container.addEventListener('dragover', (e) => {
        e.preventDefault();

        const afterElement = getAfterElement(container, itemSelector, e.clientY);

        if (!afterElement) {
            container.appendChild(draggedItem);
        } else {
            container.insertBefore(draggedItem, afterElement);
        }
    });
}

function getAfterElement(container, itemSelector, y) {
    const elements = [...container.querySelectorAll(`${itemSelector}:not(.dragging)`)];

    return elements.reduce((closest, el) => {
        const box = el.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset, element: el };
        }

        return closest;
    }, { offset: -Infinity }).element;
}