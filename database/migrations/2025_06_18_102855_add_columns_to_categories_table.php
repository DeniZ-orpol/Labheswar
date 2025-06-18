<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $databases = [
        'labheswar',
        'labheswar_branch_1',
        'labheswar_branch_2',
        // Add more databases here if needed
    ];

    public function up(): void
    {
        foreach ($this->databases as $dbName) {
            Schema::connection($dbName)->table('categories', function (Blueprint $table) use ($dbName) {
                if (!Schema::connection($dbName)->hasColumn('categories', 'image')) {
                    $table->string('image')->nullable()->after('name');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->databases as $dbName) {
            Schema::connection($dbName)->table('categories', function (Blueprint $table) use ($dbName) {
                if (Schema::connection($dbName)->hasColumn('categories', 'image')) {
                    $table->dropColumn('image');
                }
            });
        }
    }
};
