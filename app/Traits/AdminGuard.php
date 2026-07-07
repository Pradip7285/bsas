<?php

namespace App\Traits;

/** Shared admin session guard, used by AdminController and Admin\OrderController. */
trait AdminGuard
{
    private function isAuthenticated(): bool
    {
        return session()->get('is_admin_authenticated') === true;
    }

    private function guard()
    {
        if ($this->isAuthenticated()) {
            return null;
        }

        return redirect()->to('/admin/login')->with('message', 'Please sign in to access the backend.');
    }
}
