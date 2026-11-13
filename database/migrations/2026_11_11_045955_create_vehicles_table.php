<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->string('registration_number')->unique();
            $table->string('make');
            $table->string('model');
            $table->string('year', 4)->nullable();
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('color')->nullable();
            $table->string('photo_path')->nullable();

            $table->enum('vehicle_type', ['car', 'bike', 'van', 'truck', 'other'])->default('car');
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric', 'cng'])->default('petrol');
            $table->enum('transmission', ['manual', 'automatic'])->default('manual');

            $table->unsignedInteger('mileage')->default(0);
            $table->date('purchase_date')->nullable();
            $table->date('insurance_expiry')->nullable();

            $table->date('next_service_due_date')->nullable();
            $table->unsignedInteger('next_service_due_mileage')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
