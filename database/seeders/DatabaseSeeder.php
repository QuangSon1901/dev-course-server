<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'username' => 'jim',
                'role_id' => 2,
                'name' => 'Jim',
                'sex' => 'Nam',
                'birth' => '2001/01/01',
                'email' => 'jim@gmail.com',
                'password' => '123',
            ],
        ]);
    }
}
