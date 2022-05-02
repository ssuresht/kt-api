<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Students;
class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Students::factory(20)->create();

    }
}
