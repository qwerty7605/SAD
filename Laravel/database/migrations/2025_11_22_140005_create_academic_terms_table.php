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
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id('term_id');
            $table->string('academic_year', 20);
            $table->enum('semester', ['first', 'second', 'summer']);
            $table->string('term_name', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('enrollment_start');
            $table->date('enrollment_end');
            $table->boolean('is_current')->default(false);
            $table->date('clearance_deadline')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
