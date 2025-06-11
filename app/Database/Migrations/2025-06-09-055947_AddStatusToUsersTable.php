<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive', 'pending'],
                'default' => 'pending',
                'after' => 'role'
            ],
            'activation_code' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'status'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'status');
        $this->forge->dropColumn('users', 'activation_code');
    }
}