
document.addEventListener('DOMContentLoaded',async()=>{

const latestContainer=document.getElementById('latestMovies');
const popularMoviesContainer=document.getElementById('popularMovies');
const popularSeriesContainer=document.getElementById('popularSeries');
const welcomeText=document.getElementById('welcomeText');
const registerButton=document.getElementById('registerButton');
const userMenu=document.getElementById('userMenu');


//chiamo il mio file php
const response= await fetch('fetchMovies.php');
const data= await response.json();


if(data.user){
   
    welcomeText.textContent=`BENVENUTO ${data.user}! Cosa vuoi commentare oggi?`;
     
 }
else {
    welcomeText.textContent=`ACCEDI PER AVERE A DISPOSIZIONE TUTTE LE FUNZIONALITA'`;
}

function creaItem(item,isSeries=false){
    const div=document.createElement('div');
    div.classList.add('movie-item');
    const title=isSeries? item.name :item.title;
   const poster = item.poster_path
    ? `https://image.tmdb.org/t/p/w500${item.poster_path}`
    : 'https://placehold.jp/300x450.png?text=Immagine+non+disponibile';
    div.innerHTML=`
            <img src="${poster}" alt="${title}">
            <div class="movie-info">
                <h3>${title}</h3>
                <p>⭐ ${item.vote_average.toFixed(1)}</p>
            </div>
        `;
        return div;
}

data.recenti.forEach(movie=>{
    latestContainer.appendChild(creaItem(movie));
});
data.filmPopolari.forEach(movie=>{
    popularMoviesContainer.appendChild(creaItem(movie));
});
data.seriePopolari.forEach(serie=>{
    popularSeriesContainer.appendChild(creaItem(serie,true));
});

registerButton.addEventListener('click',(e)=>{
  e.stopPropagation();
  if(data.user){
     userMenu.innerHTML = `
                <a href="area_personale.php">Area personale</a>
                <a href="logout.php">Logout</a>
            `;
  }else{
    userMenu.innerHTML = `
                <a href="login.php">Accedi</a>
                <a href="register.php">Registrati</a>
            `;
  }
   userMenu.classList.toggle('hidden'); // Mostra/nascondi menu
});
 document.addEventListener('click', () => userMenu.classList.add('hidden'));


});