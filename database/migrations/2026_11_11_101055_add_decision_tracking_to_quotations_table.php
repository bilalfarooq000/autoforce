<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->enum('decision_channel', ['customer_link', 'staff_recorded'])->nullable()->after('status');
            $table->foreignId('decided_by_staff_id')->nullable()->after('decision_channel')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('decided_by_staff_id');
            $table->dropColumn('decision_channel');
        });
    }
};
