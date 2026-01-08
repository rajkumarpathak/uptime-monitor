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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->enum('status', ['up', 'down', 'checking'])->default('checking');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_down_at')->nullable();
            $table->integer('check_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
            
            // Add unique constraint for client-website combination
            $table->unique(['client_id', 'url']);
            
            // Add indexes for performance
            $table->index(['status', 'last_checked_at']);
            $table->index(['client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
