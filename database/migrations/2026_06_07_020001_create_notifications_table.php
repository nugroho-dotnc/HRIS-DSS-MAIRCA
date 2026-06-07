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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['new_application', 'dss_completed']);
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // payload tambahan (application_code, vacancy_id, dll)
            $table->enum('recipient_type', ['hr', 'candidate']); // identifikasi target role
            $table->unsignedBigInteger('recipient_id'); // user_id untuk HR, application_id untuk candidate
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index('type');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
