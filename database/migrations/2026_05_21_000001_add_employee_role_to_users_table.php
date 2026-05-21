<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah ENUM role agar mencakup 'employee' dan menyesuaikan nama role.
     * Role baru: admin, hr, employee (menggantikan supervisor & candidate yang tidak dipakai di routing)
     */
    public function up(): void
    {
        // MySQL tidak mendukung ALTER COLUMN untuk ENUM secara langsung via Blueprint,
        // sehingga kita gunakan raw statement.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'hr', 'supervisor', 'candidate', 'employee') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'hr', 'supervisor', 'candidate') NOT NULL");
    }
};
