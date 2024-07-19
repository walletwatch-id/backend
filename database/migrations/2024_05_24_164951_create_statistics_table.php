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
        Schema::create('statistics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('year');
            $table->string('personality')->nullable();
            $table->unsignedBigInteger('total_transaction')->default(0);
            $table->unsignedBigInteger('total_installment')->default(0);
            $table->unsignedBigInteger('total_income')->default(0);
            $table->decimal('ratio', 5, 4)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
