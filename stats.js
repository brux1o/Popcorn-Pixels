

document.addEventListener('DOMContentLoaded', async () => {

    const numPreferiti = document.getElementById('num-preferiti');
    const numWatchlist = document.getElementById('num-watchlist');
    const numCommenti  = document.getElementById('num-commenti');

    // se per qualche motivo gli elementi non esistono, esco
    if (!numPreferiti || !numWatchlist || !numCommenti) return;

    const response = await fetch('get_stats.php');
    const stats = await response.json();

    numPreferiti.textContent = stats.preferiti;
    numWatchlist.textContent = stats.watchlist;
    numCommenti.textContent  = stats.commenti;

})