<?php

namespace App\Database\Seeds;

use App\Models\DivisionModel;
use CodeIgniter\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        $divisions = [
            ['name' => 'Mining Equipment',       'slug' => 'mining-equipment',       'sort_order' => 10],
            ['name' => 'Construction Equipment', 'slug' => 'construction-equipment', 'sort_order' => 20],
            ['name' => 'Drilling Systems',        'slug' => 'drilling-systems',        'sort_order' => 30],
        ];

        $model    = new DivisionModel();
        $inserted = 0;

        foreach ($divisions as $division) {
            if ($model->where('slug', $division['slug'])->first()) {
                continue;
            }

            $model->insert([
                'name'        => $division['name'],
                'slug'        => $division['slug'],
                'description' => null,
                'is_active'   => 1,
                'sort_order'  => $division['sort_order'],
            ]);
            $inserted++;
        }

        // Backfill division_id onto the existing demo categories (category name => division slug).
        $categoryDivisionMap = [
            'Hydraulic Systems'         => 'drilling-systems',
            'Feed & Drill Systems'      => 'drilling-systems',
            'Electrical & Sensors'      => 'drilling-systems',
            'Engine Components'        => 'construction-equipment',
            'Transmission & Driveline' => 'construction-equipment',
            'Structural & Frame Parts' => 'construction-equipment',
            'Spare Parts'              => 'mining-equipment',
            'Service Kits'             => 'mining-equipment',
            'Filters & Consumables'    => 'mining-equipment',
            'Seals & Gaskets'          => 'mining-equipment',
            'Equipment'                => 'mining-equipment',
        ];

        $categories = $this->db->table('categories');
        foreach ($categoryDivisionMap as $categoryName => $divisionSlug) {
            $divisionRow = $model->where('slug', $divisionSlug)->first();
            if (! $divisionRow) {
                continue;
            }

            $categories->where('name', $categoryName)->update(['division_id' => $divisionRow['id']]);
        }

        echo "  ✓ Divisions seeded: {$inserted} inserted (categories backfilled with division_id).\n";
    }
}
