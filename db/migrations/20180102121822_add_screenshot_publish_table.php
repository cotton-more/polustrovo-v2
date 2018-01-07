<?php

use Phinx\Migration\AbstractMigration;

class AddScreenshotPublishTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('screenshot_publish', [
            'id' => false,
            'primary_key' => ['screenshot_publish_id'],
        ]);

        $table->addColumn('screenshot_publish_id', 'uuid');
        $table->addColumn('screenshot_id', 'uuid');
        $table->addColumn('publisher', 'string');
        $table->addColumn('published_at', 'datetime');
        $table->addColumn('error_message', 'text');

        $table->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->create();
    }
}
