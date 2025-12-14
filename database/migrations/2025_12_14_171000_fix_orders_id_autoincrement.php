<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL");
    }
};
