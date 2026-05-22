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
        Schema::create('recruitment_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions', 'id')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight');
            $table->text('description')->nullable();
            $table->enum('type', ['benefit', 'cost']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_criterias');
    }
};
