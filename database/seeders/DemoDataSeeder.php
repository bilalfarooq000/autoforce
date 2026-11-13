<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\InspectionChecklist;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $lahore = Branch::firstOrCreate(['name' => 'AutoForce Lahore'], ['city' => 'Lahore', 'address' => 'Main Boulevard, Lahore', 'phone' => '042-1234567']);
        $karachi = Branch::firstOrCreate(['name' => 'AutoForce Karachi'], ['city' => 'Karachi', 'address' => 'Shahrah-e-Faisal, Karachi', 'phone' => '021-1234567']);

        $admin = User::firstOrCreate(['email' => 'admin@autoforce.pk'], ['name' => 'AutoForce Admin', 'password' => bcrypt('password'), 'branch_id' => $lahore->id]);
        $admin->syncRoles(['admin']);

        $advisor = User::firstOrCreate(['email' => 'advisor@autoforce.pk'], ['name' => 'Ali - Service Advisor', 'password' => bcrypt('password'), 'branch_id' => $lahore->id]);
        $advisor->syncRoles(['service_advisor']);

        $technicians = [
            User::firstOrCreate(['email' => 'technician@autoforce.pk'], ['name' => 'Usman - Technician', 'password' => bcrypt('password'), 'branch_id' => $lahore->id]),
            User::firstOrCreate(['email' => 'technician2@autoforce.pk'], ['name' => 'Bilal - Technician', 'password' => bcrypt('password'), 'branch_id' => $lahore->id]),
        ];
        foreach ($technicians as $tech) {
            $tech->syncRoles(['technician']);
        }

        $karachiManager = User::firstOrCreate(['email' => 'manager.khi@autoforce.pk'], ['name' => 'Sara - Manager (Karachi)', 'password' => bcrypt('password'), 'branch_id' => $karachi->id]);
        $karachiManager->syncRoles(['manager']);

        $customers = [
            User::firstOrCreate(['email' => 'customer@example.com'], ['name' => 'Ahmed Raza', 'password' => bcrypt('password'), 'phone' => '0300-1234567']),
            User::firstOrCreate(['email' => 'customer2@example.com'], ['name' => 'Fatima Noor', 'password' => bcrypt('password'), 'phone' => '0301-9876543']),
            User::firstOrCreate(['email' => 'customer3@example.com'], ['name' => 'Hamza Sheikh', 'password' => bcrypt('password'), 'phone' => '0333-5551212']),
        ];
        foreach ($customers as $c) {
            $c->syncRoles(['customer']);
        }

        $vehiclesData = [
            ['reg' => 'LEA-2024', 'make' => 'Toyota', 'model' => 'Corolla', 'year' => '2019', 'mileage' => 54500, 'customer' => $customers[0], 'branch' => $lahore, 'next_due_date' => now()->addDays(5), 'next_due_mileage' => 55000],
            ['reg' => 'LEB-5567', 'make' => 'Honda', 'model' => 'Civic', 'year' => '2021', 'mileage' => 32000, 'customer' => $customers[1], 'branch' => $lahore, 'next_due_date' => now()->addMonths(2), 'next_due_mileage' => 40000],
            ['reg' => 'KHI-7781', 'make' => 'Suzuki', 'model' => 'Alto', 'year' => '2022', 'mileage' => 18000, 'customer' => $customers[2], 'branch' => $karachi, 'next_due_date' => now()->addMonths(3), 'next_due_mileage' => 25000],
        ];

        $vehicles = [];
        foreach ($vehiclesData as $v) {
            $vehicles[] = Vehicle::firstOrCreate(
                ['registration_number' => $v['reg']],
                [
                    'customer_id' => $v['customer']->id,
                    'branch_id' => $v['branch']->id,
                    'make' => $v['make'],
                    'model' => $v['model'],
                    'year' => $v['year'],
                    'chassis_number' => strtoupper($v['make']).'-'.random_int(100000, 999999),
                    'engine_number' => 'ENG-'.random_int(10000, 99999),
                    'mileage' => $v['mileage'],
                    'color' => ['White', 'Silver', 'Black'][array_rand([0, 1, 2])],
                    'vehicle_type' => 'car',
                    'fuel_type' => 'petrol',
                    'transmission' => 'manual',
                    'purchase_date' => now()->subYears(2),
                    'insurance_expiry' => now()->addDays(20),
                    'next_service_due_date' => $v['next_due_date'],
                    'next_service_due_mileage' => $v['next_due_mileage'],
                ]
            );
        }

        [$corolla, $civic, $alto] = $vehicles;

        // Job 1 — fully completed with paid invoice.
        $this->buildJob($corolla, $technicians[0], $admin, 'completed', [
            ['title' => 'Front suspension bushes worn', 'comment' => 'Noticeable play and clunking noise over bumps.', 'urgency' => 'advisory', 'cost' => 8500],
            ['title' => 'Brake pads (front)', 'comment' => 'Approx. 30% life remaining.', 'urgency' => 'satisfactory', 'cost' => 0],
        ], quotation: true, invoice: true, payFully: true);

        // Job 2 — ready for collection, invoice partially paid.
        $this->buildJob($civic, $technicians[1], $advisor, 'ready_for_collection', [
            ['title' => 'AC not cooling properly', 'comment' => 'Low refrigerant, minor leak at condenser.', 'urgency' => 'critical', 'cost' => 15000],
        ], quotation: true, invoice: true, payFully: false);

        // Job 3 — currently in repair (quotation approved).
        $this->buildJob($alto, null, $karachiManager, 'in_repair', [
            ['title' => 'Clutch slipping on acceleration', 'comment' => 'Clutch plate worn, needs replacement.', 'urgency' => 'critical', 'cost' => 12000],
        ], quotation: true, invoice: false, payFully: false);

        // Job 4 — awaiting customer approval.
        $this->buildJob($corolla, $technicians[0], $advisor, 'awaiting_approval', [
            ['title' => 'Engine oil leak at gasket', 'comment' => 'Minor seepage, recommend gasket replacement.', 'urgency' => 'advisory', 'cost' => 6000],
        ], quotation: true, invoice: false, payFully: false);

        // Job 5 — just checked in, awaiting diagnosis.
        $this->buildJob($civic, null, $advisor, 'checked_in', [], quotation: false, invoice: false, payFully: false);
    }

    private function buildJob(Vehicle $vehicle, ?User $technician, User $createdBy, string $status, array $items, bool $quotation, bool $invoice, bool $payFully): void
    {
        $reason = $items[0]['title'] ?? 'General check-up';

        // This whole method used to run unconditionally on every seeder call —
        // harmless the first time, but it silently piled up a fresh duplicate
        // of every demo job (and its quotation/invoice/payment) on every
        // redeploy that re-ran seeding. One job per vehicle+reason+status, ever.
        if (Inspection::where('vehicle_id', $vehicle->id)->where('reason', $reason)->where('status', $status)->exists()) {
            return;
        }

        $inspection = Inspection::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $vehicle->branch_id,
            'technician_id' => $technician?->id,
            'created_by' => $createdBy->id,
            'reason' => $reason,
            'mileage_at_visit' => $vehicle->mileage,
            'status' => $status,
            'summary' => $status !== 'checked_in' ? 'Diagnosis completed, see findings below.' : null,
            'inspection_started_at' => $status !== 'checked_in' ? now()->subDays(3) : null,
            'diagnosis_completed_at' => in_array($status, ['awaiting_approval', 'in_repair', 'ready_for_collection', 'completed']) ? now()->subDays(2) : null,
            'ready_at' => in_array($status, ['ready_for_collection', 'completed']) ? now()->subDay() : null,
            'collected_at' => $status === 'completed' ? now() : null,
        ]);

        // Backdate created_at so ready_at/collected_at land chronologically after it (avoids negative turnaround).
        \Illuminate\Support\Facades\DB::table('inspections')->where('id', $inspection->id)->update(['created_at' => now()->subDays(4)]);
        $inspection->refresh();

        // Standard checklist — fully checked (mostly satisfactory) for jobs past the "checked in" stage,
        // left untouched for the fresh "checked in" job so it demonstrates the real starting state.
        $order = 0;
        foreach (InspectionChecklist::template() as $category => $checklistTitles) {
            foreach ($checklistTitles as $title) {
                InspectionItem::create([
                    'inspection_id' => $inspection->id,
                    'category' => $category,
                    'title' => $title,
                    'urgency' => $status !== 'checked_in' ? 'satisfactory' : null,
                    'is_checked' => $status !== 'checked_in',
                    'sort_order' => $order++,
                ]);
            }
        }

        $createdItems = [];
        foreach ($items as $i) {
            $createdItems[] = InspectionItem::create([
                'inspection_id' => $inspection->id,
                'category' => null,
                'title' => $i['title'],
                'technician_comment' => $i['comment'],
                'urgency' => $i['urgency'],
                'is_checked' => true,
                'estimated_cost' => $i['cost'],
            ]);
        }

        $quotationModel = null;
        if ($quotation && count($createdItems)) {
            $total = array_sum(array_column($items, 'cost'));
            $quotationModel = Quotation::create([
                'inspection_id' => $inspection->id,
                'total_amount' => $total,
                'status' => in_array($status, ['in_repair', 'ready_for_collection', 'completed']) ? 'approved' : 'pending',
                'decided_at' => in_array($status, ['in_repair', 'ready_for_collection', 'completed']) ? now()->subDay() : null,
            ]);

            foreach ($createdItems as $idx => $ci) {
                QuotationItem::create([
                    'quotation_id' => $quotationModel->id,
                    'inspection_item_id' => $ci->id,
                    'description' => $ci->title,
                    'amount' => $items[$idx]['cost'],
                ]);
            }
        }

        if ($invoice && $quotationModel) {
            $inv = Invoice::create([
                'invoice_number' => 'AF-'.now()->format('Ymd').'-'.strtoupper(substr(md5((string) $inspection->id.microtime()), 0, 4)),
                'vehicle_id' => $vehicle->id,
                'customer_id' => $vehicle->customer_id,
                'inspection_id' => $inspection->id,
                'subtotal' => $quotationModel->total_amount,
                'tax' => 0,
                'total' => $quotationModel->total_amount,
                'status' => 'unpaid',
                'issued_at' => now()->subDay(),
            ]);

            foreach ($createdItems as $ci) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'description' => $ci->title,
                    'type' => 'labor',
                    'quantity' => 1,
                    'unit_price' => $ci->estimated_cost,
                    'amount' => $ci->estimated_cost,
                    'warranty_months' => 3,
                ]);
            }

            if ($payFully) {
                Payment::create(['invoice_id' => $inv->id, 'received_by' => $technician?->id, 'amount' => $inv->total, 'method' => 'cash', 'paid_at' => now()]);
                $inv->update(['status' => 'paid']);
            } else {
                $partial = round($inv->total * 0.4, -2);
                if ($partial > 0) {
                    Payment::create(['invoice_id' => $inv->id, 'received_by' => $technician?->id, 'amount' => $partial, 'method' => 'cash', 'paid_at' => now()]);
                    $inv->update(['status' => 'partial']);
                }
            }
        }
    }
}
