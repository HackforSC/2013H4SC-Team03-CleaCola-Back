<?php CC\Helper\Partial::render('_header.php'); ?>

<div class="container-fluid">
<div class="row-fluid">
<div class="span3">
    <div class="well sidebar-nav" data-spy="affix">
        <ul class="nav nav-list">
            <li><a href="#get-incidents">Get Incidents</a></li>
            <li><a href="#get-incident">Get Incident</a></li>
            <li><a href="#get-incident-images">Get Incident Images</a></li>
            <li><a href="#create-an-incident">Create an Incident</a></li>
            <li><a href="#add-image-to-incident">Add Image to Incident</a></li>
            <li><a href="#vote-for-incident">Vote for Incident</a></li>
            <li><a href="#get-incident-categories">Get Incident Categories</a></li>
            <li><a href="#flag-an-incident">Flag an Incident</a></li>
            <li><a href="#close-an-incident">Close an Incident</a></li>
            <li><a href="#open-an-incident">Open an Incident</a></li>
            <li><a href="#attend-an-incident">Attend an Incident</a></li>
        </ul>
    </div>
    <!--/.well -->
</div>
<div class="span9">
<h1>api.cleancola.org</h1>

<div class="well" id="get-incidents">
    <h3>Get Incidents</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Returns all incidents based on the passed filters.</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>GET</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td><code>latlng = string,string (default: columbia, sc @ state house)<br>range = int (default: 20
                    miles)<br>category_id = int (default: all)</code></td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 404, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td><code>incidents[]</code></td>
        </tr>
    </table>
</div>

<div class="well" id="get-incident">
    <h3>Get Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Get a specific incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>GET</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td><code>id : int,<br> description: string,<br> latlng : string,<br> date_created: string,<br> votes =
                    int,<br> is_closed: datetime,<br> is_flagged: datetime</code></td>
        </tr>
    </table>
</div>

<div class="well" id="get-incident-images">
    <h3>Get Incident Images</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Get images for a specific image</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>GET</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id/images</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td><code>[{image_src}, {image_src}]</code></td>
        </tr>
    </table>
</div>

<div class="well" id="create-an-incident">
    <h3>Create an Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Creates an incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>POST</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td><code>latitude : string (required),<br>longitude : string (required),<br> category_id : int
                    (required),<br> description : string (required),<br> image = multipart/form-data</code></td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 404, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td>None</td>
        </tr>
    </table>
</div>

<div class="well" id="add-image-to-incident">
    <h3>Add Image to Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Adds an image to a pre-existing incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>POST</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id/images</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td><code>image = multipart/form-data (required)</code></td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 404, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td>None</td>
        </tr>
    </table>
</div>

<div class="well" id="vote-for-incident">
    <h3>Vote for Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Upvote an incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>POST</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id/vote</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td>None</td>
        </tr>
    </table>
</div>

<div class="well" id="get-incident-categories">
    <h3>Get Incident Categories</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Get a listing of all available incident categories</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>GET</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/categories</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td><code>[{id: int, title: string, date_created: string}]</code></td>
        </tr>
    </table>
</div>

<div class="well" id="flag-an-incident">
    <h3>Flag an Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Flag an Incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>POST</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id/flag</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td>None</td>
        </tr>
    </table>
</div>

<div class="well" id="close-an-incident">
    <h3>Close an Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Close an Incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>POST</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id/close</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td>None</td>
        </tr>
    </table>
</div>

<div class="well" id="open-an-incident">
    <h3>Open an Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Open an Incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>POST</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id/open</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td>None</td>
        </tr>
    </table>
</div>

<div class="well" id="attend-an-incident">
    <h3>Attend an Incident</h3>
    <table>
        <tr>
            <td><strong>Description</strong></td>
            <td>Mark that someone is going to attend this incident</td>
        </tr>
        <tr>
            <td><strong>Method</strong></td>
            <td>POST</td>
        </tr>
        <tr>
            <td><strong>End Point</strong></td>
            <td>http://api.cleancola.org/incidents/:id/attend</td>
        </tr>
        <tr>
            <td><strong>Request Parameters</strong></td>
            <td>None</td>
        </tr>
        <tr>
            <td><strong>Return Codes</strong></td>
            <td>200, 500</td>
        </tr>
        <tr>
            <td><strong>Return Data (JSON)</strong></td>
            <td>None</td>
        </tr>
    </table>
</div>

</div>
</div>
</div>

<?php CC\Helper\Partial::render('_footer.php'); ?>
