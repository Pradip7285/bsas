<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeds a full demo dataset in dependency order: categories/vehicles the
 * products reference, then products, then the product-vehicle links, then
 * demo customers/addresses, then orders that reference all of the above.
 * Every step is idempotent (matches by slug/email), so re-running is safe.
 */
class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $this->call(CategorySeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(VehicleSeeder::class);
        $this->call(OemSeeder::class);
        $this->call(ProductVehicleSeeder::class);
        $this->call(LabelSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
