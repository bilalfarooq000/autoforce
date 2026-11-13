<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_diagram_marks', function (Blueprint $table) {
            $table->enum('view', ['front', 'rear', 'left', 'right', 'top'])->default('top')->after('inspection_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_diagram_marks', function (Blueprint $table) {
            $table->dropColumn('view');
        });
    }
};
