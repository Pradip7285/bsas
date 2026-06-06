<?php

namespace App\Models;

use CodeIgniter\Model;

class BrochureLeadModel extends Model
{
    protected $table         = 'brochure_leads';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'mobile',
        'source',
        'ip_address',
        'user_agent',
    ];
}
