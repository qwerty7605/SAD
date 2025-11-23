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
        Schema::create('students', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->primary();
            $table->string('student_number', 20)->unique();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('course', 100)->nullable();
            $table->integer('year_level')->nullable();
            $table->string('section', 20)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->date('date_enrolled')->nullable();
            $table->enum('enrollment_status', ['enrolled', 'inactive', 'graduated', 'withdrawn'])->default('enrolled');

            $table->foreign('student_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
