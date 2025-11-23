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
        Schema::create('system_admins', function (Blueprint $table) {
            $table->unsignedBigInteger('sys_admin_id')->primary();
            $table->enum('admin_level', ['super_admin', 'mis_staff']);
            $table->string('full_name', 150);
            $table->string('department', 100)->nullable();
            $table->timestamp('assigned_date')->useCurrent();

            $table->foreign('sys_admin_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_admins');
    }
};
