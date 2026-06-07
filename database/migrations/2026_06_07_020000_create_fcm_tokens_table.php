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
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->enum('owner_type', ['hr', 'candidate']);
            $table->unsignedBigInteger('owner_id'); // user_id untuk HR, application_id untuk candidate
            $table->string('fcm_token', 512);
            $table->timestamps();

            // Mencegah duplikat token untuk owner yang sama
            $table->unique(['owner_type', 'owner_id', 'fcm_token'], 'fcm_tokens_unique');
            $table->index(['owner_type', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
