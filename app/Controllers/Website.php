<?php

namespace App\Controllers;

use App\Models\BrochureLeadModel;
use App\Models\GalleryAlbumModel;
use App\Models\GalleryItemModel;
use App\Models\ProductModel;
use App\Models\QuoteRequestItemModel;
use App\Models\QuoteRequestModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class Website extends Controller
{
    private array $data = [
        'company' => 'Bharat Spares & Services',
        'brand' => 'BSAS',
        'phone' => '+91 0841 4057522',
        'email' => 'sales@exportsindiass.com',
        'address' => '21, C.I.M. Lane, Raniganj, WB 713347'
    ];

    public function home() { return $this->page('home', 'Engineered for Heavy Flow'); }
    public function about() { return $this->page('about', 'We Manufacture, We Rebuild, We Engineer'); }
    public function spareParts() { return $this->page('spare-parts', 'One Engineering Brain, Every Critical Spare'); }
    public function equipment() { return $this->page('equipment', 'Advance Drilling Solutions'); }
    public function services() { return $this->page('services', 'Reliable Field Services'); }
    public function shop()
    {
        $query    = trim((string) $this->request->getGet('q'));
        $category = trim((string) $this->request->getGet('category'));
        $sort     = trim((string) $this->request->getGet('sort'));
        $page     = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage  = 100;

        // COUNT query — separate builder so state doesn't bleed into the data query.
        $totalCount = $this->buildShopQuery($query, $category)->countAllResults();
        $pageCount  = max(1, (int) ceil($totalCount / $perPage));
        $page       = min($page, $pageCount);

        // Data query with user-selected sort and pagination.
        $dataBuilder = $this->buildShopQuery($query, $category);

        switch ($sort) {
            case 'name_desc':
                $dataBuilder->orderBy('name', 'DESC');
                break;

            case 'category':
                $dataBuilder->orderBy('category', 'ASC')->orderBy('name', 'ASC');
                break;

            case 'name_asc':
            default:
                $sort = 'name_asc';
                $dataBuilder->orderBy('name', 'ASC');
                break;
        }

        $products          = $dataBuilder->findAll($perPage, ($page - 1) * $perPage);
        $categorySummaries = $this->categorySummaries();

        return $this->page('shop', 'E-Shop', [
            'products'          => $products,
            'categories'        => array_column($categorySummaries, 'name'),
            'categorySummaries' => $categorySummaries,
            'searchQuery'       => $query,
            'activeCategory'    => $category,
            'activeSort'        => $sort,
            'cartCount'         => $this->cartCount(),
            'resultCount'       => $totalCount,
            'page'              => $page,
            'pageCount'         => $pageCount,
            'perPage'           => $perPage,
            'totalProducts'     => array_sum(array_column($categorySummaries, 'count')),
            'filterSummary'     => $this->shopFilterSummary($query, $category),
            'active'            => 'shop',
        ]);
    }

    public function product(string $slug)
    {
        $product = $this->products()->where('slug', $slug)->where('is_active', 1)->first();

        if (! $product) {
            throw PageNotFoundException::forPageNotFound();
        }

        $related = $this->products()
            ->active()
            ->where('category', $product['category'])
            ->where('id !=', $product['id'])
            ->findAll(3);

        return $this->page('product-detail', $product['name'], [
            'product' => $product,
            'relatedProducts' => $related,
            'cartCount' => $this->cartCount(),
            'active' => 'shop',
        ]);
    }

    public function cart()
    {
        return $this->page('cart', 'Cart & Quote Request', [
            'cartItems' => $this->cartItems(),
            'cartCount' => $this->cartCount(),
            'active' => 'shop',
        ]);
    }

    public function threeR() { return $this->page('3r', 'Reuse. Repair. Recycle.'); }
    public function support() { return $this->page('support', 'We respond fast. We fix faster.'); }
    public function faq() { return $this->page('faq', 'Frequently Asked Questions'); }
    public function privacy() { return $this->page('privacy', 'Privacy Policy'); }
    public function gallery()
    {
        $albums = $this->galleryAlbumModel()->active()->findAll();
        $albumIds = array_map(static fn(array $album): int => (int) $album['id'], $albums);

        $items = $albumIds === []
            ? []
            : $this->galleryItemModel()->active()->select('album_id, image_url')->whereIn('album_id', $albumIds)->findAll();

        $itemsByAlbum = [];
        foreach ($items as $item) {
            $itemsByAlbum[(int) $item['album_id']][] = $item;
        }

        foreach ($albums as &$album) {
            $albumItems = $itemsByAlbum[(int) $album['id']] ?? [];
            $album['item_count'] = count($albumItems);
            $album['preview_items'] = array_slice($albumItems, 0, 4);
            $album['cover_image_url'] = $album['cover_image_url'] ?: ($albumItems[0]['image_url'] ?? '/assets/images/b&w.png');
        }
        unset($album);

        return $this->page('gallery-home', 'Gallery Albums', [
            'albums' => $albums,
            'extraStyles' => ['/assets/css/gallery.css'],
            'bodyClass' => 'gallery-body',
            'active' => 'gallery',
        ]);
    }

    public function galleryAlbum(string $slug)
    {
        $album = $this->galleryAlbumModel()->active()->where('slug', $slug)->first();

        if (! $album) {
            throw PageNotFoundException::forPageNotFound();
        }

        $items = $this->galleryItemModel()->active()->forAlbum((int) $album['id'])->findAll();

        $album['cover_image_url'] = $album['cover_image_url'] ?: ($items[0]['image_url'] ?? '/assets/images/b&w.png');
        $album['hero_image_url'] = $album['hero_image_url'] ?: $album['cover_image_url'];

        return $this->page('gallery-album', $album['name'], [
            'album' => $album,
            'items' => $items,
            'extraStyles' => ['/assets/css/gallery.css'],
            'bodyClass' => 'gallery-body gallery-body--album',
            'active' => 'gallery',
        ]);
    }

    public function addToCart()
    {
        $rules = [
            'product_id' => 'required|is_natural_no_zero',
            'quantity' => 'required|is_natural_no_zero',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $product = $this->products()->where('id', (int) $this->request->getPost('product_id'))->where('is_active', 1)->first();

        if (! $product) {
            return redirect()->back()->with('error', 'The selected product is no longer available.');
        }

        $cart = $this->sessionCart();
        $productId = (string) $product['id'];
        $quantity = max(1, (int) $this->request->getPost('quantity'));
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        session()->set('cart', $cart);
        session()->setFlashdata('success', 'Product added to cart.');

        return redirect()->back();
    }

    public function updateCart()
    {
        $quantities = $this->request->getPost('quantities') ?? [];
        $cart = [];

        foreach ($quantities as $productId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity > 0) {
                $cart[(string) $productId] = $quantity;
            }
        }

        session()->set('cart', $cart);
        session()->setFlashdata('success', 'Cart updated.');

        return redirect()->to('/cart');
    }

    public function removeFromCart()
    {
        $productId = (string) $this->request->getPost('product_id');
        $cart = $this->sessionCart();
        unset($cart[$productId]);
        session()->set('cart', $cart);
        session()->setFlashdata('success', 'Item removed from cart.');

        return redirect()->to('/cart');
    }

    public function supportQuote()
    {
        $rules = [
            'name' => 'required|min_length[2]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'required|min_length[8]|max_length[20]',
            'message' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/support')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->quoteRequests()->insert([
            'request_type' => 'support',
            'name' => (string) $this->request->getPost('name'),
            'company' => (string) $this->request->getPost('company'),
            'designation' => (string) $this->request->getPost('designation'),
            'email' => (string) $this->request->getPost('email'),
            'phone' => (string) $this->request->getPost('phone'),
            'concern' => (string) $this->request->getPost('concerns'),
            'source_page' => 'support',
            'message' => (string) $this->request->getPost('message'),
        ]);

        session()->setFlashdata('success', 'Thanks. Your request has been received. Our team will contact you shortly.');

        return redirect()->to('/support');
    }

    public function productQuote(string $slug)
    {
        $product = $this->products()->where('slug', $slug)->where('is_active', 1)->first();

        if (! $product) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name' => 'required|min_length[2]',
            'phone' => 'required|min_length[8]|max_length[20]',
            'email' => 'permit_empty|valid_email',
            'quantity' => 'required|is_natural_no_zero',
            'message' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/e-shop/product/' . $slug)->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();
        $db->transStart();

        $quoteId = $this->quoteRequests()->insert([
            'request_type' => 'product',
            'name' => (string) $this->request->getPost('name'),
            'company' => (string) $this->request->getPost('company'),
            'designation' => (string) $this->request->getPost('designation'),
            'email' => (string) $this->request->getPost('email'),
            'phone' => (string) $this->request->getPost('phone'),
            'concern' => $product['category'],
            'source_page' => 'product:' . $product['slug'],
            'message' => (string) $this->request->getPost('message'),
        ], true);

        $this->quoteItems()->insert([
            'quote_request_id' => $quoteId,
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'sku' => $product['sku'],
            'quantity' => (int) $this->request->getPost('quantity'),
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/e-shop/product/' . $slug)
                ->withInput()
                ->with('error', 'Unable to submit the quote request right now. Please try again.');
        }

        session()->setFlashdata('success', 'Quote request submitted for ' . $product['name'] . '.');

        return redirect()->to('/e-shop/product/' . $slug);
    }

    public function cartQuote()
    {
        $items = $this->cartItems();

        if ($items === []) {
            return redirect()->to('/cart')->with('error', 'Your cart is empty.');
        }

        $rules = [
            'name' => 'required|min_length[2]',
            'phone' => 'required|min_length[8]|max_length[20]',
            'email' => 'permit_empty|valid_email',
            'message' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/cart')->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();
        $db->transStart();

        $quoteId = $this->quoteRequests()->insert([
            'request_type' => 'cart',
            'name' => (string) $this->request->getPost('name'),
            'company' => (string) $this->request->getPost('company'),
            'designation' => (string) $this->request->getPost('designation'),
            'email' => (string) $this->request->getPost('email'),
            'phone' => (string) $this->request->getPost('phone'),
            'concern' => 'Cart quote request',
            'source_page' => 'cart',
            'message' => (string) $this->request->getPost('message'),
        ], true);

        foreach ($items as $item) {
            $this->quoteItems()->insert([
                'quote_request_id' => $quoteId,
                'product_id' => $item['product']['id'],
                'product_name' => $item['product']['name'],
                'sku' => $item['product']['sku'],
                'quantity' => $item['quantity'],
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/cart')
                ->withInput()
                ->with('error', 'Unable to submit the cart quote request right now. Please try again.');
        }

        session()->remove('cart');
        session()->setFlashdata('success', 'Your cart quote request has been submitted.');

        return redirect()->to('/cart');
    }

    public function brochureRequest()
    {
        $rules = [
            'mobile' => 'required|min_length[8]|max_length[20]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $this->brochureLeads()->insert([
            'mobile' => (string) $this->request->getPost('mobile'),
            'source' => 'footer',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()?->getAgentString(),
        ]);

        $token = bin2hex(random_bytes(24));
        session()->set('brochure_download_token', $token);

        return $this->response->setJSON([
            'success' => true,
            'downloadUrl' => site_url('brochure/download?token=' . $token),
        ]);
    }

    public function downloadBrochure()
    {
        $token = (string) $this->request->getGet('token');
        $sessionToken = (string) session()->get('brochure_download_token');

        if ($token === '' || $sessionToken === '' || ! hash_equals($sessionToken, $token)) {
            throw PageNotFoundException::forPageNotFound();
        }

        session()->remove('brochure_download_token');

        $path = ROOTPATH . 'Final Website Form.pdf';

        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($path, null);
    }

    private function page(string $view, string $title, array $extra = [])
    {
        return view('layouts/main', $this->data + $extra + [
            'title' => $title,
            'view' => 'pages/' . $view,
            'active' => $extra['active'] ?? $view,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    private function products(): ProductModel
    {
        return new ProductModel();
    }

    private function galleryAlbumModel(): GalleryAlbumModel
    {
        return new GalleryAlbumModel();
    }

    private function galleryItemModel(): GalleryItemModel
    {
        return new GalleryItemModel();
    }

    private function quoteRequests(): QuoteRequestModel
    {
        return new QuoteRequestModel();
    }

    private function quoteItems(): QuoteRequestItemModel
    {
        return new QuoteRequestItemModel();
    }

    private function brochureLeads(): BrochureLeadModel
    {
        return new BrochureLeadModel();
    }

    private function sessionCart(): array
    {
        return session()->get('cart') ?? [];
    }

    private function cartCount(): int
    {
        return array_sum($this->sessionCart());
    }

    private function cartItems(): array
    {
        $cart = $this->sessionCart();
        if ($cart === []) {
            return [];
        }

        $products = $this->products()->whereIn('id', array_map('intval', array_keys($cart)))->findAll();
        $indexed = [];

        foreach ($products as $product) {
            $indexed[(string) $product['id']] = $product;
        }

        $items = [];
        foreach ($cart as $productId => $quantity) {
            if (! isset($indexed[$productId])) {
                continue;
            }

            $items[] = [
                'product' => $indexed[$productId],
                'quantity' => (int) $quantity,
            ];
        }

        return $items;
    }

    /** Returns a ProductModel builder with search/filter conditions applied. */
    private function buildShopQuery(string $query, string $category): ProductModel
    {
        $builder = $this->products()->active();

        if ($query !== '') {
            $builder->groupStart()
                ->like('name', $query)
                ->orLike('sku', $query)
                ->orLike('short_description', $query)
                ->orLike('description', $query)
                ->groupEnd();
        }

        if ($category !== '') {
            $builder->where('category', $category);
        }

        return $builder;
    }

    /**
     * Category summaries (name + product count) in admin-defined sort_order.
     * Single JOIN query replaces the previous two-ORM-call approach.
     * Falls back to a product-only aggregation if the categories table isn't migrated yet.
     * The caller derives the category name list with array_column($result, 'name').
     */
    private function categorySummaries(): array
    {
        $db = db_connect();

        try {
            // Registered categories in admin sort order, joined with active product counts.
            $rows = $db->query("
                SELECT c.name, COUNT(p.id) AS `count`
                FROM categories c
                LEFT JOIN products p ON p.category = c.name AND p.is_active = 1
                WHERE c.is_active = 1
                GROUP BY c.id, c.name, c.sort_order
                HAVING COUNT(p.id) > 0
                ORDER BY c.sort_order ASC, c.name ASC
            ")->getResultArray();

            // Append product categories not yet registered (legacy / bulk-imported rows).
            $orphans = $db->query("
                SELECT category AS name, COUNT(*) AS `count`
                FROM products
                WHERE is_active = 1
                AND category NOT IN (SELECT name FROM categories WHERE is_active = 1)
                GROUP BY category
                HAVING COUNT(*) > 0
                ORDER BY category ASC
            ")->getResultArray();

            return array_map(
                static fn(array $r): array => ['name' => (string) $r['name'], 'count' => (int) $r['count']],
                array_merge($rows, $orphans)
            );
        } catch (\Throwable $e) {
            // categories table not yet migrated — fall back to product-only aggregation.
            $rows = $db->query("
                SELECT category AS name, COUNT(*) AS `count`
                FROM products
                WHERE is_active = 1
                GROUP BY category
                HAVING COUNT(*) > 0
                ORDER BY category ASC
            ")->getResultArray();

            return array_map(
                static fn(array $r): array => ['name' => (string) $r['name'], 'count' => (int) $r['count']],
                $rows
            );
        }
    }

    private function shopFilterSummary(string $query, string $category): string
    {
        if ($query !== '' && $category !== '') {
            return 'Showing matches for "' . $query . '" in ' . $category . '.';
        }

        if ($query !== '') {
            return 'Showing matches for "' . $query . '" across the full catalogue.';
        }

        if ($category !== '') {
            return 'Showing all products in ' . $category . '.';
        }

        return 'Browse the full BSAS catalogue and shortlist products for a consolidated quote request.';
    }
}
