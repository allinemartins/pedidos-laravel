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
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('requester_name');
            $table->string('destination');
            $table->date('departure_date');
            $table->date('return_date');

            $table->string('status', 20)->default('REQUESTED'); // REQUESTED, APPROVED, CANCELED

            $table->timestamps();

            $table->index('status');
            $table->index('destination');
            $table->index(['departure_date', 'return_date']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
