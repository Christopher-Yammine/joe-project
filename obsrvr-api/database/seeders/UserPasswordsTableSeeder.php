<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPassword;
use App\Models\User;

class UserPasswordsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@szgmc.gov.ae')->first();

        if ($user) {
            UserPassword::create([
                'user_id' => $user->id,
                'hashed_password' => bcrypt('admins'),
            ]);
        }
    }
}
