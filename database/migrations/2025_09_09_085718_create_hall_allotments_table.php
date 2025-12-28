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
        Schema::create('hall_allotments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained('seats')->onDelete('cascade');
            $table->date('allotment_date');
            $table->date('starting_month');
            $table->date('ending_month')->nullable(); // ✅ Cancellation effective from this month
            $table->date('cancel_request_date')->nullable(); // ✅ When cancel was requested
            $table->string('status')->default('active'); // active, cancel_requested, cancelled
            $table->timestamps();

            $table->index(['seat_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_allotments');
    }
};
