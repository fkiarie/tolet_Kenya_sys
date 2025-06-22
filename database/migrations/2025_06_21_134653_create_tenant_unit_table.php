<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->date('move_in_date');
            $table->date('move_out_date')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_unit');
    }
};
// This migration creates a pivot table for the many-to-many relationship between tenants and units.
// It includes foreign keys for both tenant_id and unit_id, along with move_in_date and move_out_date fields.
// The unique constraint ensures that a tenant cannot be assigned to the same unit more than once.
// The onDelete('cascade') ensures that if a tenant or unit is deleted, the corresponding records in this pivot table are also removed.
// This is important for maintaining data integrity in the database.
// The move_in_date and move_out_date fields allow tracking of when a tenant moves into or out of a unit.
// The timestamps() method adds created_at and updated_at columns to the table, which can be useful for tracking changes.
// The migration is reversible, meaning it can be rolled back if needed, which is a standard practice in Laravel migrations.
// This migration is essential for managing the relationships between tenants and units in a rental management system.
// It allows for efficient querying of which tenants are in which units and vice versa.
