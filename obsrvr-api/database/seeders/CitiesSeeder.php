<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Country;

class CitiesSeeder extends Seeder
{
    public function run()
    {
        $countryA = Country::where('name', 'Country A')->first();
        $countryB = Country::where('name', 'Country B')->first();
        $countryC = Country::where('name', 'Country C')->first();

        $cities = [
            ['name' => 'City 1', 'country_id' => $countryA->id],
            ['name' => 'City 2', 'country_id' => $countryB->id],
            ['name' => 'City 3', 'country_id' => $countryC->id],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(['name' => $city['name'], 'country_id' => $city['country_id']]);
        }
    }
}
    