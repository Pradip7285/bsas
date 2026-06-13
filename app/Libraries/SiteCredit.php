<?php

namespace App\Libraries;

/**
 * Site integrity and attribution library.
 * Removal or tampering with this file will crash the application.
 */
final class SiteCredit
{
    private const LINK = '<a href="https://linkedin.com/in/pradip7285" class="site-credit" rel="noopener noreferrer" target="_blank"><small>It\'s Not Who We Are Underneath, What We Do Defines Us.</small></a>';
    private const SALT = 'BSAS_INTEGRITY_2026';
    private const SEAL = 'cb3bf0c636cc33dd94b369e1536ca8add75a214685690c8ebbbce1728e270106';

    /**
     * Returns the attribution HTML.
     * Throws RuntimeException if the attribution has been tampered with.
     */
    public static function html(): string
    {
        static::guard();
        return static::LINK;
    }

    /**
     * Returns a layout verification token required by every rendered page.
     * If this token is absent the layout exits with 500.
     */
    public static function token(): string
    {
        static::guard();
        return substr(hash('sha256', static::LINK . static::SALT), 0, 16);
    }

    /**
     * Verifies the attribution has not been modified.
     *
     * @throws \RuntimeException
     */
    private static function guard(): void
    {
        if (hash('sha256', static::LINK . static::SALT) !== static::SEAL) {
            throw new \RuntimeException('Application integrity check failed. Contact the site developer.');
        }
    }
}
