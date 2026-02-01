document.addEventListener('DOMContentLoaded', async () => {

    const commentiSection = document.getElementById('commenti');
    if (!commentiSection) return;

    const response = await fetch('get_commenti.php');
    const commenti = await response.json();

    commenti.forEach(c => creaCommento(c));

    // ✅ ATTIVO DRAG & DROP
    abilitaDragAndDrop('#commenti', '.comment-card');

    function creaCommento(commento) {
        const card = document.createElement('div');
        card.classList.add('comment-card');
        card.setAttribute('draggable', true); // 🔴 FONDAMENTALE

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

        commentiSection.appendChild(card);
    }
});


/* =========================
   DRAG & DROP – VERSIONE STABILE
   ========================= */

function abilitaDragAndDrop(containerSelector, itemSelector) {

    const container = document.querySelector(containerSelector);
    if (!container) return;

    let draggedItem = null;

    container.addEventListener('dragstart', (e) => {
        const item = e.target.closest(itemSelector);
        if (!item) return;

        draggedItem = item;
        item.classList.add('dragging');
    });

    container.addEventListener('dragend', () => {
        if (draggedItem) draggedItem.classList.remove('dragging');
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