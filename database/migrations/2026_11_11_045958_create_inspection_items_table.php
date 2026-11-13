<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->string('category')->nullable(); // null = ad-hoc finding added by technician, not from the standard checklist
            $table->string('title');
            $table->text('technician_comment')->nullable();
            $table->enum('urgency', ['critical', 'advisory', 'satisfactory'])->nullable();
            $table->boolean('is_checked')->default(false);
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
