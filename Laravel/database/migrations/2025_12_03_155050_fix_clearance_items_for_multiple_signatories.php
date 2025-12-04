<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clearance_items', function (Blueprint $table) {
            // Drop the old unique constraint (clearance_id, org_id)
            $table->dropUnique(['clearance_id', 'org_id']);

            // Add new column for required signatory (which specific admin must approve)
            $table->unsignedBigInteger('required_signatory_id')->nullable()->after('org_id');
        });

        // Populate required_signatory_id for existing records with the first admin of each org
        DB::statement('
            UPDATE clearance_items ci
            INNER JOIN (
                SELECT org_id, MIN(admin_id) as first_admin_id
                FROM organization_admins
                WHERE is_active = 1
                GROUP BY org_id
            ) oa ON ci.org_id = oa.org_id
            SET ci.required_signatory_id = oa.first_admin_id
            WHERE ci.required_signatory_id IS NULL
        ');

        Schema::table('clearance_items', function (Blueprint $table) {
            // Make required_signatory_id non-nullable after populating data
            $table->unsignedBigInteger('required_signatory_id')->nullable(false)->change();

            // Add new unique constraint (clearance_id, required_signatory_id)
            // This allows multiple items per clearance (one per signatory)
            $table->unique(['clearance_id', 'required_signatory_id'], 'clearance_signatory_unique');

            // Add foreign key to organization_admins
            $table->foreign('required_signatory_id')
                  ->references('admin_id')
                  ->on('organization_admins')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_items', function (Blueprint $table) {
            // Drop foreign key and new unique constraint
            $table->dropForeign(['required_signatory_id']);
            $table->dropUnique('clearance_signatory_unique');

            // Drop the new column
            $table->dropColumn('required_signatory_id');

            // Restore the old unique constraint
            $table->unique(['clearance_id', 'org_id']);
        });
    }
};
