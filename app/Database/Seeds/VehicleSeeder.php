<?php

namespace App\Database\Seeds;

use App\Models\VehicleModel;
use CodeIgniter\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        $vehicles = [
            ['name' => 'MPR-200 Exploration Rig', 'slug' => 'mpr-200-exploration-rig', 'category' => 'Equipment', 'sort_order' => 10],
            ['name' => 'Track Rig 300',            'slug' => 'track-rig-300',            'category' => 'Equipment', 'sort_order' => 20],
            ['name' => 'HP-500 Series Pump',        'slug' => 'hp-500-series-pump',       'category' => 'Hydraulic Systems', 'sort_order' => 10],
            ['name' => 'HP-750 Series Pump',        'slug' => 'hp-750-series-pump',       'category' => 'Hydraulic Systems', 'sort_order' => 20],
            ['name' => 'Excavator DL-320',          'slug' => 'excavator-dl-320',         'category' => 'Transmission & Driveline', 'sort_order' => 10],
            ['name' => 'Rig Alpha Feed System',     'slug' => 'rig-alpha-feed-system',    'category' => 'Feed & Drill Systems', 'sort_order' => 10],
            ['name' => 'Generator Set EC-200',      'slug' => 'generator-set-ec-200',     'category' => 'Engine Components', 'sort_order' => 10],
        ];

        $categoryTable = $this->db->table('categories');
        $model         = new VehicleModel();
        $inserted      = 0;

        foreach ($vehicles as $vehicle) {
            if ($model->where('slug', $vehicle['slug'])->first()) {
                continue;
            }

            $categoryRow = $categoryTable->where('name', $vehicle['category'])->get()->getFirstRow('array');

            $model->insert([
                'name'        => $vehicle['name'],
                'slug'        => $vehicle['slug'],
                'category_id' => $categoryRow['id'] ?? null,
                'description' => null,
                'is_active'   => 1,
                'sort_order'  => $vehicle['sort_order'],
            ]);
            $inserted++;
        }

        echo "  ✓ Vehicles seeded: {$inserted} inserted.\n";
    }
}
