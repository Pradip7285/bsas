<?php

namespace App\Controllers;

use App\Libraries\Notifier;
use App\Models\CustomerAddressModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Traits\CartHelpers;
use App\Traits\RendersStorefrontPages;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class OrderController extends Controller
{
    use CartHelpers;
    use RendersStorefrontPages;

    private const PAYMENT_METHODS = ['bank_transfer', 'cod', 'invoice'];

    public function checkoutAddress()
    {
        $items = $this->orderableCartItems();
        if ($items === []) {
            return redirect()->to('/cart')->with('error', 'Add at least one orderable product to your basket before checking out.');
        }

        $addresses = (new CustomerAddressModel())->forCustomer($this->customerId())->findAll();

        return $this->page('checkout/address', 'Checkout — Shipping Address', [
            'addresses' => $addresses,
        ]);
    }

    public function checkoutSaveAddress()
    {
        $selectedId = (int) $this->request->getPost('address_id');

        if ($selectedId > 0) {
            $address = (new CustomerAddressModel())->where('customer_id', $this->customerId())->find($selectedId);
            if (! $address) {
                return redirect()->to('/checkout/address')->with('errors', ['Select a valid address.']);
            }

            session()->set('checkout_address_id', $selectedId);

            return redirect()->to('/checkout/review');
        }

        $rules = [
            'contact_name'  => 'required|min_length[2]|max_length[160]',
            'contact_phone' => 'required|min_length[8]|max_length[20]',
            'address_line1' => 'required|max_length[255]',
            'city'          => 'required|max_length[100]',
            'state'         => 'required|max_length[100]',
            'postal_code'   => 'required|max_length[12]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/checkout/address')->withInput()->with('errors', $this->validator->getErrors());
        }

        $addressId = (new CustomerAddressModel())->insert([
            'customer_id'   => $this->customerId(),
            'label'         => trim((string) $this->request->getPost('label')) ?: 'Delivery Address',
            'contact_name'  => trim((string) $this->request->getPost('contact_name')),
            'contact_phone' => trim((string) $this->request->getPost('contact_phone')),
            'address_line1' => trim((string) $this->request->getPost('address_line1')),
            'address_line2' => trim((string) $this->request->getPost('address_line2')) ?: null,
            'city'          => trim((string) $this->request->getPost('city')),
            'state'         => trim((string) $this->request->getPost('state')),
            'postal_code'   => trim((string) $this->request->getPost('postal_code')),
            'country'       => 'IN',
        ], true);

        session()->set('checkout_address_id', (int) $addressId);

        return redirect()->to('/checkout/review');
    }

    public function checkoutReview()
    {
        $items = $this->orderableCartItems();
        if ($items === []) {
            return redirect()->to('/cart')->with('error', 'Your basket has no orderable items.');
        }

        $address = $this->selectedAddress();
        if (! $address) {
            return redirect()->to('/checkout/address')->with('errors', ['Please choose a shipping address first.']);
        }

        $totals = $this->computeTotals($items);

        return $this->page('checkout/review', 'Checkout — Review Order', [
            'items'            => $items,
            'shippingAddress'  => $address,
            'totals'           => $totals,
        ]);
    }

    public function placeOrder()
    {
        $items = $this->orderableCartItems();
        if ($items === []) {
            return redirect()->to('/cart')->with('error', 'Your basket has no orderable items.');
        }

        $address = $this->selectedAddress();
        if (! $address) {
            return redirect()->to('/checkout/address')->with('errors', ['Please choose a shipping address first.']);
        }

        $paymentMethod = (string) $this->request->getPost('payment_method');
        if (! in_array($paymentMethod, self::PAYMENT_METHODS, true)) {
            return redirect()->to('/checkout/review')->with('errors', ['Please select a valid payment method.']);
        }

        $totals   = $this->computeTotals($items);
        $orderModel = new OrderModel();
        $itemModel  = new OrderItemModel();
        $productModel = new ProductModel();

        $db = db_connect();
        $db->transStart();

        $orderId = $orderModel->insert([
            'order_number'            => $orderModel->generateOrderNumber(),
            'customer_id'             => $this->customerId(),
            'status'                  => 'pending',
            'payment_method'          => $paymentMethod,
            'payment_status'          => 'unpaid',
            'subtotal'                => $totals['subtotal'],
            'tax_total'               => $totals['tax_total'],
            'shipping_total'          => 0,
            'grand_total'             => $totals['grand_total'],
            'currency'                => $totals['currency'],
            'shipping_name'           => $address['contact_name'],
            'shipping_phone'          => $address['contact_phone'],
            'shipping_address_line1'  => $address['address_line1'],
            'shipping_address_line2'  => $address['address_line2'],
            'shipping_city'           => $address['city'],
            'shipping_state'          => $address['state'],
            'shipping_postal_code'    => $address['postal_code'],
            'shipping_country'        => $address['country'],
            'customer_note'           => trim((string) $this->request->getPost('customer_note')) ?: null,
        ], true);

        foreach ($items as $item) {
            $product = $item['product'];
            $qty     = $item['quantity'];

            if (! $productModel->decrementStock((int) $product['id'], $qty)) {
                $db->transRollback();

                return redirect()->to('/checkout/review')->with('errors', [
                    'Insufficient stock for "' . $product['name'] . '". Please adjust the quantity in your basket.',
                ]);
            }

            $itemModel->insert([
                'order_id'     => $orderId,
                'product_id'   => $product['id'],
                'product_name' => $product['name'],
                'sku'          => $product['sku'],
                'part_number'  => $product['part_number'] ?? null,
                'unit_price'   => $product['price'],
                'quantity'     => $qty,
                'line_total'   => round((float) $product['price'] * $qty, 2),
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/checkout/review')->with('errors', ['Could not place your order right now. Please try again.']);
        }

        $this->clearCartStorage();
        $order = $orderModel->find($orderId);
        $this->sendOrderEmails($order, $items);

        return redirect()->to('/checkout/confirmation/' . $order['order_number']);
    }

    public function orderConfirmation(string $orderNumber)
    {
        $order = (new OrderModel())->where('customer_id', $this->customerId())->findByOrderNumber($orderNumber);
        if (! $order) {
            throw PageNotFoundException::forPageNotFound();
        }

        $items = (new OrderItemModel())->forOrder((int) $order['id']);

        return $this->page('checkout/confirmation', 'Order Confirmed', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    private function customerId(): int
    {
        return (int) session()->get('customer_id');
    }

    private function selectedAddress(): ?array
    {
        $addressId = (int) session()->get('checkout_address_id');
        if ($addressId <= 0) {
            return null;
        }

        return (new CustomerAddressModel())->where('customer_id', $this->customerId())->find($addressId);
    }

    /** Cart items filtered to those with a real price (quote-only products can't be checked out). */
    private function orderableCartItems(): array
    {
        return array_values(array_filter(
            $this->cartItems(),
            static fn(array $item): bool => (float) ($item['product']['price'] ?? 0) > 0
        ));
    }

    private function computeTotals(array $items): array
    {
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $currency = 'INR';

        foreach ($items as $item) {
            $product  = $item['product'];
            $lineTotal = (float) $product['price'] * $item['quantity'];
            $subtotal += $lineTotal;
            $taxTotal += $lineTotal * ((float) ($product['tax_rate'] ?? 0) / 100);
            $currency  = $product['currency'] ?? $currency;
        }

        return [
            'subtotal'    => round($subtotal, 2),
            'tax_total'   => round($taxTotal, 2),
            'grand_total' => round($subtotal + $taxTotal, 2),
            'currency'    => $currency,
        ];
    }

    private function sendOrderEmails(array $order, array $items): void
    {
        $lines = array_map(
            static fn(array $item): string => sprintf('%s x %d — %s', $item['product']['name'], $item['quantity'], $item['product']['sku'] ?: 'no SKU'),
            $items
        );

        $body = "Order {$order['order_number']}\n\n" . implode("\n", $lines)
            . "\n\nGrand Total: {$order['currency']} " . number_format((float) $order['grand_total'], 2)
            . "\nPayment Method: {$order['payment_method']}"
            . "\n\nShip to:\n{$order['shipping_name']}\n{$order['shipping_address_line1']}\n{$order['shipping_city']}, {$order['shipping_state']} {$order['shipping_postal_code']}";

        Notifier::sendCustomerEmail(
            (string) session()->get('customer_email'),
            'Your BSAS order ' . $order['order_number'] . ' has been received',
            "Thank you for your order.\n\n" . $body
        );

        Notifier::sendLeadsEmail('New order ' . $order['order_number'], $body);
    }
}
