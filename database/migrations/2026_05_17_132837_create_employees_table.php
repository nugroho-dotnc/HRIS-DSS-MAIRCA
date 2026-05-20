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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments', 'id')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions', 'id')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->date('join_date');
            $table->enum('contract_status', ['permanent', 'contract', 'probation']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
