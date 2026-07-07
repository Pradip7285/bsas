<?php

namespace App\Database\Seeds;

use App\Models\ProductModel;
use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            // --- Existing 3 products, backfilled with full pricing/technical/SEO data ---
            [
                'name' => 'Hydraulic Pump Service Kit', 'slug' => 'hydraulic-pump-service-kit', 'sku' => 'BSAS-HP-001',
                'part_number' => 'HP-KIT-001', 'category' => 'Hydraulic Systems',
                'short_description' => 'Seal, bearing, and wear-part kit for high-duty hydraulic pump overhauls.',
                'description' => 'Prepared for mining and drilling duty cycles, this kit consolidates the essential overhaul components required during scheduled maintenance windows.',
                'image_url' => '/assets/images/sparePart.webp', 'price_label' => 'Quote on request',
                'price' => 18500, 'compare_at_price' => 21000, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 25, 'lead_time' => '3-5 business days', 'min_order_qty' => 1,
                'weight' => '4.2 kg', 'dimensions' => '35 x 20 x 15 cm', 'material' => 'Steel / Nitrile seals',
                'specifications' => ['Seal material' => 'Nitrile', 'Bearing type' => 'Roller', 'Duty rating' => 'Heavy'],
                'is_featured' => 1, 'sort_order' => 10,
            ],
            [
                'name' => 'Feed Beam Wear Pad Set', 'slug' => 'feed-beam-wear-pad-set', 'sku' => 'BSAS-FB-014',
                'part_number' => 'FB-PAD-014', 'category' => 'Spare Parts',
                'short_description' => 'Precision-machined wear pads for drilling rig feed beam stability and service life.',
                'description' => 'Designed to reduce play, improve guidance, and extend maintenance intervals on demanding drill rigs and surface support assets.',
                'image_url' => '/assets/images/mpr-rig.webp', 'price_label' => 'Fast dispatch',
                'price' => 6200, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 60, 'lead_time' => '1-2 business days', 'min_order_qty' => 2,
                'weight' => '1.1 kg', 'dimensions' => '18 x 10 x 4 cm', 'material' => 'Hardened steel',
                'specifications' => ['Pad thickness' => '12mm', 'Finish' => 'Hardened'],
                'is_featured' => 0, 'sort_order' => 20,
            ],
            [
                'name' => 'Man Portable Exploration Rig', 'slug' => 'man-portable-exploration-rig', 'sku' => 'BSAS-MPR-101',
                'part_number' => 'MPR-RIG-101', 'category' => 'Equipment',
                'short_description' => 'Compact drilling system built for remote-access exploration campaigns.',
                'description' => 'A modular drilling solution for teams operating in constrained terrain, with configuration support available through the BSAS engineering team.',
                'image_url' => '/assets/images/Store2.webp', 'price_label' => 'Custom quote',
                'price' => 385000, 'compare_at_price' => 420000, 'tax_rate' => 18,
                'stock_status' => 'made_to_order', 'stock_quantity' => 0, 'lead_time' => '4-6 weeks', 'min_order_qty' => 1,
                'weight' => '68 kg', 'dimensions' => '120 x 60 x 90 cm', 'material' => 'Aluminium / Steel frame',
                'specifications' => ['Drilling depth' => '30m', 'Power source' => 'Petrol/Electric', 'Portability' => '2-person carry'],
                'is_featured' => 1, 'sort_order' => 30,
            ],

            // --- New demo products, spread across every category ---
            [
                'name' => 'Hydraulic Cylinder Seal Kit', 'slug' => 'hydraulic-cylinder-seal-kit', 'sku' => 'BSAS-HP-022',
                'part_number' => 'HP-SEAL-022', 'category' => 'Hydraulic Systems',
                'short_description' => 'Precision U-cup and wiper seal set for mid-range hydraulic cylinder rebuilds.',
                'description' => 'Covers the most common bore sizes used on mining and drilling cylinders, with PTFE-backed seals for extended service intervals.',
                'image_url' => '/assets/images/sparePart.webp', 'price_label' => 'In stock',
                'price' => 6800, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 40, 'lead_time' => '2-4 business days', 'min_order_qty' => 2,
                'weight' => '0.8 kg', 'dimensions' => '20 x 15 x 8 cm', 'material' => 'Nitrile / PTFE',
                'specifications' => ['Bore size' => '80-120mm', 'Seal type' => 'U-cup'],
                'is_featured' => 0, 'sort_order' => 40,
            ],
            [
                'name' => 'Universal Wear Plate Set', 'slug' => 'universal-wear-plate-set', 'sku' => 'BSAS-SP-030',
                'part_number' => 'SP-WPS-030', 'category' => 'Spare Parts',
                'short_description' => 'Abrasion-resistant wear plates for bucket, chute, and hopper protection.',
                'description' => 'Bolt-on wear plates that extend structural life in high-abrasion material handling zones. Drilled to a universal bolt pattern.',
                'image_url' => '/assets/images/SpareParts.png', 'price_label' => 'Made to order',
                'price' => 3400, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'made_to_order', 'stock_quantity' => 0, 'lead_time' => '7-10 business days', 'min_order_qty' => 4,
                'weight' => '2.0 kg', 'dimensions' => '30 x 20 x 3 cm', 'material' => 'Abrasion-resistant steel',
                'specifications' => ['Hardness' => '400 HB', 'Bolt pattern' => 'Universal 4-hole'],
                'is_featured' => 0, 'sort_order' => 50,
            ],
            [
                'name' => 'Track-Mounted Drill Rig', 'slug' => 'track-mounted-drill-rig', 'sku' => 'BSAS-EQ-205',
                'part_number' => 'EQ-TMR-205', 'category' => 'Equipment',
                'short_description' => 'Self-propelled track drill rig for surface exploration and geotechnical drilling.',
                'description' => 'A full-size track-mounted rig for production drilling programs, built for reliability across long field deployments with remote support available.',
                'image_url' => '/assets/images/Store2.webp', 'price_label' => 'Custom quote',
                'price' => 1250000, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'out_of_stock', 'stock_quantity' => 0, 'lead_time' => '8-10 weeks', 'min_order_qty' => 1,
                'weight' => '3200 kg', 'dimensions' => '450 x 220 x 260 cm', 'material' => 'Steel chassis',
                'specifications' => ['Track type' => 'Rubber', 'Max depth' => '150m', 'Engine' => 'Diesel 130hp'],
                'is_featured' => 1, 'sort_order' => 60,
            ],
            [
                'name' => 'Engine Overhaul Service Kit', 'slug' => 'engine-overhaul-service-kit', 'sku' => 'BSAS-SK-045',
                'part_number' => 'SK-ENG-045', 'category' => 'Service Kits',
                'short_description' => 'Bundled gasket, seal, and bearing kit for scheduled diesel engine overhauls.',
                'description' => 'A consolidated parts kit matched to standard overhaul intervals, reducing procurement time for planned maintenance shutdowns.',
                'image_url' => '/assets/images/sparePart.webp', 'price_label' => 'In stock',
                'price' => 22800, 'compare_at_price' => 25000, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 12, 'lead_time' => '5-7 business days', 'min_order_qty' => 1,
                'weight' => '12 kg', 'dimensions' => '50 x 40 x 30 cm', 'material' => 'Mixed OEM components',
                'specifications' => ['Kit coverage' => 'Top-end overhaul', 'Engine class' => 'Diesel 4-6 cylinder'],
                'is_featured' => 1, 'sort_order' => 70,
            ],
            [
                'name' => 'Diesel Piston & Ring Set', 'slug' => 'diesel-piston-ring-set', 'sku' => 'BSAS-EC-060',
                'part_number' => 'EC-PRS-060', 'category' => 'Engine Components',
                'short_description' => 'Forged piston and chrome ring set for heavy-duty diesel engine rebuilds.',
                'description' => 'Matched piston and ring sets built to OEM tolerances for consistent compression and reduced oil consumption after rebuild.',
                'image_url' => '/assets/images/photo1.webp', 'price_label' => 'In stock',
                'price' => 9800, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 30, 'lead_time' => '3-5 business days', 'min_order_qty' => 1,
                'weight' => '3.4 kg', 'dimensions' => '', 'material' => 'Forged steel / Chrome rings',
                'specifications' => ['Ring count' => '3 per piston', 'Bore class' => 'Standard'],
                'is_featured' => 0, 'sort_order' => 80,
            ],
            [
                'name' => 'Turbocharger Assembly', 'slug' => 'turbocharger-assembly', 'sku' => 'BSAS-EC-072',
                'part_number' => 'EC-TC-072', 'category' => 'Engine Components',
                'short_description' => 'Remanufactured turbocharger assembly for diesel engines used in drilling and haulage fleets.',
                'description' => 'Fully tested and balanced turbocharger unit built to restore factory-rated boost pressure and throttle response.',
                'image_url' => '/assets/images/photo2.webp', 'price_label' => 'Custom quote',
                'price' => 48500, 'compare_at_price' => 54000, 'tax_rate' => 18,
                'stock_status' => 'made_to_order', 'stock_quantity' => 0, 'lead_time' => '3-4 weeks', 'min_order_qty' => 1,
                'weight' => '9.6 kg', 'dimensions' => '', 'material' => 'Cast iron / Aluminium',
                'specifications' => ['Max boost' => '2.1 bar', 'Balanced' => 'Yes'],
                'is_featured' => 1, 'sort_order' => 90,
            ],
            [
                'name' => 'Heavy-Duty Drive Axle', 'slug' => 'heavy-duty-drive-axle', 'sku' => 'BSAS-TD-088',
                'part_number' => 'TD-AXL-088', 'category' => 'Transmission & Driveline',
                'short_description' => 'Reinforced drive axle assembly rated for continuous heavy-haulage duty.',
                'description' => 'Forged-steel drive axle designed for high-torque haulage applications, with extended service intervals under continuous load.',
                'image_url' => '/assets/images/mpr-rig.webp', 'price_label' => 'Quote on request',
                'price' => 68000, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 6, 'lead_time' => '2-3 weeks', 'min_order_qty' => 1,
                'weight' => '145 kg', 'dimensions' => '', 'material' => 'Forged steel',
                'specifications' => ['Rated torque' => '4200 Nm', 'Duty' => 'Continuous'],
                'is_featured' => 0, 'sort_order' => 100,
            ],
            [
                'name' => 'Gearbox Coupling Assembly', 'slug' => 'gearbox-coupling-assembly', 'sku' => 'BSAS-TD-093',
                'part_number' => 'TD-GCA-093', 'category' => 'Transmission & Driveline',
                'short_description' => 'Flexible coupling assembly for gearbox-to-driveline torque transfer.',
                'description' => 'Alloy-steel coupling with elastomeric damping element, reducing shock load transmission between gearbox and driveline.',
                'image_url' => '/assets/images/SpareParts.png', 'price_label' => 'In stock',
                'price' => 15400, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 18, 'lead_time' => '3-5 business days', 'min_order_qty' => 1,
                'weight' => '6.2 kg', 'dimensions' => '', 'material' => 'Alloy steel',
                'specifications' => ['Damping element' => 'Elastomeric', 'Max RPM' => '3200'],
                'is_featured' => 0, 'sort_order' => 110,
            ],
            [
                'name' => 'Drill Head Assembly', 'slug' => 'drill-head-assembly', 'sku' => 'BSAS-FD-110',
                'part_number' => 'FD-DHA-110', 'category' => 'Feed & Drill Systems',
                'short_description' => 'Complete drill head assembly for rotary-percussive drilling rigs.',
                'description' => 'Tool-steel drill head engineered for high cycling rates in rotary-percussive drilling, with field-serviceable wear components.',
                'image_url' => '/assets/images/photo1.webp', 'price_label' => 'Custom quote',
                'price' => 92500, 'compare_at_price' => 99000, 'tax_rate' => 18,
                'stock_status' => 'made_to_order', 'stock_quantity' => 0, 'lead_time' => '4-5 weeks', 'min_order_qty' => 1,
                'weight' => '38 kg', 'dimensions' => '', 'material' => 'Tool steel',
                'specifications' => ['Drive type' => 'Rotary-percussive', 'Max RPM' => '180'],
                'is_featured' => 1, 'sort_order' => 120,
            ],
            [
                'name' => 'Pressure Sensor Module', 'slug' => 'pressure-sensor-module', 'sku' => 'BSAS-ES-014',
                'part_number' => 'ES-PSM-014', 'category' => 'Electrical & Sensors',
                'short_description' => 'IP67-rated pressure sensor module for hydraulic and pneumatic circuit monitoring.',
                'description' => 'Compact sensor module with standard wiring harness compatibility, suited for retrofit and OEM installation alike.',
                'image_url' => '/assets/images/photo2.webp', 'price_label' => 'In stock',
                'price' => 3200, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 50, 'lead_time' => '1-2 business days', 'min_order_qty' => 2,
                'weight' => '0.2 kg', 'dimensions' => '', 'material' => 'IP67 housing',
                'specifications' => ['Range' => '0-400 bar', 'Output' => '4-20mA'],
                'is_featured' => 0, 'sort_order' => 130,
            ],
            [
                'name' => 'Chassis Mounting Bracket Set', 'slug' => 'chassis-mounting-bracket-set', 'sku' => 'BSAS-SF-021',
                'part_number' => 'SF-CMB-021', 'category' => 'Structural & Frame Parts',
                'short_description' => 'Powder-coated mounting bracket set for auxiliary equipment chassis fitment.',
                'description' => 'Structural bracket set designed to distribute mounting loads evenly across the chassis rail, reducing fatigue cracking risk.',
                'image_url' => '/assets/images/SpareParts.png', 'price_label' => 'In stock',
                'price' => 5100, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 22, 'lead_time' => '2-4 business days', 'min_order_qty' => 1,
                'weight' => '4.5 kg', 'dimensions' => '', 'material' => 'Powder-coated steel',
                'specifications' => ['Load rating' => '850 kg', 'Finish' => 'Powder-coated'],
                'is_featured' => 0, 'sort_order' => 140,
            ],
            [
                'name' => 'Hydraulic Oil Filter Cartridge', 'slug' => 'hydraulic-oil-filter-cartridge', 'sku' => 'BSAS-FC-005',
                'part_number' => 'FC-HOF-005', 'category' => 'Filters & Consumables',
                'short_description' => 'High-efficiency cellulose filter cartridge for hydraulic reservoir return lines.',
                'description' => 'Rated for fine particulate capture on return-line filtration circuits, extending hydraulic fluid and component service life.',
                'image_url' => '/assets/images/sparePart.webp', 'price_label' => 'In stock',
                'price' => 950, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 120, 'lead_time' => '1-2 business days', 'min_order_qty' => 5,
                'weight' => '0.4 kg', 'dimensions' => '', 'material' => 'Cellulose media',
                'specifications' => ['Micron rating' => '10 micron', 'Flow rate' => '150 L/min'],
                'is_featured' => 0, 'sort_order' => 150,
            ],
            [
                'name' => 'O-Ring & Gasket Kit', 'slug' => 'o-ring-gasket-kit', 'sku' => 'BSAS-SG-033',
                'part_number' => 'SG-ORK-033', 'category' => 'Seals & Gaskets',
                'short_description' => 'Assorted O-ring and gasket kit covering common hydraulic and engine fitment sizes.',
                'description' => 'A broad assortment kit kept on hand for fast-turnaround field repairs, covering the most requested seal sizes across the product range.',
                'image_url' => '/assets/images/mpr-rig.webp', 'price_label' => 'In stock',
                'price' => 1450, 'compare_at_price' => null, 'tax_rate' => 18,
                'stock_status' => 'in_stock', 'stock_quantity' => 80, 'lead_time' => '1-2 business days', 'min_order_qty' => 3,
                'weight' => '0.5 kg', 'dimensions' => '', 'material' => 'Nitrile / EPDM',
                'specifications' => ['Piece count' => '225', 'Sizes' => 'Assorted metric'],
                'is_featured' => 0, 'sort_order' => 160,
            ],
        ];

        $categoryModel = $this->db->table('categories');
        $model         = new ProductModel();
        $inserted      = 0;
        $updated       = 0;

        foreach ($products as $product) {
            $categoryRow = $categoryModel->where('name', $product['category'])->get()->getFirstRow('array');
            $categoryId  = $categoryRow['id'] ?? null;

            $specs = isset($product['specifications']) && $product['specifications'] !== []
                ? json_encode($product['specifications'])
                : null;

            $seoTitle = $product['name'] . ' | BSAS';
            $seoDesc  = mb_strimwidth($product['short_description'], 0, 300, '');

            $payload = [
                'name'              => $product['name'],
                'slug'              => $product['slug'],
                'sku'               => $product['sku'],
                'part_number'       => $product['part_number'],
                'category'          => $product['category'],
                'category_id'       => $categoryId,
                'short_description' => $product['short_description'],
                'description'       => $product['description'],
                'image_url'         => $product['image_url'],
                'price_label'       => $product['price_label'],
                'is_active'         => 1,
                'sort_order'        => $product['sort_order'],
                'price'             => $product['price'],
                'compare_at_price'  => $product['compare_at_price'] ?? null,
                'currency'          => 'INR',
                'tax_rate'          => $product['tax_rate'],
                'stock_status'      => $product['stock_status'],
                'stock_quantity'    => $product['stock_quantity'],
                'lead_time'         => $product['lead_time'],
                'min_order_qty'     => $product['min_order_qty'],
                'weight'            => $product['weight'],
                'dimensions'        => $product['dimensions'] ?: null,
                'material'          => $product['material'],
                'datasheet_url'     => null,
                'specifications'    => $specs,
                'is_featured'       => $product['is_featured'],
                'meta_title'        => $seoTitle,
                'meta_description'  => $seoDesc,
                'meta_keyword'      => strtolower($product['name']),
                'focus_keyword'     => strtolower($product['name']),
                'image_alt_text'    => $product['name'] . ' product photo',
                'canonical_url'     => 'https://example.com/products/' . $product['slug'],
                'og_image'          => 'https://cdn.example.com/products/' . $product['slug'] . '.webp',
                'structured_data_type' => 'Product',
                'robots_meta'       => 'index, follow',
            ];

            $existing = $model->where('slug', $product['slug'])->first();
            if ($existing) {
                $model->update($existing['id'], $payload);
                $updated++;
            } else {
                $model->insert($payload);
                $inserted++;
            }
        }

        echo "  ✓ Products seeded: {$inserted} inserted, {$updated} updated.\n";
    }
}
