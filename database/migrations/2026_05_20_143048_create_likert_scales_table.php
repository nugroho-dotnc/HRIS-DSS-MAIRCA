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
        Schema::create('likert_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_criterias_id')->constrained('recruitment_criterias', 'id')->cascadeOnDelete();
            $table->string('label');
            $table->decimal('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likert_scales');
    }
};
