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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id('org_id');
            $table->string('org_code', 20)->unique();
            $table->string('org_name', 100);
            $table->enum('org_type', ['academic', 'administrative', 'finance', 'student_services']);
            $table->string('department', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_clearance')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
