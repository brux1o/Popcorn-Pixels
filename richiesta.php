<?php

    require_once __DIR__ . '/config.php';

    if(isset($_GET['query'])){
        $searchQuery=urlencode($_GET['query']); 

        $url = TMDB_BASE_URL . "/search/multi?api_key=" . api_key . "&query=" . $searchQuery;

        $response=file_get_contents($url);  

       if($response !== false){
        $jsonPerJavascript = $response;
       }
    }
?>