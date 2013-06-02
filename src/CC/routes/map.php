<?php

$app->get('/map', function() use ($app) {
    $app->render('map.php');
});
