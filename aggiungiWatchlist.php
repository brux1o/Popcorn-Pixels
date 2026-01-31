<script>
function aggiungiWatchlist(id, tipo, titolo, poster){
    
    const loggato = <?php echo $logger ? 'true' : 'false'; ?>;

    if(!loggato){
        alert("Attenzione: devi aver effettuato l'accesso per aggiungere elementi alla watchlist");
        return;
    }

    const params = "id=" + id + 
                   "&type=" + tipo + 
                   "&titolo=" + encodeURIComponent(titolo) + 
                   "&poster=" + encodeURIComponent(poster);

    fetch('watchlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    })
    .then(response => {
        if (!response.ok) {
            alert("Errore Server: " + response.status);
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;

        if(data.success === true){
            alert("Aggiunto alla watchlist! 🍿");
        } else {
            alert("Attenzione: " + (data.message || "Impossibile aggiungere"));
        }
    });
}
</script>