<?php

namespace App\Database\Seeds;

use App\Models\CustomerAddressModel;
use App\Models\CustomerModel;
use CodeIgniter\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /** Shared demo login password for every seeded customer below. */
    public const DEMO_PASSWORD = 'Demo@1234';

    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $customers = [
            [
                'name' => 'Ravi Shankar', 'email' => 'ravi.demo@example.com', 'phone' => '9800000001',
                'address' => [
                    'label' => 'Head Office', 'contact_name' => 'Ravi Shankar', 'contact_phone' => '9800000001',
                    'address_line1' => '14 Diamond Harbour Road', 'address_line2' => 'Near Behala Chowrasta',
                    'city' => 'Kolkata', 'state' => 'West Bengal', 'postal_code' => '700034',
                ],
            ],
            [
                'name' => 'Ananya Sen', 'email' => 'ananya.demo@example.com', 'phone' => '9800000002',
                'address' => [
                    'label' => 'Warehouse', 'contact_name' => 'Ananya Sen', 'contact_phone' => '9800000002',
                    'address_line1' => '221 Whitefield Industrial Layout', 'address_line2' => '',
                    'city' => 'Bengaluru', 'state' => 'Karnataka', 'postal_code' => '560066',
                ],
            ],
            [
                'name' => 'Vikram Rao', 'email' => 'vikram.demo@example.com', 'phone' => '9800000003',
                'address' => [
                    'label' => 'Site Office', 'contact_name' => 'Vikram Rao', 'contact_phone' => '9800000003',
                    'address_line1' => '77 MIDC Industrial Area', 'address_line2' => 'Butibori',
                    'city' => 'Nagpur', 'state' => 'Maharashtra', 'postal_code' => '441122',
                ],
            ],
        ];

        $customerModel = new CustomerModel();
        $addressModel  = new CustomerAddressModel();
        $inserted      = 0;

        foreach ($customers as $customer) {
            $existing = $customerModel->findByEmail($customer['email']);
            if ($existing) {
                continue;
            }

            $customerId = $customerModel->insert([
                'name'              => $customer['name'],
                'email'             => $customer['email'],
                'password_hash'     => password_hash(self::DEMO_PASSWORD, PASSWORD_DEFAULT),
                'phone'             => $customer['phone'],
                'email_verified_at' => $now,
                'phone_verified_at' => $now,
                'is_active'         => 1,
            ], true);

            $addressModel->insert(array_merge($customer['address'], [
                'customer_id'         => $customerId,
                'country'             => 'IN',
                'is_default_shipping' => 1,
                'is_default_billing'  => 1,
            ]));

            $inserted++;
        }

        // Give the real signed-up account a saved address too, so checkout/order
        // history can be exercised on it without re-entering an address each time.
        $realCustomer = $customerModel->findByEmail('pradip.paul@mmplmining.com');
        if ($realCustomer && ! $addressModel->forCustomer((int) $realCustomer['id'])->first()) {
            $addressModel->insert([
                'customer_id'         => $realCustomer['id'],
                'label'               => 'Head Office',
                'contact_name'        => $realCustomer['name'],
                'contact_phone'       => $realCustomer['phone'] ?: '9800000000',
                'address_line1'       => 'MMPL Mining House, Sector V',
                'address_line2'       => '',
                'city'                => 'Kolkata',
                'state'               => 'West Bengal',
                'postal_code'         => '700091',
                'country'             => 'IN',
                'is_default_shipping' => 1,
                'is_default_billing'  => 1,
            ]);
        }

        echo "  ✓ Demo customers seeded: {$inserted} inserted (password: " . self::DEMO_PASSWORD . ").\n";
    }
}
