<?php

namespace App\Models;

use CodeIgniter\Model;

class CartItemModel extends Model
{
    protected $table         = 'cart_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_id',
        'product_id',
        'quantity',
    ];

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)->findAll();
    }

    /** Add to (or increase) a line item's quantity for this customer. */
    public function addQuantity(int $customerId, int $productId, int $quantity): void
    {
        $existing = $this->where('customer_id', $customerId)->where('product_id', $productId)->first();

        if ($existing) {
            $this->update($existing['id'], ['quantity' => (int) $existing['quantity'] + $quantity]);

            return;
        }

        $this->insert(['customer_id' => $customerId, 'product_id' => $productId, 'quantity' => max(1, $quantity)]);
    }

    /** Merge a guest session cart (product_id => qty) into this customer's DB cart. */
    public function mergeSessionCart(int $customerId, array $sessionCart): void
    {
        foreach ($sessionCart as $productId => $quantity) {
            $this->addQuantity($customerId, (int) $productId, (int) $quantity);
        }
    }

    public function setQuantity(int $customerId, int $productId, int $quantity): void
    {
        $existing = $this->where('customer_id', $customerId)->where('product_id', $productId)->first();
        if (! $existing) {
            return;
        }

        if ($quantity <= 0) {
            $this->delete($existing['id']);

            return;
        }

        $this->update($existing['id'], ['quantity' => $quantity]);
    }

    public function removeProduct(int $customerId, int $productId): void
    {
        $this->where('customer_id', $customerId)->where('product_id', $productId)->delete();
    }

    public function clearForCustomer(int $customerId): void
    {
        $this->where('customer_id', $customerId)->delete();
    }
}
