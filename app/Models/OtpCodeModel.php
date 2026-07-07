<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpCodeModel extends Model
{
    protected $table         = 'otp_codes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'phone',
        'code_hash',
        'purpose',
        'attempts',
        'expires_at',
        'consumed_at',
        'created_at',
    ];

    private const MAX_ATTEMPTS = 5;
    private const TTL_SECONDS  = 600;

    /** Generate and store a new 6-digit OTP, returning the raw code to send via SMS. */
    public function issue(string $phone, string $purpose): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->insert([
            'phone'      => $phone,
            'code_hash'  => hash('sha256', $code),
            'purpose'    => $purpose,
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL_SECONDS),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $code;
    }

    /** Verify a submitted code. Returns true on success; false on mismatch/expiry/attempt cap. */
    public function verify(string $phone, string $purpose, string $code): bool
    {
        $row = $this->where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('consumed_at', null)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! $row || strtotime((string) $row['expires_at']) < time() || (int) $row['attempts'] >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! hash_equals($row['code_hash'], hash('sha256', $code))) {
            $this->update($row['id'], ['attempts' => (int) $row['attempts'] + 1]);

            return false;
        }

        $this->update($row['id'], ['consumed_at' => date('Y-m-d H:i:s')]);

        return true;
    }
}
