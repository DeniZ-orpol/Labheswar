<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListBranches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:list-branches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all branches with their details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branches = DB::connection('master')->table('branches')->get();

        if ($branches->isEmpty()) {
            $this->info('No branches found.');
            return;
        }

        $this->info('ERP Branches:');
        $this->info('============');

        foreach ($branches as $branch) {
            $userCount = DB::connection('master')->table('users')
                ->where('branch_id', $branch->id)
                ->count();

            $this->info("ID: {$branch->id}");
            $this->info("Name: {$branch->name}");
            $this->info("Code: {$branch->code}");
            $this->info("Database: {$branch->database_name}");
            $this->info("Connection: {$branch->connection_name}");
            $this->info("Status: {$branch->status}");
            $this->info("Users: {$userCount}");
            $this->info("Created: {$branch->created_at}");
            $this->info('---');
        }
    }
}
