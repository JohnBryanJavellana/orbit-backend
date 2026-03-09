<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $preBuiltUsers = [
            [
                "first_name"    => "John Bryan",
                "middle_name"   => "Argota",
                "last_name"     => "Javellana",
                "gender"        => "MALE",
                "role"          => "SUPERADMIN",
                "email"         => "johnbryanjavellana@gmail.com",
                "password"      => \Hash::make('password123'),
                "email_verified_at" => now(),
            ]
        ];

        foreach ($preBuiltUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
