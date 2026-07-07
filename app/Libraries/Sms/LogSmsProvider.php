<?php

namespace App\Libraries\Sms;

use App\Libraries\SmsProviderInterface;

/** Dev/fallback provider — writes the OTP/message to the app log instead of sending a real SMS. */
class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $phone, string $message): bool
    {
        log_message('info', 'SMS (log provider) to ' . $phone . ': ' . $message);

        return true;
    }
}
