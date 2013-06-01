<?php

use Phinx\Migration\AbstractMigration;

class IncidentPhotos extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('IncidentPhotos');
        $table->addColumn('incident_id', 'integer');
        $table->addColumn('image_src', 'string');
        $table->addColumn('date_created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'));
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}