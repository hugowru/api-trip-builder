<?php

    require __DIR__ . "/inc/startingFiles.php";

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $uri = explode( '/', $uri );

    if ((isset($uri[3]) && $uri[3] != 'trip') || !isset($uri[4])) { //depenting of the root folder, 2 or more
        header("HTTP/1.1 404 Not Found"); // is the url doesn't match 404
        exit();
    }

    require PROJECT_ROOT_PATH . "/Controller/Api/TripController.php";

    $objProviderController = new TripController();

    $startMethodName = $uri[4] . 'Action';

    $objProviderController->{$startMethodName}();
?>