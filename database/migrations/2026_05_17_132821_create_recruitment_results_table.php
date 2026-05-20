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
        Schema::create('recruitment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications', 'id')->cascadeOnDelete();
            $table->decimal('final_score');
            $table->decimal('ranking');
            $table->enum('decission', ['rejected', 'hired']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_results');
    }
};
