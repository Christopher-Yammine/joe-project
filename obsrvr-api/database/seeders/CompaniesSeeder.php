<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompaniesSeeder extends Seeder
{
    public function run()
    {
        $companies = [
            ['name' => 'Company 1'],
            ['name' => 'Company 2'],
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate(['name' => $company['name']]);
        }
    }
}
    