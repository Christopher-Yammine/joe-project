<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountriesSeeder extends Seeder
{
    public function run()
    {
        $countries = [
            ['name' => 'Country A'],
            ['name' => 'Country B'],
            ['name' => 'Country C'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(['name' => $country['name']]);
        }
    }
}
