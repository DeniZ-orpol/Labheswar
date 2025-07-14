<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('configureBranchConnection')) {
    /**
     * Configure dynamic database connection for branch
     *
     * @param object $branch
     * @return void
     */
    function configureBranchConnection($branch)
    {
        $connectionName = $branch->connection_name;

        $branchConfig = [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $branch->database_name,
            'username' => $branch->db_username ?? env('DB_USERNAME', 'root'),
            'password' => $branch->db_password ?? env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        config(['database.connections.' . $connectionName => $branchConfig]);
        DB::purge($connectionName);

        // ✅ Return the connection name
        return $connectionName;
    }
}

if (!function_exists('getBranchConnection')) {
    /**
     * Get configured branch database connection
     *
     * @param object $branch
     * @return \Illuminate\Database\Connection
     */
    function getBranchConnection($branch)
    {
        configureBranchConnection($branch);
        return DB::connection($branch->connection_name);
    }
}

if (!function_exists('testBranchConnection')) {
    /**
     * Test if branch database connection is working
     *
     * @param object $branch
     * @return bool
     */
    function testBranchConnection($branch)
    {
        try {
            configureBranchConnection($branch);
            DB::connection($branch->connection_name)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    function weightInKilogramsToPrice($kg, $product)
    {
        $kg = (float) $kg;

        // Use price_1 for 1–5 KG fixed mapping
        if (in_array($kg, [1, 2, 3, 4, 5])) {
            $field = "price_" . intval($kg);
            $fixedPrice = $product->$field;
            if (!is_null($fixedPrice)) {
                return $fixedPrice;
            }
        }

        // Default: use price_1 to calculate
        $perKgPrice = (float) $product->price_1;
        return $kg * $perKgPrice;
    }

    function weightInGramsToPrice($grams, $product)
    {
        $perGramPrice = ((float) $product->price_1) / 1000;
        return $grams * $perGramPrice;
    }

    function weightInPriceToKilograms($price, $product)
    {
        $perKgPrice = (float) $product->price_1;
        if ($perKgPrice == 0) return 0;
        return $price / $perKgPrice;
    }

    function weightInPriceToGrams($price, $product)
    {
        $perGramPrice = ((float) $product->price_1) / 1000;
        if ($perGramPrice == 0) return 0;
        return $price / $perGramPrice;
    }

    function formatNumber($value)
    {
        return round((float) $value, 2);
    }
}
