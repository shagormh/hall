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
        Schema::table('student_fees', function (Blueprint $table) {
            $table->index('status', 'idx_student_fees_status');
            $table->index('payment_date', 'idx_student_fees_payment_date');
        });

        Schema::table('hall_allotments', function (Blueprint $table) {
            $table->index('allotment_date', 'idx_hall_allotments_date');
            if (!Schema::hasColumn('hall_allotments', 'status_index')) { // Check if index already exists in some form if status is enum
                // Note: status might already be indexed if it was defined as a foreign key or explicitly in original migration
                // But let's ensure it's indexed for our common whereIn queries
                 $table->index('status', 'idx_hall_allotments_status');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index('hall_id', 'idx_students_hall_id');
            $table->index('roll', 'idx_students_roll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropIndex('idx_student_fees_status');
            $table->dropIndex('idx_student_fees_payment_date');
        });

        Schema::table('hall_allotments', function (Blueprint $table) {
            $table->dropIndex('idx_hall_allotments_date');
            $table->dropIndex('idx_hall_allotments_status');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_hall_id');
            $table->dropIndex('idx_students_roll');
        });
    }
};
