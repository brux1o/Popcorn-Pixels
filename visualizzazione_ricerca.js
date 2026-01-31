
document.addEventListener('DOMContentLoaded', () =>{
    if(!tmdbData || !tmdbData.results || tmdbData.results.length == 0){
        console.log("Nessun risultato o nessuna ricerca effettuata");
        return;
    }

    const container = document.getElementById('contenitore-film');

    const baseUrlImg = "https://image.tmdb.org/t/p/w342";

    container.innerHTML = '';

    tmdbData.results.forEach(item => {

        if (item.media_type === 'person') return;

        let titolo, data, tipoLabel;

        if (item.media_type === 'movie') {
            titolo = item.title;
            data = item.release_date;
            tipoLabel = '🎬 Film';
        } else if (item.media_type === 'tv') {
            titolo = item.name;
            data = item.first_air_date;
            tipoLabel = '📺 Serie TV';
        } else {
            titolo = item.title || item.name;
            data = item.release_date || item.first_air_date;
            tipoLabel = 'Media';
        }

        let locandinaUrl;

        if (item.poster_path) {
            locandinaUrl = baseUrlImg + item.poster_path;
        } else {
            locandinaUrl = "https://placehold.jp/300x450.png?text=Immagine+non+disponibile";
        }

        const dataLeggibile = data ? data : 'Data N/D'

        const card = document.createElement('div');
        card.className = 'card';

        card.style.cursor = "pointer";

        card.addEventListener('click',function(){
            apriDettaglio(item.id,item.media_type);
        });

        card.innerHTML = `
            <div class="card-image">
                <img src="${locandinaUrl}" alt="${titolo}">
            </div>
            
            <div class="card-content">
                <h3 class="card-title">${titolo}</h3>
                <p class="card-info">
                    <span class="tipo">${tipoLabel}</span> • 
                    <span class="data">${dataLeggibile}</span>
                </p>
            </div>
        `;

        container.appendChild(card);
    });


})