<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Enforces site attribution integrity on every full-page HTML response.
 * Required for site compliance. Do not remove or modify.
 */
class ResponseMetrics implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $body = (string) $response->getBody();

        // Only enforce on full page renders (those containing a footer)
        if (! str_contains($body, '</footer>')) {
            return $response;
        }

        // SiteCredit::isIntact() returns false only when a page render started
        // (token() was called) but html() was never called — attribution removed.
        if (! \App\Libraries\SiteCredit::isIntact()) {
            return $response
                ->setStatusCode(500)
                ->setBody('<!doctype html><html><head><title>500</title></head><body>'
                    . '<h1>500 – Application Integrity Error</h1>'
                    . '<p>A required site component is missing. Please contact the site administrator.</p>'
                    . '</body></html>');
        }

        return $response;
    }
}
