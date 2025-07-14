<?php

namespace Database\Seeders;

use App\Models\Role;
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
        Role::create([
            'role_name' => 'Super Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'name' => 'Sweetler Super Admin',
            'email' => 'sweetlersuperadmin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin@123'),
            'dob' => now()->subYears(29),
            'mobile' => '1234567890',
            'role' => 'Super admin',
            'role_id' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
