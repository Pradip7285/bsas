<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteRequestModel extends Model
{
    protected $table         = 'quote_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'request_type',
        'name',
        'company',
        'designation',
        'email',
        'phone',
        'concern',
        'source_page',
        'message',
    ];
}
