document.addEventListener('DOMContentLoaded', async () => {

const latestContainer = document.getElementById('latestMovies');
const popularMoviesContainer = document.getElementById('popularMovies');
const popularSeriesContainer = document.getElementById('popularSeries');
const welcomeText = document.getElementById('welcomeText');
const registerButton = document.getElementById('registerButton');
const userMenu = document.getElementById('userMenu');

// ===  elementi zona utente ===
const statoUtente = document.getElementById("stato-utente");
const nomeUtente = document.getElementById("nome-utente");

// chiamo il mio file php
const response = await fetch('fetchMovies.php');
const data = await response.json();

// === AGGIUNTA: gestione stato login ===
if (statoUtente && nomeUtente) {
    if (data.user) {
        statoUtente.textContent = "Online";
        statoUtente.classList.add("online");
        statoUtente.classList.remove("offline");

        nomeUtente.textContent = data.user;
        nomeUtente.style.display = "inline";
    } else {
        statoUtente.textContent = "Offline";
        statoUtente.classList.add("offline");
        statoUtente.classList.remove("online");

        nomeUtente.style.display = "none";
    }
}

if (data.user) {
    welcomeText.textContent = `BENVENUTO ${data.user}! Cosa vuoi commentare oggi?`;
} else {
    welcomeText.textContent = `ACCEDI PER AVERE A DISPOSIZIONE TUTTE LE FUNZIONALITA'`;
}

function creaItem(item, isSeries = false) {
    const div = document.createElement('div');
    div.classList.add('movie-item');
    const title = isSeries ? item.name : item.title;
    const poster = item.poster_path
        ? `https://image.tmdb.org/t/p/w500${item.poster_path}`
        : 'https://placehold.jp/300x450.png?text=Immagine+non+disponibile';

    div.innerHTML = `
        <img src="${poster}" alt="${title}">
        <div class="movie-info">
            <h3>${title}</h3>
            <p>⭐ ${item.vote_average.toFixed(1)}</p>
        </div>
    `;
        div.addEventListener('click', () => {
        apriDettaglio(
            item.id,
            isSeries ? 'tv' : 'movie'
        );
    });
    return div;
}

data.recenti.forEach(movie => {
    latestContainer.appendChild(creaItem(movie));
});
data.filmPopolari.forEach(movie => {
    popularMoviesContainer.appendChild(creaItem(movie));
});
data.seriePopolari.forEach(serie => {
    popularSeriesContainer.appendChild(creaItem(serie, true));
});

registerButton.addEventListener('click', (e) => {
    e.stopPropagation();
    if (data.user) {
        userMenu.innerHTML = `
            <a href="paginapersonale.html">Area personale</a>
            <a href="logout.php">Logout</a>
        `;
    } else {
        userMenu.innerHTML = `
            <a href="login.php">Accedi</a>
        `;
    }
    userMenu.classList.toggle('hidden');
});

document.addEventListener('click', () => userMenu.classList.add('hidden'));

});