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
        Schema::create('fee_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('fee_type'); // e.g., 'hall_rent', 'dining_charge', etc.
            $table->decimal('amount', 10, 2);
            $table->string('period')->default('monthly'); // monthly, semester, yearly
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint: one active configuration per fee_type per hall
            $table->unique(['hall_id', 'fee_type', 'is_active'], 'unique_active_fee_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_configurations');
    }
};
