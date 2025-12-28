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
        Schema::table('students', function (Blueprint $table) {
            $table->index(['hall_status', 'is_active'], 'idx_students_hall_status_active');
        });

        Schema::table('seats', function (Blueprint $table) {
            $table->index('status', 'idx_seats_status');
        });

        Schema::table('hall_allotments', function (Blueprint $table) {
            // Note: status index already exists from original migration
            // If not, uncomment the line below:
            // $table->index('status', 'idx_hall_allotments_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_hall_status_active');
        });

        Schema::table('seats', function (Blueprint $table) {
            $table->dropIndex('idx_seats_status');
        });

        // If you added the index above, uncomment this:
        // Schema::table('hall_allotments', function (Blueprint $table) {
        //     $table->dropIndex('idx_hall_allotments_status');
        // });
    }
};
