<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Http\Actions\GetInternalId;
class StudentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'family_name' => $this->faker->name, 
            'first_name' => $this->faker->name, 
            'family_name_furigana' => $this->faker->name, 
            'first_name_furigana' => $this->faker->name, 
            'email_valid' => $this->faker->email, 
            'email_invalid' => $this->faker->email, 
            'is_email_approved'=>1,
            'education_facility_id' =>5,
            'university_name' => $this->faker->sentence(3), 
            'graduate_year'   =>2022,
            'password'    =>Hash::make('12345678'),
            'graduate_month'  =>02,
            'self_introduction' =>$this->faker->sentence(20),
            'status'      => 1,
           'student_internal_id' => GetInternalId::get_internal_student_id(rand(10,1000),'2022-03-30')
        ];
    }
}
