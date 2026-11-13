<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_item_id')->constrained('inspection_items')->cascadeOnDelete();
            $table->string('path');
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->string('stage')->default('before'); // before | after
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_media');
    }
};
