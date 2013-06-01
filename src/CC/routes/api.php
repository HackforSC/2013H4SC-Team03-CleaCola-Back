<?php

$app->get('/api', function() use ($app) {
    $app->render('api.php');
});

$app->get('/api/v1/incidents', function() use ($app) {
    $latlng = $app->request()->get('latlng');
    $range = $app->request()->get('range');
    $category = $app->request()->get('category_id');

    // return all incidents w/i range of latlng of category

});
