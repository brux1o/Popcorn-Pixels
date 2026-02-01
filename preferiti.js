document.addEventListener('DOMContentLoaded', async () => {

    const preferitiSection = document.getElementById('preferiti');
    if (!preferitiSection) return;

    const movieGrid = preferitiSection.querySelector('.movie-grid');

    const response = await fetch('get_preferiti.php');
    const preferiti = await response.json();

    preferiti.forEach(preferito => creaPreferito(preferito));

   
    abilitaDragAndDropGrid('#preferiti .movie-grid', '.movie-card');

    function creaPreferito(preferito) {
        const card = document.createElement('div');
        card.classList.add('movie-card');
        card.setAttribute('draggable', true);

        const posterUrl = preferito.poster_path
            ? `https://image.tmdb.org/t/p/w500${preferito.poster_path}`
            : 'https://placehold.jp/300x450.png?text=Immagine+non+disponibile';

        card.innerHTML = `
            <img src="${posterUrl}">
            <h4>${preferito.titolo}</h4>
            <div class="action-icons">
                <button class="btn-delete" data-id="${preferito.content_id}">🗑️</button>
            </div>
        `;

        movieGrid.appendChild(card);
    }

    movieGrid.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;

        if (!confirm('Vuoi rimuovere dai preferiti?')) return;

        const response = await fetch('delete_preferito.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${btn.dataset.id}`
        });

        if ((await response.text()).trim() === 'OK') {
            btn.closest('.movie-card').remove();
            aggiornaStats();
        }
    });
}); 