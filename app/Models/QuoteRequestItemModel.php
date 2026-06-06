<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteRequestItemModel extends Model
{
    protected $table         = 'quote_request_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'quote_request_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
    ];
}
