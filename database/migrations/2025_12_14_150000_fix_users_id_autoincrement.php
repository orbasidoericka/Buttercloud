<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make `users.id` an auto-increment primary key if it's not already
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous non-auto increment column (keeps data)
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL");
    }
};
