document.addEventListener('DOMContentLoaded', async () => {

    const watchlistSection = document.getElementById('watchlist');
    const movieGrid = watchlistSection.querySelector('.movie-grid');

    // prendo la watchlist dal PHP
    const response = await fetch('get_watchlist.php');
    const watchlist = await response.json();

    watchlist.forEach(item => {
        creaWatchlist(item);
    });

    function creaWatchlist(item) {
        const card = document.createElement('div');
        card.classList.add('movie-card');
         const posterUrl = item.poster_path
        ? `https://image.tmdb.org/t/p/w500${item.poster_path}`
        : 'https://placehold.jp/300x450.png?text=Immagine+non+disponibile';

    card.innerHTML = `
        <img src="${posterUrl}">
        <h4>${item.titolo}</h4>

        <div class="action-icons">
            <button class="btn-delete" data-id="${item.id}">🗑️</button>
        </div>
    `;
       
        movieGrid.appendChild(card);
    }

    // elimina dalla watchlist
    movieGrid.addEventListener('click', async (e) => {

        if (!e.target.classList.contains('btn-delete')) return;

        const id = e.target.dataset.id;

        if (!confirm('Vuoi rimuovere dalla watchlist?')) return;

        const response = await fetch('delete_watchlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        });

        const result = await response.text();

        if (result === 'OK') {
            e.target.closest('.movie-card').remove();
        } else {
            alert('Errore eliminazione');
        }
    });

});