<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['expense', 'income']);
            $table->enum('category', ['needs', 'wants', 'savings', 'income'])->index();
            $table->string('sub_category')->nullable();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('icon', 10)->default('💳');
            $table->date('date')->index();
            $table->text('notes')->nullable();
            $table->string('reference')->nullable(); // bank import ref
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
