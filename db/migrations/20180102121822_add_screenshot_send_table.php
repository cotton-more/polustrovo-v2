<?php

use Phinx\Migration\AbstractMigration;

class AddScreenshotSendTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('screenshot_send', [
            'id' => false,
            'primary_key' => ['screenshot_send_id'],
        ]);

        $table->addColumn('screenshot_send_id', 'uuid');
        $table->addColumn('screenshot_id', 'uuid');
        $table->addColumn('publisher', 'string');
        $table->addColumn('sent_at', 'datetime');
        $table->addColumn('error_message', 'text');

        $table->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->create();
    }
}
