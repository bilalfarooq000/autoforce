<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_diagram_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->decimal('x', 5, 2); // percentage position (0-100) across the diagram, scale-independent
            $table->decimal('y', 5, 2);
            $table->enum('severity', ['critical', 'advisory', 'satisfactory'])->default('advisory');
            $table->string('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_diagram_marks');
    }
};
