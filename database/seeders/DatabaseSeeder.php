<?php

namespace Database\Seeders;

use App\Models\BusinessIndustries;
use App\Models\EducationFacilities;
use App\Models\MediaTags;
use App\Models\WorkCategories;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
       WorkCategories::factory(10)->create();
        BusinessIndustries::factory(10)->create();
        MediaTags::factory(10)->create();
        EducationFacilities::factory(10)->create();
       $this->call([
           AdminSeeder::class
        ]);
    }
}
