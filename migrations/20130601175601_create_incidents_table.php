<?php

use Phinx\Migration\AbstractMigration;

class CreateIncidentsTable extends AbstractMigration
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
        $table = $this->table('Incidents');
        $table->addColumn('latitude', 'string');
        $table->addColumn('longitude', 'string');
        $table->addColumn('description', 'string');
        $table->addColumn('date_created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'));
        $table->addColumn('is_flagged', 'boolean');
        $table->addColumn('is_closed', 'boolean');
        $table->addColumn('category_id', 'integer');
        $table->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}