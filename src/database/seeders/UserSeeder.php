<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@corp.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'ADMIN',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@corp.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'role' => 'USER',
            ]
        );
    }
}
