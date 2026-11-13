<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('reason')->nullable();
            $table->unsignedInteger('mileage_at_visit')->default(0);

            // Full workshop job lifecycle — drives the Kanban dashboard and customer notifications.
            $table->enum('status', [
                'checked_in',         // vehicle arrived, awaiting inspection
                'inspecting',         // technician actively diagnosing
                'awaiting_approval',  // quotation sent, waiting on customer
                'in_repair',          // customer approved, repair underway
                'ready_for_collection', // repair/diagnosis finished, vehicle ready
                'completed',          // collected by customer, job closed
            ])->default('checked_in');

            $table->uuid('access_token')->unique();
            $table->text('summary')->nullable();

            $table->timestamp('inspection_started_at')->nullable();
            $table->timestamp('diagnosis_completed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('collected_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
