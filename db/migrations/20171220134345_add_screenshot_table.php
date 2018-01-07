<?php


use Phinx\Migration\AbstractMigration;

class AddScreenshotTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('screenshot', [
            'id' => false,
            'primary_key' => ['screenshot_id'],
        ]);

        $table->addColumn('screenshot_id', 'uuid');
        $table->addColumn('browshot_id', 'string', [
            'null' => false,
        ]);
        $table->addColumn('browshot_instance', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('url', 'text', [
            'null' => false,
        ]);
        $table->addColumn('status', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('error_message', 'text');
        $table->addColumn('filename', 'string');
        $table->addColumn('file_size', 'integer', [
            'null' => false,
            'default' => 0,
        ]);

        $table->addColumn('created_at', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
        ]);

        $table->create();
    }
}
