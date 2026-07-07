<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table         = 'customers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'email',
        'password_hash',
        'phone',
        'google_id',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
        'last_login_at',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?array
    {
        return $this->where('phone', $phone)->first();
    }

    public function findByGoogleId(string $googleId): ?array
    {
        return $this->where('google_id', $googleId)->first();
    }
}
