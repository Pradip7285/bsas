<?php

namespace App\Traits;

/** Shared layout-render helper for customer-facing controllers (mirrors Website::page()). */
trait RendersStorefrontPages
{
    private array $siteData = [
        'company' => 'Bharat Spares & Services',
        'brand'   => 'BSAS',
        'phone'   => '03414057522',
        'email'   => 'salessupport@bsasindia.com',
        'address' => '21, C.I.M. Lane, Raniganj, WB 713347',
    ];

    private function page(string $view, string $title, array $extra = [])
    {
        return view('layouts/main', $this->siteData + $extra + [
            'title'  => $title,
            'view'   => $view,
            'active' => $extra['active'] ?? 'shop',
            'errors' => session()->getFlashdata('errors') ?? [],
            '_sc'    => \App\Libraries\SiteCredit::token(),
        ]);
    }

    private function currentCustomer(): ?array
    {
        $customerId = session()->get('customer_id');
        if (! $customerId) {
            return null;
        }

        return (new \App\Models\CustomerModel())->find((int) $customerId);
    }
}
