<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       DB::table('admins')->insert(
        [
            'name' => 'moto',
            'status' => 1,
            'email' => 'business@motocle.com',
            'password' => Hash::make('amwTdv,PjU%$2V4?')
        ]
        );
    }

}
