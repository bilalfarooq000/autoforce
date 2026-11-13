<?php

namespace App\Support;

/**
 * AutoForce's standard multi-point inspection checklist.
 * Every vehicle that is checked in gets these items created automatically
 * (status = unchecked) so nothing gets skipped during diagnosis.
 *
 * Each item also has a fixed position on one of the five vehicle diagram
 * views (front/rear/left/right/top), so marking an item's status
 * automatically places/updates a marker at that part's real-world location.
 */
class InspectionChecklist
{
    public const VIEWS = ['front' => 'Front', 'rear' => 'Rear', 'left' => 'Left', 'right' => 'Right', 'top' => 'Top'];

    public static function template(): array
    {
        return [
            'Engine & Fluids' => [
                'Engine oil level & condition',
                'Coolant level',
                'Oil / fluid leaks',
            ],
            'Brakes' => [
                'Brake pads (front)',
                'Brake pads (rear)',
                'Brake fluid level',
            ],
            'Suspension & Steering' => [
                'Shock absorbers',
                'Suspension bushes & joints',
                'Steering alignment',
            ],
            'Tires & Wheels' => [
                'Tire tread depth',
                'Tire pressure',
                'Wheel balancing',
            ],
            'Electrical & Battery' => [
                'Battery condition & terminals',
                'Alternator charging',
            ],
            'Lights & Signals' => [
                'Headlights & taillights',
                'Turn indicators',
            ],
            'AC & Interior' => [
                'AC cooling performance',
                'Wipers & horn',
            ],
        ];
    }

    /** Fixed diagram view + coordinates (percent 0-100 within that view) for each standard item. */
    public static function positions(): array
    {
        return [
            'Engine oil level & condition' => ['view' => 'top', 'x' => 42, 'y' => 18],
            'Coolant level' => ['view' => 'top', 'x' => 58, 'y' => 18],
            'Oil / fluid leaks' => ['view' => 'top', 'x' => 50, 'y' => 28],

            'Brake pads (front)' => ['view' => 'left', 'x' => 25, 'y' => 72],
            'Brake pads (rear)' => ['view' => 'left', 'x' => 78, 'y' => 72],
            'Brake fluid level' => ['view' => 'top', 'x' => 50, 'y' => 12],

            'Shock absorbers' => ['view' => 'right', 'x' => 25, 'y' => 72],
            'Suspension bushes & joints' => ['view' => 'right', 'x' => 78, 'y' => 72],
            'Steering alignment' => ['view' => 'front', 'x' => 50, 'y' => 80],

            'Tire tread depth' => ['view' => 'left', 'x' => 25, 'y' => 85],
            'Tire pressure' => ['view' => 'right', 'x' => 25, 'y' => 85],
            'Wheel balancing' => ['view' => 'left', 'x' => 78, 'y' => 85],

            'Battery condition & terminals' => ['view' => 'top', 'x' => 30, 'y' => 15],
            'Alternator charging' => ['view' => 'top', 'x' => 70, 'y' => 15],

            'Headlights & taillights' => ['view' => 'front', 'x' => 20, 'y' => 40],
            'Turn indicators' => ['view' => 'front', 'x' => 80, 'y' => 40],

            'AC cooling performance' => ['view' => 'top', 'x' => 50, 'y' => 55],
            'Wipers & horn' => ['view' => 'front', 'x' => 50, 'y' => 20],
        ];
    }

    public static function positionFor(string $title): array
    {
        return self::positions()[$title] ?? ['view' => 'top', 'x' => 50, 'y' => 50];
    }

    public static function flatCount(): int
    {
        return array_sum(array_map('count', self::template()));
    }
}
