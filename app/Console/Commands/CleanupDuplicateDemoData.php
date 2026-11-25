<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
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
        $this->cleanupDuplicateInvoices($dryRun);
        $this->cleanupKnownTestCustomers($dryRun);
        $this->cleanupQaRetestArtifacts($dryRun);

        return self::SUCCESS;
    }

    /**
     * The duplicate-inspection cleanup above only reunites inspections — but
     * invoices.inspection_id is nullOnDelete(), not cascadeOnDelete(), so an
     * earlier run that deduplicated inspections left their invoices behind as
     * orphans (e.g. Fatima Noor's 22 identical PKR 15,000 invoices for one
     * "AC not cooling properly" job, flagged in the 20 Nov 2026 QA retest).
     * This catches invoices for the same vehicle with identical totals AND
     * identical line items (not just a coincidental matching total).
     */
    private function cleanupDuplicateInvoices(bool $dryRun): void
    {
        $groups = Invoice::select('vehicle_id', 'subtotal', 'tax', 'total', DB::raw('COUNT(*) as total_count'))
            ->groupBy('vehicle_id', 'subtotal', 'tax', 'total')
            ->having('total_count', '>', 1)
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No duplicate invoices found.');

            return;
        }

        $removed = 0;
        $signature = fn ($invoice) => $invoice->items
            ->map(fn ($item) => $item->description.'|'.$item->amount)
            ->sort()->values()->implode(',');

        foreach ($groups as $group) {
            $candidates = Invoice::where('vehicle_id', $group->vehicle_id)
                ->where('subtotal', $group->subtotal)
                ->where('tax', $group->tax)
                ->where('total', $group->total)
                ->with('items')
                ->get();

            foreach ($candidates->groupBy($signature) as $sameShapeInvoices) {
                if ($sameShapeInvoices->count() <= 1) {
                    continue;
                }

                $keep = $sameShapeInvoices->sortBy('id')->first();
                $duplicates = $sameShapeInvoices->where('id', '!=', $keep->id);

                $this->line("Vehicle #{$group->vehicle_id} — keeping invoice {$keep->invoice_number} (PKR {$group->total}), removing {$duplicates->count()} duplicate(s): ".$duplicates->pluck('invoice_number')->implode(', ').'.');

                if (! $dryRun) {
                    foreach ($duplicates as $duplicate) {
                        $duplicate->delete();
                    }
                }

                $removed += $duplicates->count();
            }
        }

        $this->info(($dryRun ? '[DRY RUN] Would remove ' : 'Removed ').$removed.' duplicate invoice(s) (and their cascaded items/payments).');
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

    /**
     * Test data explicitly called out in the 20 Nov 2026 QA retest report:
     * customer "Test Inam" / booking #3, and the two QAT-prefixed vehicles
     * created while walking the full booking-to-collection flow.
     */
    private function cleanupQaRetestArtifacts(bool $dryRun): void
    {
        $bookings = Booking::where('customer_phone', '0300-1112233')
            ->orWhere('customer_name', 'like', '%Test Inam%')
            ->get();

        foreach ($bookings as $booking) {
            $this->line("Booking #{$booking->id} \"{$booking->customer_name}\" — ".($dryRun ? 'would delete' : 'deleting').'.');

            if (! $dryRun) {
                $booking->delete();
            }
        }

        // Deleting the customer below cascades their vehicles (and those
        // vehicles' inspections/invoices/payments), but QAT-% vehicles are
        // matched directly too in case one was registered under a different
        // customer account during testing.
        $vehicles = Vehicle::where('registration_number', 'like', 'QAT-%')->get();

        foreach ($vehicles as $vehicle) {
            $this->line("Vehicle {$vehicle->registration_number} (#{$vehicle->id}) — ".($dryRun ? 'would delete' : 'deleting').' (and its jobs/invoices/payments via cascade).');

            if (! $dryRun) {
                $vehicle->delete();
            }
        }

        if ($bookings->isEmpty() && $vehicles->isEmpty()) {
            $this->info('No QA-retest booking or QAT-% vehicle artifacts found.');
        }
    }

    private function cleanupKnownTestCustomers(bool $dryRun): void
    {
        $phones = ['0300-7778889', '0300-7778890', 'abc', '0300-1112233'];

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
