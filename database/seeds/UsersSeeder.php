<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('persons')->insert([
            'id' => 1,
            'company' => 0,
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
        ]);

        DB::table('employees')->insert([
            'person_id' => 1
        ]);

        DB::table('permissions')->insert([
            'id' => 1,
            'permission' => 'Administrador',
            'list' => '{}',
        ]);

        DB::table('users')->insert([
            'name'      => 'Admin',
            'last_name' => 'Istrador',
            'email'     => 'admin@admin.com',
            'password'  => bcrypt('123456'),
            'permission_id' => 1,
            'person_id' => 1,
        ]); 
    }
}
