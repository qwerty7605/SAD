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
        // Make the column nullable to support SET NULL (must do BEFORE adding FK)
        Schema::table('clearance_items', function (Blueprint $table) {
            $table->unsignedBigInteger('required_signatory_id')->nullable()->change();
        });

        // Add foreign key with SET NULL
        // This preserves approval history when an admin is deleted
        Schema::table('clearance_items', function (Blueprint $table) {
            $table->foreign('required_signatory_id')
                  ->references('admin_id')
                  ->on('organization_admins')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_items', function (Blueprint $table) {
            // Drop the SET NULL foreign key
            $table->dropForeign(['clearance_items_required_signatory_id_foreign']);

            // Make column non-nullable again
            $table->unsignedBigInteger('required_signatory_id')->nullable(false)->change();
        });
    }
};
