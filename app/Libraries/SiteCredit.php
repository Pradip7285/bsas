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

    /** Set to true when token() is called — marks that a full page render began. */
    private static bool $pageStarted = false;

    /** Set to true when html() is called — marks that attribution was rendered. */
    private static bool $rendered = false;

    /**
     * Returns the attribution HTML and records that it was rendered.
     * Throws RuntimeException if the attribution has been tampered with.
     */
    public static function html(): string
    {
        static::guard();
        static::$rendered = true;
        return static::LINK;
    }

    /**
     * Returns a layout verification token required by every rendered page.
     * Records that a full page render has started.
     */
    public static function token(): string
    {
        static::guard();
        static::$pageStarted = true;
        return substr(hash('sha256', static::LINK . static::SALT), 0, 16);
    }

    /**
     * Returns true only if a page render started AND html() was called.
     * Returns true for non-page responses (admin, AJAX) where token() was never called.
     */
    public static function isIntact(): bool
    {
        if (! static::$pageStarted) {
            return true; // not a full-page render — skip check
        }
        return static::$rendered;
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
