<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stream;
use App\Models\Branch;
use App\Models\StreamType;

class StreamSeeder extends Seeder
{
    public function run()
    {
        $branchA = Branch::where('name', 'Branch A')->first();
        $branchB = Branch::where('name', 'Branch B')->first();
        $branchC = Branch::where('name', 'Branch C')->first();

        $entryType = StreamType::where('name', 'Entry')->first();
        $exitType = StreamType::where('name', 'Exit')->first();

        $streams = [
            ['name' => 'Mosque Entry 1', 'branch_id' => $branchA->id, 'stream_type_id' => $entryType->id],
            ['name' => 'Mosque Entry 2', 'branch_id' => $branchA->id, 'stream_type_id' => $entryType->id],
            ['name' => 'Mosque Entry 3', 'branch_id' => $branchB->id, 'stream_type_id' => $entryType->id],
            ['name' => 'Souq Entry 1', 'branch_id' => $branchC->id, 'stream_type_id' => $entryType->id],
        ];

        foreach ($streams as $stream) {
            Stream::firstOrCreate([
                'name' => $stream['name'], 
                'branch_id' => $stream['branch_id'], 
                'stream_type_id' => $stream['stream_type_id']
            ]);
        }
    }
}
