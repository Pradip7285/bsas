<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table         = 'orders';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'order_number',
        'customer_id',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'tax_total',
        'shipping_total',
        'grand_total',
        'currency',
        'shipping_name',
        'shipping_phone',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'customer_note',
        'admin_note',
        'courier_name',
        'tracking_number',
        'tracking_url',
        'confirmed_at',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    /** Maps a status value to the timestamp column stamped when it is first reached. */
    public const STATUS_TIMESTAMP_COLUMNS = [
        'confirmed'  => 'confirmed_at',
        'processing' => 'processing_at',
        'shipped'    => 'shipped_at',
        'delivered'  => 'delivered_at',
        'cancelled'  => 'cancelled_at',
    ];

    public function forCustomer(int $customerId): static
    {
        return $this->where('customer_id', $customerId)->orderBy('created_at', 'DESC');
    }

    public function findByOrderNumber(string $orderNumber): ?array
    {
        return $this->where('order_number', $orderNumber)->first();
    }

    /** Generates a unique order number of the form BSAS-{YYYY}-{NNNNNN}. */
    public function generateOrderNumber(): string
    {
        $year = date('Y');

        do {
            $candidate = sprintf('BSAS-%s-%06d', $year, random_int(1, 999999));
        } while ($this->where('order_number', $candidate)->countAllResults() > 0);

        return $candidate;
    }

    /**
     * Move an order to a new status, stamping the corresponding timestamp column
     * only if it has not already been set (a status is never re-stamped).
     */
    public function transitionStatus(int $orderId, string $newStatus): void
    {
        $order = $this->find($orderId);
        if (! $order) {
            return;
        }

        $payload = ['status' => $newStatus];
        $column  = self::STATUS_TIMESTAMP_COLUMNS[$newStatus] ?? null;

        if ($column !== null && empty($order[$column])) {
            $payload[$column] = date('Y-m-d H:i:s');
        }

        $this->update($orderId, $payload);
    }
}
