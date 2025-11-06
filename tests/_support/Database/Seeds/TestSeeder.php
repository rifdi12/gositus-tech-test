<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run()
    {
        // Insert test users
        $this->db->table('users')->insert([
            'email'      => 'admin@test.com',
            'password'   => password_hash('Admin123', PASSWORD_BCRYPT),
            'role'       => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('users')->insert([
            'email'      => 'user@test.com',
            'password'   => password_hash('User123', PASSWORD_BCRYPT),
            'role'       => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Insert test books
        $this->db->table('books')->insertBatch([
            [
                'title'       => 'Test Book 1',
                'description' => 'Description for test book 1',
                'image'       => 'test1.jpg',
                'uploaded_by' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'title'       => 'Test Book 2',
                'description' => 'Description for test book 2',
                'image'       => 'test2.jpg',
                'uploaded_by' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ]);

        // Insert test favorites
        $this->db->table('favorites')->insert([
            'user_id'    => 2,
            'book_id'    => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
