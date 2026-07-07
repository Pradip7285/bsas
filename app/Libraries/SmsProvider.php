<?php

namespace App\Libraries;

use App\Libraries\Sms\LogSmsProvider;
use App\Libraries\Sms\Msg91Provider;

/** Resolves the configured SMS provider (.env: sms.provider — defaults to "log"). */
class SmsProvider
{
    public static function resolve(): SmsProviderInterface
    {
        return match ((string) env('sms.provider', 'log')) {
            'msg91' => new Msg91Provider(),
            default => new LogSmsProvider(),
        };
    }
}
