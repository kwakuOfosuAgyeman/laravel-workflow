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
        Schema::create('run_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained()->onDelete('cascade');
            $table->foreignId('step_id')->nullable()->constrained()->onDelete('set null');
            $table->string('level'); // info, warn, error
            $table->text('message');
            $table->timestamp('timestamp');
            $table->timestamps();

            // Indexes for querying logs
            $table->index(['run_id', 'created_at']);
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('run_logs');
    }
};
