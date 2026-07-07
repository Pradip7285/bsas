<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $orders = [
            [
                'email' => 'ravi.demo@example.com', 'status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'unpaid',
                'days_ago' => 1,
                'items' => [['slug' => 'hydraulic-oil-filter-cartridge', 'qty' => 2], ['slug' => 'o-ring-gasket-kit', 'qty' => 1]],
            ],
            [
                'email' => 'vikram.demo@example.com', 'status' => 'pending', 'payment_method' => 'bank_transfer', 'payment_status' => 'unpaid',
                'days_ago' => 2,
                'items' => [['slug' => 'chassis-mounting-bracket-set', 'qty' => 1], ['slug' => 'hydraulic-oil-filter-cartridge', 'qty' => 5]],
            ],
            [
                'email' => 'ananya.demo@example.com', 'status' => 'confirmed', 'payment_method' => 'bank_transfer', 'payment_status' => 'unpaid',
                'days_ago' => 5,
                'items' => [['slug' => 'hydraulic-cylinder-seal-kit', 'qty' => 1]],
            ],
            [
                'email' => 'vikram.demo@example.com', 'status' => 'processing', 'payment_method' => 'invoice', 'payment_status' => 'unpaid',
                'days_ago' => 8,
                'items' => [['slug' => 'engine-overhaul-service-kit', 'qty' => 1], ['slug' => 'diesel-piston-ring-set', 'qty' => 2]],
            ],
            [
                'email' => 'ravi.demo@example.com', 'status' => 'shipped', 'payment_method' => 'bank_transfer', 'payment_status' => 'paid',
                'days_ago' => 12, 'courier_name' => 'BlueDart', 'tracking_number' => 'BD778812345IN',
                'tracking_url' => 'https://www.bluedart.com/tracking?awb=BD778812345IN',
                'items' => [['slug' => 'gearbox-coupling-assembly', 'qty' => 1]],
            ],
            [
                'email' => 'pradip.paul@mmplmining.com', 'status' => 'delivered', 'payment_method' => 'cod', 'payment_status' => 'paid',
                'days_ago' => 20, 'courier_name' => 'Delhivery', 'tracking_number' => 'DL556677889IN',
                'tracking_url' => 'https://www.delhivery.com/track/package/DL556677889IN',
                'items' => [['slug' => 'hydraulic-pump-service-kit', 'qty' => 3], ['slug' => 'feed-beam-wear-pad-set', 'qty' => 1]],
            ],
            [
                'email' => 'ananya.demo@example.com', 'status' => 'cancelled', 'payment_method' => 'cod', 'payment_status' => 'unpaid',
                'days_ago' => 6, 'admin_note' => 'Customer requested cancellation — duplicate order.',
                'items' => [['slug' => 'turbocharger-assembly', 'qty' => 1]],
            ],
        ];

        $customers = $this->db->table('customers');
        $addresses = $this->db->table('customer_addresses');
        $products  = $this->db->table('products');
        $ordersTbl = $this->db->table('orders');
        $itemsTbl  = $this->db->table('order_items');

        // Timestamp columns stamped progressively as an order advances through its lifecycle.
        $statusProgression = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $statusColumns      = [
            'confirmed'  => 'confirmed_at',
            'processing' => 'processing_at',
            'shipped'    => 'shipped_at',
            'delivered'  => 'delivered_at',
            'cancelled'  => 'cancelled_at',
        ];

        $inserted = 0;

        foreach ($orders as $order) {
            $customer = $customers->where('email', $order['email'])->get()->getFirstRow('array');
            if (! $customer) {
                continue;
            }

            $address = $addresses->where('customer_id', $customer['id'])->get()->getFirstRow('array');
            if (! $address) {
                continue;
            }

            $orderNumber = $this->generateOrderNumber((int) $order['days_ago']);
            if ($ordersTbl->where('order_number', $orderNumber)->countAllResults() > 0) {
                continue; // already seeded on a previous run
            }

            $subtotal = 0.0;
            $taxTotal = 0.0;
            $lineItems = [];

            foreach ($order['items'] as $item) {
                $product = $products->where('slug', $item['slug'])->get()->getFirstRow('array');
                if (! $product) {
                    continue;
                }

                $lineTotal = round((float) $product['price'] * $item['qty'], 2);
                $subtotal += $lineTotal;
                $taxTotal += round($lineTotal * ((float) $product['tax_rate'] / 100), 2);

                $lineItems[] = [
                    'product_id'   => $product['id'],
                    'product_name' => $product['name'],
                    'sku'          => $product['sku'],
                    'part_number'  => $product['part_number'],
                    'unit_price'   => $product['price'],
                    'quantity'     => $item['qty'],
                    'line_total'   => $lineTotal,
                ];
            }

            if ($lineItems === []) {
                continue;
            }

            $createdAt = date('Y-m-d H:i:s', strtotime('-' . $order['days_ago'] . ' days'));
            $now       = date('Y-m-d H:i:s');

            $payload = [
                'order_number'           => $orderNumber,
                'customer_id'            => $customer['id'],
                'status'                 => $order['status'],
                'payment_method'         => $order['payment_method'],
                'payment_status'         => $order['payment_status'],
                'subtotal'               => round($subtotal, 2),
                'tax_total'              => round($taxTotal, 2),
                'shipping_total'         => 0,
                'grand_total'            => round($subtotal + $taxTotal, 2),
                'currency'               => 'INR',
                'shipping_name'          => $address['contact_name'],
                'shipping_phone'         => $address['contact_phone'],
                'shipping_address_line1' => $address['address_line1'],
                'shipping_address_line2' => $address['address_line2'],
                'shipping_city'          => $address['city'],
                'shipping_state'         => $address['state'],
                'shipping_postal_code'   => $address['postal_code'],
                'shipping_country'       => $address['country'],
                'admin_note'             => $order['admin_note'] ?? null,
                'courier_name'           => $order['courier_name'] ?? null,
                'tracking_number'        => $order['tracking_number'] ?? null,
                'tracking_url'           => $order['tracking_url'] ?? null,
                'created_at'             => $createdAt,
                'updated_at'             => $now,
            ];

            // Stamp every lifecycle timestamp up to and including the order's final status,
            // spaced a day apart from creation so the admin/customer timelines look real.
            $targetIndex = array_search($order['status'], $statusProgression, true);
            if ($targetIndex !== false) {
                foreach (array_slice($statusProgression, 1, $targetIndex) as $step => $stepStatus) {
                    $payload[$statusColumns[$stepStatus]] = date('Y-m-d H:i:s', strtotime($createdAt . ' +' . ($step + 1) . ' days'));
                }
            } elseif ($order['status'] === 'cancelled') {
                $payload['cancelled_at'] = date('Y-m-d H:i:s', strtotime($createdAt . ' +1 days'));
            }

            $orderId = $ordersTbl->insert($payload) ? $this->db->insertID() : null;
            if (! $orderId) {
                continue;
            }

            foreach ($lineItems as $lineItem) {
                $itemsTbl->insert(array_merge($lineItem, [
                    'order_id'   => $orderId,
                    'created_at' => $createdAt,
                    'updated_at' => $now,
                ]));
            }

            $inserted++;
        }

        echo "  ✓ Demo orders seeded: {$inserted} inserted.\n";
    }

    /** Deterministic order number per seed row so re-running the seeder is idempotent. */
    private function generateOrderNumber(int $daysAgo): string
    {
        $year = date('Y', strtotime('-' . $daysAgo . ' days'));

        return sprintf('BSAS-%s-DEMO%02d', $year, $daysAgo);
    }
}
