<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class UldSeeder extends Seeder
{
    public function run(): void
    {
        $uldTypes = [
            'AKE' => [
                'code' => 'AKE',
                'name' => 'LD3 Container',
                'tare_weight' => 65,
                'max_gross_weight' => 1588,
                'positions_required' => 1,
                'color' => '#0dcaf0',
                'icon' => 'box-seam',
                'allowed_holds' => ['FWD', 'AFT', 'BULK'],
                'restrictions' => [
                    'requires_adjacent_positions' => false,
                    'requires_vertical_positions' => false,
                ],
            ],
            'AKH' => [
                'code' => 'AKH',
                'name' => 'LD3 Insulated Container',
                'tare_weight' => 64,
                'max_gross_weight' => 1588,
                'positions_required' => 1,
                'color' => '#20c997',
                'icon' => 'box-seam',
                'allowed_holds' => ['FWD', 'AFT'],
                'restrictions' => [
                    'requires_adjacent_positions' => false,
                    'requires_vertical_positions' => false,
                ],
            ],
            'PAG' => [
                'code' => 'PAG',
                'name' => 'LD7 Pallet',
                'tare_weight' => 95,
                'max_gross_weight' => 6800,
                'positions_required' => 2,
                'color' => '#fd7e14',
                'icon' => 'box-seam',
                'allowed_holds' => ['FWD', 'AFT'],
                'restrictions' => [
                    'requires_adjacent_positions' => true,
                    'requires_vertical_positions' => false,
                ],
            ],
            'PLA' => [
                'code' => 'PLA',
                'name' => 'LD11 Pallet',
                'tare_weight' => 90,
                'max_gross_weight' => 2300,
                'positions_required' => 2,
                'color' => '#fd7e14',
                'icon' => 'box-seam',
                'allowed_holds' => ['FWD', 'AFT'],
                'restrictions' => [
                    'requires_adjacent_positions' => true,
                    'requires_vertical_positions' => false,
                ],
            ],
            'PMC' => [
                'code' => 'PMC',
                'name' => 'Pallet with Net',
                'tare_weight' => 110,
                'max_gross_weight' => 3400,
                'positions_required' => 2,
                'color' => '#fd7e14',
                'icon' => 'box-seam',
                'allowed_holds' => ['FWD', 'AFT'],
                'restrictions' => [
                    'requires_adjacent_positions' => true,
                    'requires_vertical_positions' => true,
                ],
            ],
        ];

        $airlines = Airline::all();

        foreach ($airlines as $airline) {
            $airline->settings()->updateOrCreate(
                ['key' => 'uld_types'],
                ['value' => json_encode($uldTypes)]
            );

            foreach ($uldTypes as $key => $type) {
                for ($i = 1; $i <= 5; $i++) {
                    $containerNumber = $type['code'].str_pad($i, 5, '0', STR_PAD_LEFT).$airline->iata_code;

                    $airline->containers()->updateOrCreate(
                        ['container_number' => $containerNumber],
                        [
                            'tare_weight' => $type['tare_weight'],
                            'max_weight' => $type['max_gross_weight'],
                            'serviceable' => true,
                            'uld_type' => $key,
                        ]
                    );
                }
            }
        }
    }
}
