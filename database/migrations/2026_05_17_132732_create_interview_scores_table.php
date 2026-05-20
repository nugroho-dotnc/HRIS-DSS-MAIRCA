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
        Schema::create('interview_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('interview_sessions', 'id')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('recruitment_criterias', 'id')->cascadeOnDelete();
            $table->decimal('score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_scores');
    }
};
