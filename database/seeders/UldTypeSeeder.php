<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class UldTypeSeeder extends Seeder
{
    protected $uldTypes = [
        'pmc' => [
            'code' => 'PMC',
            'name' => 'Pallet with Net',
            'tare_weight' => 110,
            'max_gross_weight' => 6033,
            'positions_required' => 2,
            'color' => '#fd7e14',
            'icon' => 'box-seam',
            'allowed_holds' => ['FWD', 'AFT'],
            'restrictions' => [
                'requires_adjacent_positions' => true,
                'requires_vertical_positions' => true,
            ],
        ],
        'ake' => [
            'code' => 'AKE',
            'name' => 'LD3 Container',
            'tare_weight' => 85,
            'max_gross_weight' => 1588,
            'positions_required' => 1,
            'color' => '#0dcaf0',
            'icon' => 'luggage-fill',
            'allowed_holds' => ['FWD', 'AFT', 'BULK'],
            'restrictions' => [
                'requires_adjacent_positions' => false,
                'requires_vertical_positions' => false,
            ],
        ],
        'akh' => [
            'code' => 'AKH',
            'name' => 'LD3-45 Container',
            'tare_weight' => 85,
            'max_gross_weight' => 1588,
            'positions_required' => 1,
            'color' => '#198754',
            'icon' => 'box-seam-fill',
            'allowed_holds' => ['FWD', 'AFT', 'BULK'],
            'restrictions' => [
                'requires_adjacent_positions' => false,
                'requires_vertical_positions' => false,
            ],
        ],
    ];

    public function run(): void
    {
        $airlines = Airline::all();

        foreach ($airlines as $airline) {
            $selectedTypes = collect($this->uldTypes)
                ->map(function ($type) use ($airline) {
                    $type['units'] = $this->generateUnits($type['code'], rand(3, 40), $airline);

                    return $type;
                })->toArray();

            $airline->settings()->updateOrCreate(
                ['key' => 'uld_types'],
                ['value' => json_encode($selectedTypes)]
            );
        }
    }

    protected function generateUnits(string $code, int $count, Airline $airline): array
    {
        $units = [];
        $usedNumbers = [];

        for ($i = 0; $i < $count; $i++) {
            do {
                $number = $code.rand(10000, 99999).strtoupper($airline->iata_code);
            } while (in_array($number, $usedNumbers));

            $usedNumbers[] = $number;
            $units[] = [
                'number' => $number,
                'serviceable' => rand(1, 100) <= 80, // 80% chance of being serviceable
            ];
        }

        return $units;
    }
}
