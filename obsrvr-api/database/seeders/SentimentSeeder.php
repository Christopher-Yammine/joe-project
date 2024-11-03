<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sentiment;

class SentimentSeeder extends Seeder
{
    public function run()
    {
        $sentiments = ['Happy', 'Neutral', 'Sad'];

        foreach ($sentiments as $sentiment) {
            Sentiment::firstOrCreate(['sentiment' => $sentiment]);
        }
    }
}
