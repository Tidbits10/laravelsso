<?php

namespace Database\Seeders;

use App\Models\MockSis;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pup.edu.ph'],
            [
                'name' => 'SSO Administrator',
                'username' => 'admin',
                'password' => 'admin12345',
                'role' => 'admin',
            ]
        );

        MockSis::updateOrCreate(
            ['student_number' => '2026-00001-SP-0'],
            ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@iskolarngbayan.pup.edu.ph']
        );

        MockSis::updateOrCreate(
            ['student_number' => '2026-00002-SP-0'],
            ['name' => 'Maria Santos', 'email' => 'maria.santos@iskolarngbayan.pup.edu.ph']
        );
    }
}
