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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions', 'id')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('requirements');
            $table->date('deadline');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
