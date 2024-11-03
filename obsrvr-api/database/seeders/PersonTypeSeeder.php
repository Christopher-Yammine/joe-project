<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonType;

class PersonTypeSeeder extends Seeder
{
    public function run()
    {
        $personTypes = ['New', 'Returning', 'Staff'];

        foreach ($personTypes as $type) {
            PersonType::firstOrCreate(['name' => $type]);
        }
    }
}
