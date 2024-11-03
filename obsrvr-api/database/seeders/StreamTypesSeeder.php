<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StreamType;

class StreamTypesSeeder extends Seeder
{
    public function run()
    {
        $streamTypes = [
            ['name' => 'Entry'],
            ['name' => 'Exit'],
            ['name' => 'Area of Interest']
        ];

        foreach ($streamTypes as $type) {
            StreamType::firstOrCreate(['name' => $type['name']]);
        }
    }
}
