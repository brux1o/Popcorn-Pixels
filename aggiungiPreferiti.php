<script>
function aggiungiPreferiti(id, tipo, titolo, poster){

    const loggato = <?php echo $logger ? 'true' : 'false'; ?>;

    if(!loggato){
        alert("Attenzione: devi aver effettuato l'accesso per aggiungere elementi ai preferiti!");
        return;
    }

    const params = "id=" + id + 
                   "&type=" + tipo + 
                   "&titolo=" + encodeURIComponent(titolo) + 
                   "&poster=" + encodeURIComponent(poster);

    fetch('/preferiti.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(response => {
        if(!response.ok) {
            alert("Errore Server: " + response.status);
            return null;
        }
        return response.json();
    })
    .then(data => {
        if(!data) return;

        if(data.success === true){
            alert("Aggiunto ai preferiti! ❤️");
        } 
        else if(data.code == 'limite_raggiunto'){
            
            let risposta = prompt("Hai raggiunto il limite di 5 preferiti!\nScrivi il titolo esatto del film da rimuovere per far posto a questo:");

            if (risposta !== null && risposta.trim() !== "") {
                fetch('/preferiti.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params + "&action=scambio&titolo_da_rimuovere=" + encodeURIComponent(risposta)
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert("Sostituzione completata! Il nuovo film è stato salvato.");
                    } else {
                        alert("Errore: " + (res.message || "Impossibile effettuare lo scambio."));
                    }
                });
            }
        } 
        else {
            alert(data.message || "Impossibile aggiungere.");
        }
    });
}
</script>