<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Company;
use App\Models\City;

class BranchesSeeder extends Seeder
{
    public function run()
    {
        $company1 = Company::where('name', 'Company 1')->first();
        $company2 = Company::where('name', 'Company 2')->first();

        $city1 = City::where('name', 'City 1')->first();
        $city2 = City::where('name', 'City 2')->first();
        $city3 = City::where('name', 'City 3')->first();

        $branches = [
            ['name' => 'Branch A', 'company_id' => $company1->id, 'city_id' => $city1->id],
            ['name' => 'Branch B', 'company_id' => $company1->id, 'city_id' => $city2->id],
            ['name' => 'Branch C', 'company_id' => $company2->id, 'city_id' => $city3->id],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['name' => $branch['name'], 'company_id' => $branch['company_id'], 'city_id' => $branch['city_id']]);
        }
    }
}
