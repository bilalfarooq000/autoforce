<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Engine Parts', 'Brakes & Suspension', 'Tyres', 'Fluids & Lubricants',
            'Filters', 'Batteries & Electrical', 'Body & Accessories', 'Consumables',
        ])->mapWithKeys(fn ($name) => [$name => InventoryCategory::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        )]);

        $templates = [
            ['Engine Oil 5W-30 (1L)', 'Fluids & Lubricants', 'liter', 1200, 1800, 10, 40],
            ['Oil Filter', 'Filters', 'pcs', 450, 700, 5, 25],
            ['Air Filter', 'Filters', 'pcs', 600, 950, 5, 20],
            ['Brake Pad Set (Front)', 'Brakes & Suspension', 'set', 3500, 5200, 3, 12],
            ['Brake Fluid DOT 4 (500ml)', 'Fluids & Lubricants', 'pcs', 500, 800, 5, 15],
            ['Tyre 185/65R15', 'Tyres', 'pcs', 8500, 11500, 4, 16],
            ['Car Battery 12V 55Ah', 'Batteries & Electrical', 'pcs', 12000, 16000, 2, 8],
            ['Spark Plug', 'Engine Parts', 'pcs', 350, 550, 8, 32],
            ['Shock Absorber (Rear)', 'Brakes & Suspension', 'pcs', 4200, 6000, 2, 10],
            ['Wiper Blade Set', 'Body & Accessories', 'set', 900, 1400, 4, 16],
            ['Coolant (1L)', 'Fluids & Lubricants', 'liter', 600, 950, 6, 24],
            ['Shop Rags/Cleaning Pack', 'Consumables', 'pack', 250, 400, 10, 40],
        ];

        foreach (Branch::all() as $branch) {
            $performer = User::where('branch_id', $branch->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['manager', 'inventory_manager']))
                ->first() ?? User::role('admin')->first();

            if (! $performer) {
                continue;
            }

            foreach ($templates as [$name, $categoryName, $unit, $cost, $sell, $reorder, $opening]) {
                $item = InventoryItem::firstOrCreate(
                    ['branch_id' => $branch->id, 'name' => $name],
                    [
                        'category_id' => $categories[$categoryName]->id,
                        'unit' => $unit,
                        'cost_price' => $cost,
                        'selling_price' => $sell,
                        'reorder_level' => $reorder,
                        'quantity_on_hand' => 0,
                    ]
                );

                if ($item->wasRecentlyCreated) {
                    // Spread starting stock across the full 0..opening range so some
                    // items land above and some genuinely below reorder level —
                    // otherwise the Low Stock report would always be empty in demo data.
                    $startingQty = random_int(0, $opening);

                    InventoryMovement::create([
                        'branch_id' => $branch->id,
                        'item_id' => $item->id,
                        'type' => 'stock_in',
                        'quantity' => max($startingQty, 1),
                        'unit_cost' => $cost,
                        'reference' => 'Initial stock',
                        'performed_by' => $performer->id,
                    ]);

                    $item->update(['quantity_on_hand' => max($startingQty, 1)]);
                }
            }
        }
    }
}
