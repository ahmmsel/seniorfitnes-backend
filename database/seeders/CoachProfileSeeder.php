<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CoachProfile;

class CoachProfileSeeder extends Seeder
{
    public function run()
    {
        // Create 8 coach users and profiles
        $names = [
            'Ahmad Ali',
            'Sara Mohamed',
            'Omar Hassan',
            'Laila Ibrahim',
            'Youssef Karim',
            'Mona Salem',
            'Khaled Nasser',
            'Noura Adel',
        ];

        $allowedSpecialties = ['nutrition', 'workout', 'both'];

        foreach ($names as $index => $name) {
            $i = $index + 1;
            $email = 'coach' . $i . '@example.com';

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make((string)$i),
                'email_verified_at' => now(),
            ]);

            CoachProfile::create([
                'user_id' => $user->id,
                'description' => 'Sample coach profile for ' . $name,
                'specialty' => $allowedSpecialties[$index % count($allowedSpecialties)],
                'years_of_experience' => rand(1, 12),
                'nutrition_price' => rand(10, 50),
                'workout_price' => rand(15, 80),
                'full_package_price' => rand(40, 150),
            ]);
        }
    }
}
