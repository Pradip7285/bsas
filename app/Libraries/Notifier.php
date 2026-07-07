<?php

namespace App\Libraries;

/** Shared SMTP mailer helper — same pattern as Website::notifyLead(), reused by checkout/account/admin order flows. */
class Notifier
{
    public static function sendLeadsEmail(string $subject, string $body): void
    {
        $cfg     = config('Email');
        $toEmail = $cfg->leadsEmail ?? '';
        if ($toEmail === '') {
            return;
        }

        self::send($toEmail, $subject, $body);
    }

    public static function sendCustomerEmail(string $toEmail, string $subject, string $body): void
    {
        if ($toEmail === '') {
            return;
        }

        self::send($toEmail, $subject, $body);
    }

    private static function send(string $toEmail, string $subject, string $body): void
    {
        try {
            $mailer = \Config\Services::email();
            $mailer->setTo($toEmail);
            $mailer->setSubject($subject);
            $mailer->setMessage($body);
            $mailer->send();
        } catch (\Throwable $e) {
            log_message('error', 'Notifier email failed: ' . $e->getMessage());
        }
    }
}
