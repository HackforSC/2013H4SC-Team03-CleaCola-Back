<?php

$db = \CC\Helper\DB::instance();
$get_stmt = $db->prepare('
    SELECT title, description, latitude, longitude
    FROM Incidents
');
$get_stmt->execute();

$incidents = $get_stmt->fetchAll(\PDO::FETCH_CLASS, 'CC\Model\Incident');

?>
<!DOCTYPE html>
<html>
<head>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
    <script src='http://api.tiles.mapbox.com/mapbox.js/v1.0.2/mapbox.js'></script>
    <link href='http://api.tiles.mapbox.com/mapbox.js/v1.0.2/mapbox.css' rel='stylesheet' />
    <!--[if lte IE 8]>
    <link href='http://api.tiles.mapbox.com/mapbox.js/v1.0.2/mapbox.ie.css' rel='stylesheet' >
    <![endif]-->
    <style>
        body { margin:0; padding:0; }
        #map { position:absolute; top:0; bottom:0; width:100%; }
    </style>
</head>
<body>
<div id='map'></div>
<script>
    var map = L.mapbox.map('map', 'cleancola.map-rmhk6v1q');
        //.setView([37.9, -77], 6);

    <?php foreach ($incidents as $incident) : ?>
    L.mapbox.markerLayer({
        // this feature is in the GeoJSON format: see geojson.org
        // for the full specification
        type: 'Feature',
        geometry: {
            type: 'Point',
            // coordinates here are in longitude, latitude order because
            // x, y is the standard for GeoJSON and many formats
            coordinates: [<?php echo (int)$incident->longitude; ?>, <?php echo (int)$incident->latitude; ?>]
        },
        properties: {
            title: "<?php echo $incident->title; ?>",
            description: "<?php echo $incident->description; ?>",
            // one can customize markers by adding simplestyle properties
            // http://mapbox.com/developers/simplestyle/
            'marker-size': 'large',
            'marker-color': '#f0a'
        }
    }).addTo(map);
    <?php endforeach; ?>
</script>
</body>
</html>