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
        Schema::create('student_clearances', function (Blueprint $table) {
            $table->id('clearance_id');
            $table->unsignedBigInteger('student_id');
            $table->foreignId('term_id')->constrained('academic_terms', 'term_id')->onDelete('cascade');
            $table->enum('overall_status', ['approved', 'pending', 'incomplete'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('approved_date')->nullable();
            $table->boolean('is_locked')->default(false);

            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');

            $table->unique(['student_id', 'term_id']);
            $table->index(['student_id', 'term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_clearances');
    }
};
