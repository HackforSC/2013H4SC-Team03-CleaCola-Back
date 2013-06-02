<?php namespace CC\Model;

class Incident
{
    public $id;
    public $latitude;
    public $longitude;
    public $description;
    public $date_created;
    public $is_flagged;
    public $is_closed;
    public $category_id;
}
