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
        Schema::create('organization_admins', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->primary();
            $table->foreignId('org_id')->constrained('organizations', 'org_id')->onDelete('cascade');
            $table->string('position', 100)->nullable();
            $table->string('full_name', 150);
            $table->timestamp('assigned_date')->useCurrent();
            $table->timestamp('removed_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('admin_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_admins');
    }
};
