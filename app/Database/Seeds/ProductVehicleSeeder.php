<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductVehicleSeeder extends Seeder
{
    public function run()
    {
        // product slug => [vehicle slugs...]
        $links = [
            'hydraulic-pump-service-kit'  => ['mpr100', 'hp-500-series-pump', 'hp-750-series-pump'],
            'hydraulic-cylinder-seal-kit' => ['hp-500-series-pump', 'hp-750-series-pump'],
            'man-portable-exploration-rig' => ['mpr100', 'mpr-200-exploration-rig'],
            'track-mounted-drill-rig'     => ['track-rig-300', 'mpr-200-exploration-rig'],
            'drill-head-assembly'         => ['rig-alpha-feed-system', 'track-rig-300'],
            'turbocharger-assembly'       => ['generator-set-ec-200'],
            'diesel-piston-ring-set'      => ['generator-set-ec-200'],
            'heavy-duty-drive-axle'       => ['excavator-dl-320'],
            'gearbox-coupling-assembly'   => ['excavator-dl-320', 'generator-set-ec-200'],
        ];

        $products = $this->db->table('products');
        $vehicles = $this->db->table('vehicles');
        $pivot    = $this->db->table('product_vehicles');
        $now      = date('Y-m-d H:i:s');
        $inserted = 0;

        foreach ($links as $productSlug => $vehicleSlugs) {
            $productRow = $products->where('slug', $productSlug)->get()->getFirstRow('array');
            if (! $productRow) {
                continue;
            }

            foreach ($vehicleSlugs as $vehicleSlug) {
                $vehicleRow = $vehicles->where('slug', $vehicleSlug)->get()->getFirstRow('array');
                if (! $vehicleRow) {
                    continue;
                }

                $exists = $pivot
                    ->where('product_id', $productRow['id'])
                    ->where('vehicle_id', $vehicleRow['id'])
                    ->get()
                    ->getFirstRow('array');

                if ($exists) {
                    continue;
                }

                $pivot->insert([
                    'product_id' => $productRow['id'],
                    'vehicle_id' => $vehicleRow['id'],
                    'created_at' => $now,
                ]);
                $inserted++;
            }
        }

        echo "  ✓ Product-vehicle links seeded: {$inserted} inserted.\n";
    }
}
