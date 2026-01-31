

document.addEventListener('DOMContentLoaded', async () => {

const preferitiSection = document.getElementById('preferiti');
const movieGrid = preferitiSection.querySelector('.movie-grid');


const response = await fetch('get_preferiti.php');
const preferiti = await response.json();

 preferiti.forEach(preferito => {
        creaPreferito(preferito);
    });

function creaPreferito(preferito){
 const card = document.createElement('div');
        card.classList.add('movie-card');

       const posterUrl = preferito.poster_path
        ? `https://image.tmdb.org/t/p/w500${preferito.poster_path}`
        : 'https://placehold.jp/300x450.png?text=Immagine+non+disponibile';

    card.innerHTML = `
        <img src="${posterUrl}" alt="${preferito.titolo}">
        <h4>${preferito.titolo}</h4>

        <div class="action-icons">
            <button class="btn-delete" data-id="${preferito.id}" title="Rimuovi dai preferiti">
                🗑️
            </button>
        </div>
    `;

        movieGrid.appendChild(card);

}

movieGrid.addEventListener('click', async (e) => {

        if (!e.target.classList.contains('btn-delete')) return;

        const preferitoId = e.target.dataset.id;

        if (!confirm('Vuoi rimuovere questo contenuto dai preferiti?')) return;

        const response = await fetch('delete_preferito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${preferitoId}`
        });

        const result = await response.text();

        if (result === 'OK') {
            e.target.closest('.movie-card').remove();
        } else {
            alert('Errore durante la rimozione dai preferiti');
        }
    });



});