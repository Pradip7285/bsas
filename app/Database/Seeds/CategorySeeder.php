<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $categories = [
            ['name' => 'Hydraulic Systems',       'slug' => 'hydraulic-systems',       'description' => 'Hydraulic pumps, cylinders, valves, seals, and service kits.',              'sort_order' => 10],
            ['name' => 'Engine Components',        'slug' => 'engine-components',        'description' => 'Pistons, gaskets, bearings, and rebuild parts for diesel engines.',        'sort_order' => 20],
            ['name' => 'Transmission & Driveline', 'slug' => 'transmission-driveline',   'description' => 'Gearboxes, axles, couplings, and driveshaft assemblies.',                 'sort_order' => 30],
            ['name' => 'Spare Parts',              'slug' => 'spare-parts',              'description' => 'General replacement and maintenance parts across equipment lines.',         'sort_order' => 40],
            ['name' => 'Service Kits',             'slug' => 'service-kits',             'description' => 'Bundled overhaul and service kits for scheduled maintenance intervals.',   'sort_order' => 50],
            ['name' => 'Feed & Drill Systems',     'slug' => 'feed-drill-systems',       'description' => 'Feed beam components, drill heads, and rig-specific wear parts.',         'sort_order' => 60],
            ['name' => 'Electrical & Sensors',     'slug' => 'electrical-sensors',       'description' => 'Sensors, wiring harnesses, switches, and control modules.',               'sort_order' => 70],
            ['name' => 'Structural & Frame Parts', 'slug' => 'structural-frame-parts',   'description' => 'Chassis brackets, mounting hardware, and structural weldments.',          'sort_order' => 80],
            ['name' => 'Filters & Consumables',    'slug' => 'filters-consumables',      'description' => 'Oil, air, hydraulic, and fuel filters plus lubricants.',                  'sort_order' => 90],
            ['name' => 'Seals & Gaskets',          'slug' => 'seals-gaskets',            'description' => 'O-rings, lip seals, shaft seals, and gasket sets.',                       'sort_order' => 100],
            ['name' => 'Equipment',                'slug' => 'equipment',                'description' => 'Complete rigs and machines for exploration and surface support work.',     'sort_order' => 110],
        ];

        foreach ($categories as $cat) {
            // Skip if slug already exists
            $exists = $this->db->table('categories')->where('slug', $cat['slug'])->get()->getFirstRow();
            if ($exists) {
                continue;
            }

            $this->db->table('categories')->insert([
                'name'        => $cat['name'],
                'slug'        => $cat['slug'],
                'description' => $cat['description'],
                'is_active'   => 1,
                'sort_order'  => $cat['sort_order'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo '  ✓ ' . count($categories) . " demo categories seeded.\n";
    }
}
