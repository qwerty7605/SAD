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
        Schema::create('clearance_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->foreignId('clearance_id')->constrained('student_clearances', 'clearance_id')->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations', 'org_id')->onDelete('cascade');
            $table->enum('status', ['approved', 'pending', 'needs_compliance'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_date')->nullable();
            $table->boolean('is_auto_approved')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('status_updated')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('approved_by')->references('admin_id')->on('organization_admins')->onDelete('set null');

            $table->unique(['clearance_id', 'org_id']);
            $table->index(['clearance_id', 'org_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_items');
    }
};
