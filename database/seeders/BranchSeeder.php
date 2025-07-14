<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Sweetler Branch 1',
            'code' => 'SB1',
            'location' => 'Sweetler Main Office',
            'latitude' => '12.9715987',
            'longitude' => '77.594566',
            'gst_no' => '24ABCDE1234F1Z5',
            'database_name' => 'sweetler_branch_1',
            'connection_name' => 'sweetler_branch_1',
            'db_username' => '',
            'db_password' => '',
            'status' => 'active',
            'branch_admin' => 1
        ]);
    }
}
