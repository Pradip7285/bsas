<?php

namespace App\Database\Seeds;

use App\Models\LabelModel;
use CodeIgniter\Database\Seeder;

class LabelSeeder extends Seeder
{
    public function run()
    {
        $labels = [
            ['name' => 'New Arrival',        'slug' => 'new-arrival',        'sort_order' => 10],
            ['name' => 'Best Seller',        'slug' => 'best-seller',        'sort_order' => 20],
            ['name' => 'Clearance',          'slug' => 'clearance',          'sort_order' => 30],
            ['name' => 'Fast Moving',        'slug' => 'fast-moving',        'sort_order' => 40],
            ['name' => 'Engineered Upgrade', 'slug' => 'engineered-upgrade', 'sort_order' => 50],
        ];

        $model    = new LabelModel();
        $inserted = 0;

        foreach ($labels as $label) {
            if ($model->where('slug', $label['slug'])->first()) {
                continue;
            }

            $model->insert([
                'name'        => $label['name'],
                'slug'        => $label['slug'],
                'description' => null,
                'is_active'   => 1,
                'sort_order'  => $label['sort_order'],
            ]);
            $inserted++;
        }

        // Link a handful of the demo products (product slug => [label slugs...]).
        $links = [
            'hydraulic-pump-service-kit'      => ['best-seller', 'fast-moving'],
            'track-mounted-drill-rig'         => ['new-arrival'],
            'universal-wear-plate-set'        => ['clearance'],
            'turbocharger-assembly'           => ['engineered-upgrade'],
            'drill-head-assembly'             => ['new-arrival', 'engineered-upgrade'],
            'hydraulic-oil-filter-cartridge'  => ['fast-moving', 'best-seller'],
        ];

        $products = $this->db->table('products');
        $pivot    = $this->db->table('product_labels');
        $now      = date('Y-m-d H:i:s');
        $linked   = 0;

        foreach ($links as $productSlug => $labelSlugs) {
            $productRow = $products->where('slug', $productSlug)->get()->getFirstRow('array');
            if (! $productRow) {
                continue;
            }

            foreach ($labelSlugs as $labelSlug) {
                $labelRow = $model->where('slug', $labelSlug)->first();
                if (! $labelRow) {
                    continue;
                }

                $exists = $pivot
                    ->where('product_id', $productRow['id'])
                    ->where('label_id', $labelRow['id'])
                    ->get()
                    ->getFirstRow('array');

                if ($exists) {
                    continue;
                }

                $pivot->insert([
                    'product_id' => $productRow['id'],
                    'label_id'   => $labelRow['id'],
                    'created_at' => $now,
                ]);
                $linked++;
            }
        }

        echo "  ✓ Labels seeded: {$inserted} inserted, {$linked} product links added.\n";
    }
}
