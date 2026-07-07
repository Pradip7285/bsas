<?php

namespace App\Traits;

use App\Models\CartItemModel;
use App\Models\ProductModel;

/**
 * Storage-aware cart helpers: guests use the PHP session; logged-in customers use
 * the `cart_items` table (so checkout, which requires login, always sees a durable cart).
 * Session -> DB merge happens once, at login (see Customer\AuthController::establishSession()).
 */
trait CartHelpers
{
    private function isCustomerLoggedIn(): bool
    {
        return session()->get('is_customer_authenticated') === true;
    }

    private function currentCustomerId(): ?int
    {
        return $this->isCustomerLoggedIn() ? (int) session()->get('customer_id') : null;
    }

    /** Raw cart as product_id => quantity. */
    private function currentCart(): array
    {
        $customerId = $this->currentCustomerId();

        if ($customerId !== null) {
            $cart = [];
            foreach ((new CartItemModel())->forCustomer($customerId) as $row) {
                $cart[(string) $row['product_id']] = (int) $row['quantity'];
            }

            return $cart;
        }

        return session()->get('cart') ?? [];
    }

    private function cartCount(): int
    {
        return array_sum($this->currentCart());
    }

    private function cartItems(): array
    {
        $cart = $this->currentCart();
        if ($cart === []) {
            return [];
        }

        $products = (new ProductModel())->whereIn('id', array_map('intval', array_keys($cart)))->findAll();
        $indexed  = [];
        foreach ($products as $product) {
            $indexed[(string) $product['id']] = $product;
        }

        $items = [];
        foreach ($cart as $productId => $quantity) {
            if (! isset($indexed[$productId])) {
                continue;
            }

            $items[] = ['product' => $indexed[$productId], 'quantity' => (int) $quantity];
        }

        return $items;
    }

    private function addToCartStorage(int $productId, int $quantity): void
    {
        $customerId = $this->currentCustomerId();
        if ($customerId !== null) {
            (new CartItemModel())->addQuantity($customerId, $productId, $quantity);

            return;
        }

        $cart = session()->get('cart') ?? [];
        $key  = (string) $productId;
        $cart[$key] = ($cart[$key] ?? 0) + $quantity;
        session()->set('cart', $cart);
    }

    private function updateCartStorage(array $quantities): void
    {
        $customerId = $this->currentCustomerId();
        if ($customerId !== null) {
            $model = new CartItemModel();
            foreach ($quantities as $productId => $quantity) {
                $model->setQuantity($customerId, (int) $productId, (int) $quantity);
            }

            return;
        }

        $cart = [];
        foreach ($quantities as $productId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity > 0) {
                $cart[(string) $productId] = $quantity;
            }
        }
        session()->set('cart', $cart);
    }

    private function removeFromCartStorage(int $productId): void
    {
        $customerId = $this->currentCustomerId();
        if ($customerId !== null) {
            (new CartItemModel())->removeProduct($customerId, $productId);

            return;
        }

        $cart = session()->get('cart') ?? [];
        unset($cart[(string) $productId]);
        session()->set('cart', $cart);
    }

    private function clearCartStorage(): void
    {
        $customerId = $this->currentCustomerId();
        if ($customerId !== null) {
            (new CartItemModel())->clearForCustomer($customerId);

            return;
        }

        session()->remove('cart');
    }
}
