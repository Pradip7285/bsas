<?php

namespace App\Database\Seeds;

use App\Models\OemModel;
use CodeIgniter\Database\Seeder;

class OemSeeder extends Seeder
{
    public function run()
    {
        $oems = [
            ['name' => 'Atlas Copco', 'slug' => 'atlas-copco', 'sort_order' => 10],
            ['name' => 'Sandvik',     'slug' => 'sandvik',     'sort_order' => 20],
            ['name' => 'Komatsu',     'slug' => 'komatsu',     'sort_order' => 30],
            ['name' => 'Caterpillar', 'slug' => 'caterpillar', 'sort_order' => 40],
        ];

        $model    = new OemModel();
        $inserted = 0;

        foreach ($oems as $oem) {
            if ($model->where('slug', $oem['slug'])->first()) {
                continue;
            }

            $model->insert([
                'name'        => $oem['name'],
                'slug'        => $oem['slug'],
                'description' => null,
                'is_active'   => 1,
                'sort_order'  => $oem['sort_order'],
            ]);
            $inserted++;
        }

        // Backfill oem_id onto the demo vehicles seeded in VehicleSeeder (vehicle slug => OEM slug).
        $vehicleOemMap = [
            'mpr100'                   => 'atlas-copco',
            'mpr-200-exploration-rig'  => 'atlas-copco',
            'track-rig-300'            => 'sandvik',
            'rig-alpha-feed-system'    => 'sandvik',
            'hp-500-series-pump'       => 'caterpillar',
            'hp-750-series-pump'       => 'caterpillar',
            'excavator-dl-320'         => 'komatsu',
            'generator-set-ec-200'     => 'komatsu',
        ];

        $vehicles = $this->db->table('vehicles');
        foreach ($vehicleOemMap as $vehicleSlug => $oemSlug) {
            $oemRow = $model->where('slug', $oemSlug)->first();
            if (! $oemRow) {
                continue;
            }

            $vehicles->where('slug', $vehicleSlug)->update(['oem_id' => $oemRow['id']]);
        }

        echo "  ✓ OEMs seeded: {$inserted} inserted (vehicles backfilled with oem_id).\n";
    }
}
