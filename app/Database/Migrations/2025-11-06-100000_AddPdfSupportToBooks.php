<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPdfSupportToBooks extends Migration
{
    public function up()
    {
        $fields = [
            'pdf_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'image',
            ],
            'has_vector' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'after'      => 'pdf_file',
            ],
            'collection_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'has_vector',
            ],
            'total_pages' => [
                'type'       => 'INT',
                'null'       => true,
                'after'      => 'collection_name',
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'total_pages',
            ],
        ];

        $this->forge->addColumn('books', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('books', ['pdf_file', 'has_vector', 'collection_name', 'total_pages', 'processed_at']);
    }
}
