<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Demographic;
use App\Models\PersonType;
use App\Models\Gender;
use App\Models\Sentiment;
use App\Models\AgeGroup;
use App\Models\Stream;

class DemographicsSeeder extends Seeder
{
    public function run()
    {
       
       
        $genders = Gender::all();
        $sentiments = Sentiment::all();
        $ageGroups = AgeGroup::all();
      

   
    
            foreach ($genders as $gender) {
                foreach ($sentiments as $sentiment) {
                    foreach ($ageGroups as $ageGroup) {
                       
                           
                            Demographic::create([
                               
                               
                                'gender_id' => $gender->id,
                                'age_group_id' => $ageGroup->id,
                                'sentiment_id' => $sentiment->id,
                            ]);
                    
                    }
                }
            }
        
    }
}
