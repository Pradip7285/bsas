<?php

namespace App\Libraries\Sms;

use App\Libraries\SmsProviderInterface;

/** MSG91 REST adapter. Configure via .env: sms.msg91.authKey, sms.msg91.senderId. */
class Msg91Provider implements SmsProviderInterface
{
    public function send(string $phone, string $message): bool
    {
        $authKey  = (string) env('sms.msg91.authKey');
        $senderId = (string) env('sms.msg91.senderId');

        if ($authKey === '') {
            log_message('error', 'MSG91 provider used without sms.msg91.authKey configured.');

            return false;
        }

        try {
            $client   = \Config\Services::curlrequest();
            $response = $client->post('https://api.msg91.com/api/v5/flow/', [
                'headers' => [
                    'authkey'      => $authKey,
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'sender'  => $senderId,
                    'mobiles' => $phone,
                    'message' => $message,
                ],
            ]);

            return $response->getStatusCode() < 300;
        } catch (\Throwable $e) {
            log_message('error', 'MSG91 send failed: ' . $e->getMessage());

            return false;
        }
    }
}
