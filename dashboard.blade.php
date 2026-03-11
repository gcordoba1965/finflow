<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Monthly budget snapshots
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7);          // 'YYYY-MM'
            $table->decimal('total_income', 12, 2);
            $table->decimal('needs_limit', 12, 2);   // 50%
            $table->decimal('wants_limit', 12, 2);   // 30%
            $table->decimal('savings_goal', 12, 2);  // 20%
            $table->timestamps();

            $table->unique(['user_id', 'period']);
            $table->index(['user_id', 'period']);
        });

        // Savings goals
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon', 10)->default('🎯');
            $table->decimal('target_amount', 12, 2);
            $table->decimal('saved_amount', 12, 2)->default(0);
            $table->date('deadline')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
        Schema::dropIfExists('budgets');
    }
};
