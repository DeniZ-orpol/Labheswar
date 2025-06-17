<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-database';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup databases for branches';

    protected $branches = [
        1 => ['name' => 'Labheswar Branch 1', 'code' => 'LB1', 'db' => 'labheswar_branch_1', 'connection' => 'labheswar_branch_1'],
        2 => ['name' => 'Labheswar Branch 2', 'code' => 'LB2', 'db' => 'labheswar_branch_2', 'connection' => 'labheswar_branch_2'],
    ];

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $this->info('Setting up Labheswar databases for 2 branches...');

        // 1. Setup Master Database
        $this->setupMasterDatabase();

        // 2. Setup Branch Databases
        foreach ($this->branches as $id => $branch) {
            $this->setupBranchDatabase($id, $branch);
        }

        $this->info('✅ 2-Branch database setup completed!');
        $this->showLoginCredentials();
    }

    // Master database setup
    protected function setupMasterDatabase()
    {
        $this->info('Setting up Master Database...');

        try {
            // Create master database
            $masterDb = env('DB_DATABASE');
            DB::connection('master')->statement("CREATE DATABASE IF NOT EXISTS `{$masterDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Run master migrations
            Artisan::call('migrate', ['--database' => 'master']);

            // Create superadmin first
            // $this->createSuperAdmin();

            // Insert branch records
            // $this->seedMasterData();

            $this->info('✅ Master database setup completed');

        } catch (\Exception $e) {
            $this->error('❌ Master database setup failed: ' . $e->getMessage());
            return false;
        }
    }

    // setup branch database
    protected function setupBranchDatabase($id, $branch)
    {
        $this->info("Setting up {$branch['name']} Branch Database...");

        try {
            // Create branch database
            DB::connection('master')->statement("CREATE DATABASE IF NOT EXISTS `{$branch['db']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Run branch migrations
            Artisan::call('migrate', [
                '--database' => $branch['connection'],
                '--path' => 'database/migrations/branch'
            ]);

            // Seed branch users data
            // $this->seedBranchUsers($branch['connection'], $branch['code'], $id);

            $this->info("✅ {$branch['name']} branch database setup completed");

        } catch (\Exception $e) {
            $this->error("❌ {$branch['name']} branch database setup failed: " . $e->getMessage());
        }
    }

    // Seed master data for branches and super admin user
    protected function seedMasterData()
    {
        // create super admin user
        DB::connection('master')->table('users')->updateOrInsert(
            ['email' => 'superadmin@labheswar.com'],
            [
                'id' => 1,
                'name' => 'Super Administrator',
                'email' => 'superadmin@labheswar.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'dob' => now()->subYears(35),
                'mobile' => '9876543210',
                'role' => 'Superadmin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $this->info('✅ Super Admin created: superadmin@labheswar.com');

        // Insert branch records
        foreach ($this->branches as $id => $branch) {
            DB::connection('master')->table('branches')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $branch['name'] . ' Branch',
                    'code' => $branch['code'],
                    'location' => 'Location of ' . $branch['name'],
                    'latitude' => $branch['code'] == 'LB1' ? '19.0760' : '28.7041',
                    'longitude' => $branch['code'] == 'LB1' ? '72.8777' : '77.1025',
                    'gst_no' => '27AABCL1234C1Z' . $id,
                    'database_name' => $branch['db'],
                    'connection_name' => $branch['connection'],
                    'status' => 'active',
                    'branch_admin' => 1, // Super admin manages all initially
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->info('✅ Master data seeded for 2 branches');
    }

    protected function showLoginCredentials()
    {
        $this->info('');
        $this->info('🔐 LOGIN CREDENTIALS:');
        $this->info('====================');
        
        $this->info('👨‍💼 SUPER ADMIN (Master Database):');
        $this->info('   Email: superadmin@labheswar.com');
        $this->info('   Password: password');
        $this->info('   Database: master');
        $this->info('');
        
        $this->info('🎯 NEXT STEPS:');
        $this->info('- Superadmin can create/manage branch users from master dashboard');
        $this->info('- Branch users login to their respective branch systems');
        $this->info('- Run: php artisan app:list-branches');
        $this->info('- Run: php artisan app:branch-users 1');
        $this->info('- Run: php artisan app:branch-users 2');
    }

}
