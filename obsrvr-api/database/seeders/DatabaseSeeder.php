<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {

        $this->call([
            PersonTypeSeeder::class,
            GenderSeeder::class,
            SentimentSeeder::class,
            AgeGroupSeeder::class,


            MetricsSeeder::class,
            CountriesSeeder::class,
            CitiesSeeder::class,
            CompaniesSeeder::class,
            BranchesSeeder::class,
            StreamTypesSeeder::class,
            StreamSeeder::class,
            DemographicsSeeder::class,

            ETLDataSeeder::class,

            UsersTableSeeder::class,
            UserPasswordsTableSeeder::class
        ]);
    }
}
