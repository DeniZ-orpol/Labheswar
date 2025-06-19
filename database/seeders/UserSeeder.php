<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        User::create([
            'name' => 'Labheshwar Admin',
            'email' => 'orpol@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin@613'),
            'dob' => now()->subYears(32),
            'mobile' => '9876543210',
            'role' => 'Superadmin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
