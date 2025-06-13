<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BranchStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:branch-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get detailed status of a specific branch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branchId = $this->argument('branch_id');

        $branch = DB::connection('master')->table('branches')->where('id', $branchId)->first();
        if (!$branch) {
            $this->error("Branch with ID {$branchId} not found.");
            return;
        }

        $this->info("Branch Status Report");
        $this->info("===================");
        $this->info("Branch: {$branch->name} ({$branch->code})");
        $this->info("Status: {$branch->status}");
        $this->info("Database: {$branch->database_name}");

        try {
            // Test database connection
            DB::connection($branch->connection_name)->getPdo();
            $this->info("✅ Database connection: OK");

            // Get branch statistics
            // $stats = $this->getBranchStatistics($branch->connection_name);

            // $this->info("\nBranch Statistics:");
            // $this->info("- Customers: {$stats['customers']}");
            // $this->info("- Products: {$stats['products']}");
            // $this->info("- Sales Orders: {$stats['sales_orders']}");
            // $this->info("- Workflow Templates: {$stats['workflow_templates']}");
            // $this->info("- Active Workflows: {$stats['active_workflows']}");

        } catch (\Exception $e) {
            $this->error("❌ Database connection failed: " . $e->getMessage());
        }

        // Get users for this branch
        $users = DB::connection('master')->table('users')
            ->where('branch_id', $branchId)
            ->get(['username', 'email', 'role', 'is_active', 'last_login_at']);

        $this->info("\nUsers:");
        foreach ($users as $user) {
            $status = $user->is_active ? 'Active' : 'Inactive';
            $lastLogin = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never';
            $this->info("- {$user->username} ({$user->email}) - {$user->role} - {$status} - Last login: {$lastLogin}");
        }
    }

    protected function getBranchStatistics($connection)
    {
        try {
            return [
                'customers' => DB::connection($connection)->table('customers')->count(),
                'products' => DB::connection($connection)->table('products')->count(),
                'sales_orders' => DB::connection($connection)->table('sales_orders')->count(),
                'workflow_templates' => DB::connection($connection)->table('workflow_templates')->count(),
                'active_workflows' => DB::connection($connection)->table('workflow_instances')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count(),
            ];
        } catch (\Exception $e) {
            return [
                'customers' => 'Error',
                'products' => 'Error',
                'sales_orders' => 'Error',
                'workflow_templates' => 'Error',
                'active_workflows' => 'Error',
            ];
        }
    }
}
