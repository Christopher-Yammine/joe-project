<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Demographic;
use App\Models\Gender;
use App\Models\Sentiment;
use App\Models\AgeGroup;

class DemographicsSeeder extends Seeder
{
    public function run()
    {
        $genderIds = Gender::all()->pluck('id')->toArray();
        $sentimentIds = Sentiment::all()->pluck('id')->toArray();
        $ageGroupIds = AgeGroup::all()->pluck('id')->toArray();

        $counter = 0;
        $limit = 1000;

        while ($counter < $limit) {
            $randomGenderId = $genderIds[array_rand($genderIds)];
            $randomAgeGroupId = $ageGroupIds[array_rand($ageGroupIds)];
            $randomSentimentId = $sentimentIds[array_rand($sentimentIds)];

            Demographic::create([
                'gender_id' => $randomGenderId,
                'age_group_id' => $randomAgeGroupId,
                'sentiment_id' => $randomSentimentId,
            ]);

            $counter++;
        }
    }
}

