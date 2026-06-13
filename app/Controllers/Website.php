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
        'phone' => '03414057522',
        'email' => 'salessupport@bsasindia.com',
        'address' => '21, C.I.M. Lane, Raniganj, WB 713347'
    ];

    public function home()
    {
        $baseUrl = base_url();
        $jsonLd  = json_encode([
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'         => 'Organization',
                    '@id'           => $baseUrl . '#organization',
                    'name'          => 'Bharat Spares & Services',
                    'alternateName' => 'BSAS',
                    'url'           => $baseUrl,
                    'logo'          => $baseUrl . 'assets/images/white.svg',
                    'description'   => 'Manufacturers of drill rigs and suppliers of rock drill spares, hydraulic assemblies, and refurbishment services for mining and construction machinery.',
                    'address'       => [
                        '@type'           => 'PostalAddress',
                        'streetAddress'   => '21, C.I.M. Lane',
                        'addressLocality' => 'Raniganj',
                        'addressRegion'   => 'West Bengal',
                        'postalCode'      => '713347',
                        'addressCountry'  => 'IN',
                    ],
                    'contactPoint' => [
                        '@type'       => 'ContactPoint',
                        'telephone'   => '+91-08414057522',
                        'contactType' => 'sales',
                        'email'       => 'sales@exportsindiass.com',
                        'areaServed'  => 'IN',
                    ],
                ],
                [
                    '@type'           => 'WebSite',
                    '@id'             => $baseUrl . '#website',
                    'name'            => 'BSAS – Bharat Spares & Services',
                    'url'             => $baseUrl,
                    'publisher'       => ['@id' => $baseUrl . '#organization'],
                    'inLanguage'      => 'en-IN',
                    'potentialAction' => [
                        '@type'       => 'SearchAction',
                        'target'      => $baseUrl . 'e-shop?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type'       => 'WebPage',
                    '@id'         => $baseUrl . '#webpage',
                    'url'         => $baseUrl,
                    'name'        => 'Bharat Spares & Services (BSAS) | Mining & Construction Equipment',
                    'description' => 'BSAS engineers, manufactures, and rebuilds critical systems for heavy equipment. Rock drill spares, hydraulic assemblies, and India\'s first man-portable drill rig.',
                    'isPartOf'    => ['@id' => $baseUrl . '#website'],
                    'about'       => ['@id' => $baseUrl . '#organization'],
                    'inLanguage'  => 'en-IN',
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->page('home', 'Engineered for Heavy Flow', [
            'metaTitle'       => 'Bharat Spares & Services (BSAS) | Mining & Construction Equipment',
            'metaDescription' => 'Rock drill spares, hydraulic assemblies & India\'s first man-portable rig — BSAS, Raniganj. Serving mining and construction fleets across India.',
            'ogImage'         => base_url('assets/images/photo1.webp'),
            'jsonLd'          => $jsonLd,
        ]);
    }
    public function about()
    {
        return $this->page('about', 'We Manufacture, We Rebuild, We Engineer', [
            'metaTitle'       => 'About BSAS – Bharat Spares & Services | Raniganj, India',
            'metaDescription' => 'Drill rig manufacturers and spare parts suppliers with 50+ years of experience. 26+ acre workshop in Raniganj, West Bengal. Trusted by mining and construction.',
        ]);
    }

    public function spareParts()
    {
        return $this->page('spare-parts', 'One Engineering Brain, Every Critical Spare', [
            'metaTitle'       => 'Rock Drill Spare Parts | BSAS – Bharat Spares & Services',
            'metaDescription' => 'Rock drill spares, drifter assemblies, hydraulic pumps, and gearbox components for mining machinery. OEM-grade, engineering-validated. BSAS India.',
        ]);
    }

    public function equipment()
    {
        return $this->page('equipment', 'Advance Drilling Solutions', [
            'metaTitle'       => 'Drill Rigs & Equipment | BSAS – India\'s First Man-Portable Rig',
            'metaDescription' => 'BSAS manufactures drill rigs including India\'s first man-portable rig for remote exploration. Engineered for mining, construction, and geotechnical applications.',
        ]);
    }

    public function services()
    {
        return $this->page('services', 'Reliable Field Services', [
            'metaTitle'       => 'Equipment Services & Refurbishment | BSAS India',
            'metaDescription' => 'Equipment overhaul, hydraulic refurbishment, component rebuild, and field support for mining fleets. BSAS — 26+ acre workshop, Raniganj, India.',
        ]);
    }
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
            'metaTitle'         => 'BSAS E-Shop | Rock Drill Spares, Hydraulics & Mining Parts',
            'metaDescription'   => 'Browse and request quotes for rock drill spares, hydraulic assemblies, drifters, and gearboxes. Fast sourcing for mining and construction fleets. BSAS India.',
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

        $productDesc = trim((string) ($product['short_description'] ?? ''));
        if ($productDesc === '') {
            $productDesc = 'View specifications, compatibility, and request a quote for ' . $product['name'] . '. Sourced and validated by BSAS engineering for mining and construction equipment.';
        }

        $stockStatus = $product['stock_status'] ?? 'in_stock';
        $availability = match ($stockStatus) {
            'in_stock'      => 'https://schema.org/InStock',
            'made_to_order' => 'https://schema.org/PreOrder',
            default         => 'https://schema.org/OutOfStock',
        };

        $productUrl = site_url('e-shop/product/' . $product['slug']);
        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => $product['name'],
            'image'    => $product['image_url'] ?? base_url('assets/images/photo1.webp'),
            'description' => mb_strimwidth($productDesc, 0, 300, '…'),
            'sku'      => $product['part_number'] ?? $product['slug'],
            'brand'    => ['@type' => 'Brand', 'name' => 'BSAS'],
            'offers'   => [
                '@type'           => 'Offer',
                'url'             => $productUrl,
                'priceCurrency'   => 'INR',
                'price'           => '0',
                'priceValidUntil' => date('Y') . '-12-31',
                'availability'    => $availability,
                'seller'          => ['@type' => 'Organization', 'name' => 'BSAS – Bharat Spares & Services'],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->page('product-detail', $product['name'], [
            'metaTitle'       => $product['name'] . ' | BSAS E-Shop',
            'metaDescription' => mb_strimwidth($productDesc, 0, 160, '…'),
            'ogType'          => 'product',
            'ogImage'         => $product['image_url'] ?? base_url('assets/images/photo1.webp'),
            'jsonLd'          => $jsonLd,
            'product'         => $product,
            'relatedProducts' => $related,
            'cartCount'       => $this->cartCount(),
            'active'          => 'shop',
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

    public function threeR()
    {
        return $this->page('3r', 'Reuse. Repair. Recycle.', [
            'metaTitle'       => 'Rebuild, Repair & Recycle Services | BSAS India',
            'metaDescription' => 'BSAS 3R services extend component life through precision rebuilding, repair, and responsible recycling. Reduce downtime and total ownership cost.',
        ]);
    }

    public function support()
    {
        return $this->page('support', 'We respond fast. We fix faster.', [
            'metaTitle'       => 'Contact BSAS | Spare Parts Enquiry & Technical Support',
            'metaDescription' => 'Contact BSAS for spare parts sourcing, equipment enquiries, and after-sales support. Raniganj, WB. We respond within one business day.',
        ]);
    }

    public function faq()
    {
        return $this->page('faq', 'Frequently Asked Questions', [
            'metaTitle'       => 'FAQs – BSAS Spare Parts, Equipment & Services',
            'metaDescription' => 'Find answers to common questions about BSAS products, spare parts ordering, lead times, OEM compatibility, refurbishment services, and the BSAS e-shop.',
        ]);
    }

    public function privacy()
    {
        return $this->page('privacy', 'Privacy Policy', [
            'metaTitle'       => 'Privacy Policy | BSAS – Bharat Spares & Services',
            'metaDescription' => 'Read the BSAS privacy policy — how we collect, use, and protect your personal data when you use our website, e-shop, or contact forms.',
            'metaRobots'      => 'noindex, follow',
        ]);
    }
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
            'metaTitle'       => 'BSAS Gallery | Field, Workshop & Engineering Albums',
            'metaDescription' => 'Browse BSAS photo galleries — field operations, workshop activity, drill rig manufacturing, and engineering moments organised as dedicated albums. ' . count($albums) . ' albums published.',
            'albums'          => $albums,
            'extraStyles'     => ['/assets/css/gallery.css'],
            'bodyClass'       => 'gallery-body',
            'active'          => 'gallery',
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

        $albumDesc = trim((string) ($album['description'] ?? ''));
        if ($albumDesc === '') {
            $albumDesc = 'Browse photos from the BSAS ' . $album['name'] . ' gallery — field operations, workshop activity, and engineering in action.';
        }

        return $this->page('gallery-album', $album['name'], [
            'metaTitle'       => $album['name'] . ' | BSAS Gallery',
            'metaDescription' => mb_strimwidth($albumDesc, 0, 160, '…'),
            'ogImage'         => $album['cover_image_url'] ?? base_url('assets/images/photo1.webp'),
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

    public function quickQuote()
    {
        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'email'       => 'required|valid_email',
            'machine'     => 'required|min_length[2]|max_length[200]',
            'GST'         => 'permit_empty|max_length[20]|regex_match[/^[A-Z0-9]{4,20}$/i]',
            'requirement' => 'required|min_length[5]|max_length[2000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $gst = strtoupper(trim((string) $this->request->getPost('GST')));

        $this->quoteRequests()->insert([
            'request_type' => 'quick-quote',
            'name'         => (string) $this->request->getPost('name'),
            'email'        => (string) $this->request->getPost('email'),
            'company'      => $gst,
            'phone'        => '',
            'message'      => 'Machine: ' . (string) $this->request->getPost('machine') . "\n\n" . (string) $this->request->getPost('requirement'),
            'source_page'  => 'home',
        ]);

        return $this->response->setJSON(['success' => true]);
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
