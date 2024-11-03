<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgeGroup;

class AgeGroupSeeder extends Seeder
{
    public function run()
    {
        $ageGroups = [
            '85+',
            '80-84',
            '75-79',
            '70-74',
            '65-69',
            '60-64',
            '55-59',
            '50-54',
            '45-49',
            '40-44',
            '35-39',
            '30-34',
            '25-29',
            '19-24'
        ];

        foreach ($ageGroups as $group) {
            AgeGroup::firstOrCreate(['group_name' => $group]);
        }
    }
}
