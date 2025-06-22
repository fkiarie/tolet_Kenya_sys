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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->onDelete('cascade');
            $table->string('unit_no');
            $table->enum('state', ['vacant', 'occupied', 'under_maintenance']);
            $table->decimal('rent_per_month', 10, 2);
            $table->decimal('deposit', 10, 2);
            $table->enum('unit_type', ['bedsitter', 'studio', '1_bedroom', '2_bedroom', '3_bedroom', 'shop', 'standalone']);
            $table->timestamps();

            $table->unique(['building_id', 'unit_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
