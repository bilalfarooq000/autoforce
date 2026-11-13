<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendServiceReminders extends Command
{
    protected $signature = 'autoforce:send-service-reminders';

    protected $description = 'Notify customers whose vehicles are approaching their next recommended service (date or mileage).';

    public function handle(): int
    {
        $due = Vehicle::with('customer')->get()->filter(fn (Vehicle $v) => $v->isServiceDueSoon());

        foreach ($due as $vehicle) {
            NotificationService::notify($vehicle, 'service_reminder');
            $this->info("Reminder sent for {$vehicle->registration_number}");
        }

        $this->info("Done. {$due->count()} reminder(s) sent.");

        return self::SUCCESS;
    }
}
