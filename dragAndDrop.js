function abilitaDragAndDropGrid(containerSelector, itemSelector) {

    const container = document.querySelector(containerSelector);
    if (!container) return;

    let draggedItem = null;

    container.addEventListener('dragstart', (e) => {
        const item = e.target.closest(itemSelector);
        if (!item) return;

        draggedItem = item;
        item.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    container.addEventListener('dragend', () => {
        if (draggedItem) draggedItem.classList.remove('dragging');
        draggedItem = null;
    });

    container.addEventListener('dragover', (e) => {
        e.preventDefault();

        const target = document.elementFromPoint(e.clientX, e.clientY);
        const targetCard = target?.closest(itemSelector);

        if (!targetCard || targetCard === draggedItem) return;

        const rect = targetCard.getBoundingClientRect();

        const shouldInsertAfter =
            e.clientY > rect.top + rect.height / 2 ||
            e.clientX > rect.left + rect.width / 2;

        if (shouldInsertAfter) {
            targetCard.after(draggedItem);
        } else {
            targetCard.before(draggedItem);
        }
    });
} 