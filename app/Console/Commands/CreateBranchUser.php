<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateBranchUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-branch-user {branch_id} {username} {email} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user for specific branch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branchId = $this->argument('branch_id');
        $username = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->option('password') ?: $this->secret('Enter password');

        // Validate branch ID
        $branch = DB::connection('master')->table('branches')->where('id', $branchId)->first();
        if (!$branch) {
            $this->error("Branch with ID {$branchId} not found.");
            return;
        }

        // Check if user already exists
        $existingUser = DB::connection('master')->table('users')
            ->where('name', $username)
            ->orWhere('email', $email)
            ->first();

        if ($existingUser) {
            $this->error("User with username '{$username}' or email '{$email}' already exists.");
            return;
        }

        try {
            DB::connection('master')->table('users')->insert([
                'branch_id' => $branchId,
                'name' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'permissions' => json_encode([]),
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("✅ User created successfully!");
            $this->info("Branch: {$branch->name} ({$branch->code})");
            $this->info("Username: {$username}");
            $this->info("Email: {$email}");

        } catch (\Exception $e) {
            $this->error("❌ Failed to create user: " . $e->getMessage());
        }
    }
}
