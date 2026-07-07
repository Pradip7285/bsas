<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Guards customer account and checkout routes — separate from admin auth. */
class CustomerAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('is_customer_authenticated') === true) {
            return null;
        }

        session()->set('post_login_redirect', current_url(true)->getPath());

        return redirect()->to('/login')->with('message', 'Please sign in to continue.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
