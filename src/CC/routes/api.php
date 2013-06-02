<?php

$app->response()->header('Content-Type', 'application/json');

$app->get('/', function () use ($app) {
    $app->response()->header('Content-Type', 'text/html');
    $app->render('api.php');
});

$app->get('/v1/incidents', function () use ($app) {
    $latlng = $app->request()->get('latlng');
    $range = $app->request()->get('range');
    $category_id = $app->request()->get('category_id');

    // define the defaults
    if (is_null($latlng)) {
        // use columbia, sc
        $latlng = '34.0006,81.0350';
    }

    if (is_null($range)) {
        $range = 20;
    }

    if (is_null($category_id)) {
        $category_id = 1;
    }

    $latlng_split = explode(',', $latlng);
    $latitude = $latlng_split[0];
    $longitude = $latlng_split[1];

    $db = \CC\Helper\DB::instance();
    $stmt = $db->prepare('
        SELECT ((ACOS(SIN(:latitude * PI() / 180) * SIN(Incidents.latitude * PI() / 180) + COS(:latitude * PI() / 180) * COS(Incidents.latitude * PI() / 180) * COS((:longitude - Incidents.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance, id, latitude, longitude, description, date_created, is_flagged, is_closed, category_id
        FROM Incidents
        WHERE category_id = :category_id
        HAVING distance <= :range
    ');
    $stmt->execute(array(
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':range' => $range,
        ':category_id' => $category_id
    ));

    $incidents = $stmt->fetchAll(\PDO::FETCH_CLASS, 'CC\Model\Incident');

    $app->response()->write(json_encode(array('incidents' => $incidents)));
});

$app->get('/v1/incidents/:id', function ($incident_id) use ($app) {
    $db = \CC\Helper\DB::instance();
    $get_stmt = $db->prepare('
        SELECT id, latitude, longitude, description, date_created, is_flagged, is_closed, category_id
        FROM Incidents
        WHERE id = :id
    ');

    $get_stmt->execute(array(
        ':id' => $incident_id
    ));
    $get_stmt->setFetchMode(\PDO::FETCH_INTO, new \CC\Model\Incident());
    $incident = $get_stmt->fetch();

    $app->response()->write(json_encode($incident));
});

$app->post('/v1/incidents', function () use ($app) {
    /*$latlng = $app->request()->post('latlng');
    $category_id = $app->request()->post('category_id');
    $description = $app->request()->post('description');

    $latlng_split = explode(',' ,$latlng);
    $latitude = $latlng_split[0];
    $longitude = $latlng_split[1];

    $db = \CC\Helper\DB::instance();
    $insert_stmt = $db->prepare('
        INSERT INTO Incentives (latitude, longitude, description, category_id)
        VALUES (:latitude, :longitude, :description, :category_id)
    ');
    $insert_stmt->execute(array(
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':description' => $description,
        ':category_id' => $category_id
    ));

    if (isset($_FILES['image']) == true && $_FILES['image']['name'] != '') {
        $filename = null;

        $storage = new \Upload\Storage\FileSystem(realpath(dirname(__FILE__) . '/../../../uploads/'));
        $file = new \Upload\File('image', $storage);

        // Validate file upload
        $file->addValidations(array(
            // Ensure file is no larger than 5M (use "B", "K", M", or "G")
            new \Upload\Validation\Size('5M')
        ));

        // Try to upload file
        try {
            // Success!
            $file->upload($this->id . uniqid('_'));
            $filename = $file->getNameWithExtension();
        } catch (\Exception $e) {
            // Fail!
            \Slim\Slim::getInstance()->getLog()->error('Failed to upload file on create!');
        }

        $this->lesson_upload_file_name = $filename;
    }*/

});

$app->get('/v1/categories', function () use ($app) {

});

$app->post('/v1/incidents/:id/vote', function ($id) use ($app) {

});

$app->post('/v1/incidents/:id/flag', function ($id) use ($app) {

});

$app->post('/v1/incidents/:id/close', function ($id) use ($app) {

});

$app->post('/v1/incidents/:id/open', function ($id) use ($app) {

});