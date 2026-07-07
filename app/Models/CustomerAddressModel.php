<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerAddressModel extends Model
{
    protected $table         = 'customer_addresses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_id',
        'label',
        'contact_name',
        'contact_phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default_shipping',
        'is_default_billing',
    ];

    public function forCustomer(int $customerId): static
    {
        return $this->where('customer_id', $customerId)->orderBy('id', 'DESC');
    }
}
