<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SeederController extends Controller
{
    public function migrateFreshAndSeed(Request $request) {
        try {
            set_time_limit(300);
            Artisan::call('migrate:fresh', ['--seed' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Database migrated and seeded successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during migration.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
