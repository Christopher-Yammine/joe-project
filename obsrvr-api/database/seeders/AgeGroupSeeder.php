<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgeGroup;

class AgeGroupSeeder extends Seeder
{
    public function run()
    {
        $ageGroups = [
            '70+',
            '50-69',
            '35-49',
            '25-34',
            '19-24'];

        foreach ($ageGroups as $group) {
            AgeGroup::firstOrCreate(['group_name' => $group]);
        }
    }
}
