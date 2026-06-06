<?php

namespace App\Database\Seeds;

use App\Models\ProductModel;
use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Hydraulic Pump Service Kit',
                'slug' => 'hydraulic-pump-service-kit',
                'sku' => 'BSAS-HP-001',
                'category' => 'Service Kits',
                'short_description' => 'Seal, bearing, and wear-part kit for high-duty hydraulic pump overhauls.',
                'description' => 'Prepared for mining and drilling duty cycles, this kit consolidates the essential overhaul components required during scheduled maintenance windows.',
                'image_url' => '/assets/images/sparePart.webp',
                'price_label' => 'Quote on request',
                'is_active' => 1,
                'sort_order' => 10,
            ],
            [
                'name' => 'Feed Beam Wear Pad Set',
                'slug' => 'feed-beam-wear-pad-set',
                'sku' => 'BSAS-FB-014',
                'category' => 'Spare Parts',
                'short_description' => 'Precision-machined wear pads for drilling rig feed beam stability and service life.',
                'description' => 'Designed to reduce play, improve guidance, and extend maintenance intervals on demanding drill rigs and surface support assets.',
                'image_url' => '/assets/images/mpr-rig.webp',
                'price_label' => 'Fast dispatch',
                'is_active' => 1,
                'sort_order' => 20,
            ],
            [
                'name' => 'Man Portable Exploration Rig',
                'slug' => 'man-portable-exploration-rig',
                'sku' => 'BSAS-MPR-101',
                'category' => 'Equipment',
                'short_description' => 'Compact drilling system built for remote-access exploration campaigns.',
                'description' => 'A modular drilling solution for teams operating in constrained terrain, with configuration support available through the BSAS engineering team.',
                'image_url' => '/assets/images/Store2.webp',
                'price_label' => 'Custom quote',
                'is_active' => 1,
                'sort_order' => 30,
            ],
        ];

        $model = new ProductModel();

        foreach ($products as $product) {
            if (! $model->where('slug', $product['slug'])->first()) {
                $model->insert($product);
            }
        }
    }
}
