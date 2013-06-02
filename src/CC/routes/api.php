<?php

$app->get('/', function () use ($app) {
    $app->response()->header('Content-Type', 'text/html');
    $app->render('api.php');
});

$app->get('/v1/incidents', function () use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $latlng = $app->request()->get('latlng');
    $range = $app->request()->get('range');
    $category_id = $app->request()->get('category_id');

    // define the defaults
    if (is_null($latlng)) {
        // use columbia, sc
        $latlng = '34.0006,-81.0350';
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
        SELECT ((ACOS(SIN(:latitude * PI() / 180) * SIN(Incidents.latitude * PI() / 180) + COS(:latitude * PI() / 180) * COS(Incidents.latitude * PI() / 180) * COS((:longitude - Incidents.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance, Incidents.id, latitude, longitude, description, Incidents.date_created, is_flagged, is_closed, category_id
        FROM Incidents
        WHERE category_id = :category_id AND is_closed = \'0000-00-00 00:00:00\'
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
    $app->response()->header('Content-Type', 'application/json');

    $db = \CC\Helper\DB::instance();
    $get_stmt = $db->prepare('
        SELECT Incidents.id, latitude, longitude, description, Incidents.date_created, is_flagged, is_closed, category_id, COUNT(IncidentVotes.id) as votes
        FROM Incidents
        LEFT JOIN IncidentVotes ON IncidentVotes.incident_id = Incidents.id
        WHERE Incidents.id = :id
    ');

    $get_stmt->execute(array(
        ':id' => $incident_id
    ));
    $get_stmt->setFetchMode(\PDO::FETCH_INTO, new \CC\Model\Incident());
    $incident = $get_stmt->fetch();

    $app->response()->write(json_encode($incident));
});

$app->get('/v1/incidents/:id/images', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $db = \CC\Helper\DB::instance();
    $get_stmt = $db->prepare('
        SELECT image_src
        FROM IncidentPhotos
        WHERE incident_id = :incident_id
    ');

    $get_stmt->execute(array(
        ':incident_id' => $incident_id
    ));
    $incident = $get_stmt->fetchAll(\PDO::FETCH_ASSOC);

    $app->response()->write(json_encode($incident));
});

$app->post('/v1/incidents', function () use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $latitude = $app->request()->post('latitude');
    $longitude = $app->request()->post('longitude');
    $category_id = $app->request()->post('category_id');
    $description = $app->request()->post('description');

    if ($latitude == false || $longitude == false || $category_id == false || $description == false) {
        $app->response()->status(404);
        exit();
    }

    $db = \CC\Helper\DB::instance();
    $insert_stmt = $db->prepare('
        INSERT INTO Incidents (latitude, longitude, description, category_id)
        VALUES (:latitude, :longitude, :description, :category_id)
    ');
    $insert_stmt->execute(array(
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':description' => $description,
        ':category_id' => $category_id
    ));

    $incident_id = $db->lastInsertId();

    if (isset($_FILES['image']) == true && $_FILES['image']['name'] != '') {
        $db = CC\Helper\DB::instance();

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
            $file->upload($incident_id . uniqid('_'));
            $filename = $file->getNameWithExtension();
        } catch (\Exception $e) {
            // Fail!
            \Slim\Slim::getInstance()->getLog()->error('Failed to upload file on create!');
        }

        $insert_stmt = $db->prepare('
            INSERT INTO IncidentPhotos (incident_id, image_src)
            VALUES (:incentive_id, :image_src)
        ');
        $insert_stmt->execute(array(
            ':incentive_id' => $incident_id,
            ':image_src' => $filename
        ));
    }

    $app->response()->status(200);
});

$app->post('/v1/incidents/:id/images', function($incident_id) use ($app) {
    if (isset($_FILES['image']) == true && $_FILES['image']['name'] != '') {
        $db = CC\Helper\DB::instance();

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
            $file->upload($incident_id . uniqid('_'));
            $filename = $file->getNameWithExtension();
        } catch (\Exception $e) {
            // Fail!
            \Slim\Slim::getInstance()->getLog()->error('Failed to upload file on create!');
        }

        $insert_stmt = $db->prepare('
            INSERT INTO IncidentPhotos (incident_id, image_src)
            VALUES (:incentive_id, :image_src)
        ');
        $insert_stmt->execute(array(
            ':incentive_id' => $incident_id,
            ':image_src' => $filename
        ));
    }
});

$app->get('/v1/categories', function () use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $db = \CC\Helper\DB::instance();
    $get_stmt = $db->prepare('
        SELECT id, title, date_created
        FROM Categories
    ');
    $get_stmt->setFetchMode(PDO::FETCH_CLASS, 'CC\Model\Category');
    $get_stmt->execute();
    $categories = $get_stmt->fetchAll();

    $app->response()->write(json_encode(array(
        'categories' => $categories
    )));
});

$app->post('/v1/incidents/:id/attend', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $db = \CC\Helper\DB::instance();
    $insert_stmt = $db->prepare('
        UPDATE Incidents
        SET attending_count = attending_count + 1
        WHERE id = :incident_id
    ');
    $insert_stmt->execute(array(
        ':incident_id' => $incident_id
    ));

    $app->response()->status(200);
});

$app->post('/v1/incidents/:id/flag', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $db = \CC\Helper\DB::instance();
    $update_stmt = $db->prepare('
        UPDATE Incidents
        SET is_flagged = NOW()
        WHERE id = :id
    ');
    $update_stmt->execute(array(
        ':id' => $incident_id
    ));

    $app->response()->status(200);
});

$app->post('/v1/incidents/:id/close', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $db = \CC\Helper\DB::instance();
    $update_stmt = $db->prepare('
        UPDATE Incidents
        SET is_closed = NOW()
        WHERE id = :id
    ');
    $update_stmt->execute(array(
        ':id' => $incident_id
    ));

    $app->response()->status(200);
});

$app->post('/v1/incidents/:id/open', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');

    $db = \CC\Helper\DB::instance();
    $update_stmt = $db->prepare('
        UPDATE Incidents
        SET is_closed = null
        WHERE id = :id
    ');
    $update_stmt->execute(array(
        ':id' => $incident_id
    ));

    $app->response()->status(200);
});
