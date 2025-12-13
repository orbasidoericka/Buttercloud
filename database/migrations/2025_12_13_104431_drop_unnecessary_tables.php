<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('sessions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Recreating these tables would require their original migration definitions
        // This is a one-way migration - these tables won't be restored on rollback
    }
};
