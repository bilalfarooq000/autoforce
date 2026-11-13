<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_diagram_marks', function (Blueprint $table) {
            $table->foreignId('inspection_item_id')->nullable()->after('inspection_id')
                ->constrained('inspection_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_diagram_marks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inspection_item_id');
        });
    }
};
