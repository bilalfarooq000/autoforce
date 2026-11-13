<?php

namespace App\Console\Commands;

use App\Models\Inspection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off repair for a Railway deploy misconfiguration: RUN_SEEDERS=true ran
 * DemoDataSeeder on every redeploy, and its buildJob() step had no existence
 * check, so every redeploy added one more duplicate of each demo job (and its
 * quotation/invoice/payment). Run once, not wired into the normal seed chain.
 */
class CleanupDuplicateDemoData extends Command
{
    protected $signature = 'autoforce:cleanup-duplicate-demo-data {--dry-run : Report what would be removed without deleting anything}';

    protected $description = 'Remove duplicate seeded inspections (and their cascaded quotation/invoice/payment) created by repeated demo-data seeding, and known QA test accounts.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->cleanupDuplicateInspections($dryRun);
        $this->cleanupKnownTestCustomers($dryRun);

        return self::SUCCESS;
    }

    private function cleanupDuplicateInspections(bool $dryRun): void
    {
        $groups = Inspection::select('vehicle_id', 'reason', 'status', DB::raw('COUNT(*) as total'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('vehicle_id', 'reason', 'status')
            ->having('total', '>', 1)
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No duplicate inspections found.');

            return;
        }

        $removed = 0;

        foreach ($groups as $group) {
            $duplicates = Inspection::where('vehicle_id', $group->vehicle_id)
                ->where('reason', $group->reason)
                ->where('status', $group->status)
                ->where('id', '!=', $group->keep_id)
                ->get();

            $this->line("Vehicle #{$group->vehicle_id} — \"{$group->reason}\" ({$group->status}): keeping #{$group->keep_id}, removing {$duplicates->count()} duplicate(s).");

            if (! $dryRun) {
                foreach ($duplicates as $duplicate) {
                    $duplicate->delete();
                }
            }

            $removed += $duplicates->count();
        }

        $this->info(($dryRun ? '[DRY RUN] Would remove ' : 'Removed ').$removed.' duplicate inspection(s) (and their quotations/invoices/payments via cascade).');
    }

    private function cleanupKnownTestCustomers(bool $dryRun): void
    {
        $phones = ['0300-7778889', '0300-7778890', 'abc'];

        $customers = User::role('customer')->whereIn('phone', $phones)->get();

        if ($customers->isEmpty()) {
            $this->info('No known QA test customers found.');

            return;
        }

        foreach ($customers as $customer) {
            $this->line("Customer #{$customer->id} \"{$customer->name}\" ({$customer->phone}) — ".($dryRun ? 'would delete' : 'deleting').' (and their vehicles/jobs via cascade).');

            if (! $dryRun) {
                $customer->delete();
            }
        }

        $this->info(($dryRun ? '[DRY RUN] Would remove ' : 'Removed ').$customers->count().' test customer(s).');
    }
}
