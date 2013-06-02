<?php

$app->get('/', function () use ($app) {
    $app->response()->header('Content-Type', 'text/html');
    $app->render('api.php');
});

$app->get('/incidents', function () use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

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
        $category_id = 0;
    }

    $latlng_split = explode(',', $latlng);
    $latitude = $latlng_split[0];
    $longitude = $latlng_split[1];

    $db = \CC\Helper\DB::instance();
    $stmt = $db->prepare('
        SELECT ((ACOS(SIN(:latitude * PI() / 180) * SIN(Incidents.latitude * PI() / 180) + COS(:latitude * PI() / 180) * COS(Incidents.latitude * PI() / 180) * COS((:longitude - Incidents.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance, Incidents.id, latitude, longitude, description, Incidents.date_created, is_flagged, is_closed, category_id, title
        FROM Incidents
        HAVING distance <= :range
    ');
    $stmt->execute(array(
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':range' => $range
    ));

    $incidents = $stmt->fetchAll(\PDO::FETCH_CLASS, 'CC\Model\Incident');

    $app->response()->write(json_encode(array('incidents' => $incidents)));
});

$app->get('/incidents/images', function() use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

    $db = \CC\Helper\DB::instance();
    $get_stmt = $db->prepare('
        SELECT image_src
        FROM IncidentPhotos
    ');

    $get_stmt->execute();
    $incident = $get_stmt->fetchAll(\PDO::FETCH_ASSOC);

    $app->response()->write(json_encode(array(
        'images' => $incident
    )));
});

$app->get('/incidents/:id', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

    $db = \CC\Helper\DB::instance();
    $get_stmt = $db->prepare('
        SELECT Incidents.id, title, latitude,  longitude, description, Incidents.date_created, is_flagged, is_closed, category_id, COUNT(IncidentVotes.id) AS votes, incidentphotos.image_src
        FROM Incidents
        LEFT JOIN IncidentVotes ON IncidentVotes.incident_id = Incidents.id
        LEFT JOIN IncidentPhotos ON IncidentPhotos.incident_id = Incidents.id
        WHERE Incidents.id = :id
    ');

    $get_stmt->execute(array(
        ':id' => $incident_id
    ));
    $get_stmt->setFetchMode(\PDO::FETCH_INTO, new \CC\Model\Incident());
    $incident = $get_stmt->fetch();

    $app->response()->write(json_encode(array(
        'incident' => $incident
    )));
});

$app->get('/incidents/:id/images', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

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

$app->post('/incidents', function () use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

    $latitude = $app->request()->post('latitude');
    $longitude = $app->request()->post('longitude');
    $category_id = $app->request()->post('category_id');
    $description = $app->request()->post('description');
    $title = $app->request()->post('title');

    if (is_null($latitude) || is_null($longitude) || is_null($category_id) || is_null($description) || is_null($title)) {
        $app->halt(406);
    }

    $db = \CC\Helper\DB::instance();
    $insert_stmt = $db->prepare('
        INSERT INTO Incidents (latitude, longitude, description, category_id, title)
        VALUES (:latitude, :longitude, :description, :category_id, :title)
    ');
    $insert_stmt->execute(array(
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':description' => $description,
        ':category_id' => $category_id,
        ':title' => $title
    ));

    $incident_id = $db->lastInsertId();

    if (isset($_FILES['image']) == true && $_FILES['image']['name'] != '') {
        $db = CC\Helper\DB::instance();

        $filename = null;

        $storage = new \Upload\Storage\FileSystem(realpath(dirname(__FILE__) . '/../../../public_html/images/incidents/'));
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
            ':image_src' => 'http://api.cleancola.org/images/incidents/' . $filename
        ));
    }

    $db = \CC\Helper\DB::instance();
    $get_stmt = $db->prepare('
        SELECT Incidents.id, title, latitude, longitude, description, Incidents.date_created, is_flagged, is_closed, category_id, COUNT(IncidentVotes.id) AS votes
        FROM Incidents
        LEFT JOIN IncidentVotes ON IncidentVotes.incident_id = Incidents.id
        WHERE Incidents.id = :id
    ');

    $get_stmt->execute(array(
        ':id' => $incident_id
    ));
    $get_stmt->setFetchMode(\PDO::FETCH_INTO, new \CC\Model\Incident());
    $incident = $get_stmt->fetch();

    $app->response()->status(200);
    $app->response()->write(json_encode(array(
        'incident' => $incident
    )));
});

$app->post('/incidents/:id/images', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

    if (isset($_FILES['image']) == true && $_FILES['image']['name'] != '') {
        $db = CC\Helper\DB::instance();

        $filename = null;

        $storage = new \Upload\Storage\FileSystem(realpath(dirname(__FILE__) . '/../../../public_html/images/incidents/'));
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
            ':image_src' => 'http://api.cleancola.org/images/incidents/' . $filename
        ));
    }

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

    $app->response()->write(json_encode(array(
        'images' => $incident
    )));
});

$app->get('/categories', function () use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

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

$app->post('/incidents/:id/attend', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

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

$app->post('/incidents/:id/flag', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

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

$app->post('/incidents/:id/close', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

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

$app->post('/incidents/:id/open', function ($incident_id) use ($app) {
    $app->response()->header('Content-Type', 'application/json');
    $app->response()->header('Api-Version', '1');

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
