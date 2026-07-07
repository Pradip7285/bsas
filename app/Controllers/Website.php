<?php

namespace App\Controllers;

use App\Models\BrochureLeadModel;
use App\Models\GalleryAlbumModel;
use App\Models\GalleryItemModel;
use App\Models\ProductModel;
use App\Models\QuoteRequestItemModel;
use App\Models\QuoteRequestModel;
use App\Traits\CartHelpers;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class Website extends Controller
{
    use CartHelpers;

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
        $query       = trim((string) $this->request->getGet('q'));
        $categoryRaw = $this->request->getGet('category');
        $categories  = is_array($categoryRaw) ? $categoryRaw : ($categoryRaw !== null && trim((string) $categoryRaw) !== '' ? [$categoryRaw] : []);
        $categories  = array_values(array_unique(array_filter(array_map(static fn($c): string => trim((string) $c), $categories), static fn(string $c): bool => $c !== '')));
        $sort        = trim((string) $this->request->getGet('sort'));
        $page        = max(1, (int) ($this->request->getGet('page') ?? 1));
        $stockRaw    = $this->request->getGet('stock');
        $stockStatuses = is_array($stockRaw) ? $stockRaw : ($stockRaw !== null && trim((string) $stockRaw) !== '' ? [$stockRaw] : []);
        $stockStatuses = array_values(array_intersect(['in_stock', 'made_to_order', 'out_of_stock'], $stockStatuses));
        $priceMinRaw = $this->request->getGet('price_min');
        $priceMaxRaw = $this->request->getGet('price_max');
        $priceMin    = is_numeric($priceMinRaw) ? (float) $priceMinRaw : null;
        $priceMax    = is_numeric($priceMaxRaw) ? (float) $priceMaxRaw : null;
        $featuredOnly = $this->request->getGet('featured') === '1';
        $saleOnly    = $this->request->getGet('sale') === '1';
        $vehicleRaw  = $this->request->getGet('vehicle');
        $vehicleIds  = is_array($vehicleRaw) ? $vehicleRaw : ($vehicleRaw !== null && trim((string) $vehicleRaw) !== '' ? [$vehicleRaw] : []);
        $vehicleIds  = array_values(array_unique(array_filter(array_map('intval', $vehicleIds), static fn(int $v): bool => $v > 0)));
        $materialRaw = $this->request->getGet('material');
        $materials   = is_array($materialRaw) ? $materialRaw : ($materialRaw !== null && trim((string) $materialRaw) !== '' ? [$materialRaw] : []);
        $materials   = array_values(array_unique(array_filter(array_map(static fn($m): string => trim((string) $m), $materials), static fn(string $m): bool => $m !== '')));
        $divisionRaw = $this->request->getGet('division');
        $divisionIds = is_array($divisionRaw) ? $divisionRaw : ($divisionRaw !== null && trim((string) $divisionRaw) !== '' ? [$divisionRaw] : []);
        $divisionIds = array_values(array_unique(array_filter(array_map('intval', $divisionIds), static fn(int $v): bool => $v > 0)));
        $labelRaw    = $this->request->getGet('label');
        $labelIds    = is_array($labelRaw) ? $labelRaw : ($labelRaw !== null && trim((string) $labelRaw) !== '' ? [$labelRaw] : []);
        $labelIds    = array_values(array_unique(array_filter(array_map('intval', $labelIds), static fn(int $v): bool => $v > 0)));
        $perPage     = 100;

        // Cascading OEM -> Vehicle sidebar step (the actual product filtering still
        // happens via $vehicleIds above, unchanged). All other filters below stay
        // visible the whole time — their counts just narrow once a vehicle is picked.
        $oemRaw      = $this->request->getGet('oem');
        $activeOemId = is_numeric($oemRaw) ? (int) $oemRaw : 0;
        if ($activeOemId <= 0 && $vehicleIds !== []) {
            // Deep link support: a vehicle[] param with no oem= (e.g. product page
            // "Compatible Vehicles" tags) still resolves to the right OEM context.
            $firstVehicle = $this->vehicleModel()->find($vehicleIds[0]);
            $activeOemId  = (int) ($firstVehicle['oem_id'] ?? 0);
        }

        // COUNT query — separate builder so state doesn't bleed into the data query.
        $totalCount = $this->buildShopQuery($query, $categories, $stockStatuses, $priceMin, $priceMax, $featuredOnly, $saleOnly, $vehicleIds, $materials, $divisionIds, $labelIds)->countAllResults();
        $pageCount  = max(1, (int) ceil($totalCount / $perPage));
        $page       = min($page, $pageCount);

        // Data query with user-selected sort and pagination.
        $dataBuilder = $this->buildShopQuery($query, $categories, $stockStatuses, $priceMin, $priceMax, $featuredOnly, $saleOnly, $vehicleIds, $materials, $divisionIds, $labelIds);

        switch ($sort) {
            case 'name_desc':
                $dataBuilder->orderBy('name', 'DESC');
                break;

            case 'category':
                $dataBuilder->orderBy('category', 'ASC')->orderBy('name', 'ASC');
                break;

            case 'in_stock_first':
                $dataBuilder->orderBy("CASE WHEN stock_status = 'in_stock' THEN 0 WHEN stock_status = 'made_to_order' THEN 1 ELSE 2 END", 'ASC', false);
                $dataBuilder->orderBy('name', 'ASC');
                break;

            case 'price_asc':
                $dataBuilder->orderBy('price', 'ASC');
                break;

            case 'price_desc':
                $dataBuilder->orderBy('price', 'DESC');
                break;

            case 'name_asc':
            default:
                // Matches the admin-curated ordering ProductModel::active() used to apply implicitly.
                $sort = 'name_asc';
                $dataBuilder->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');
                break;
        }

        $products          = $dataBuilder->findAll($perPage, ($page - 1) * $perPage);
        $products          = $this->attachLabelNames($products);
        // Every summary below is scoped to $vehicleIds (when set) so Category/Price/
        // Availability/Material/Division/Labels counts reflect the OEM/Vehicle step above.
        $categorySummaries = $this->categorySummaries($vehicleIds);
        $priceBounds       = $this->priceBounds($vehicleIds);
        $oemSummaries      = $this->oemSummaries();
        $vehiclesForSelectedOem = $activeOemId > 0 ? $this->vehicleModel()->vehiclesForOem($activeOemId) : [];
        $stockStatusSummaries = $this->stockStatusSummaries($vehicleIds);
        $materialSummaries    = $this->materialSummaries($vehicleIds);
        $divisionSummaries    = $this->divisionSummaries($vehicleIds);
        $labelSummaries       = $this->labelSummaries($vehicleIds);

        // Canonical consolidates pagination/sort/search variants under the category URL.
        // Keep only the category param — it changes genuine page content, unlike page/sort/q.
        // Only canonicalize when exactly one category is selected (matches the pre-multi-select behavior).
        $shopCanonical = count($categories) === 1
            ? site_url('e-shop') . '?category=' . urlencode($categories[0])
            : site_url('e-shop');

        return $this->page('shop', 'E-Shop', [
            'metaTitle'         => 'BSAS E-Shop | Rock Drill Spares, Hydraulics & Mining Parts',
            'metaDescription'   => 'Browse and request quotes for rock drill spares, hydraulic assemblies, drifters, and gearboxes. Fast sourcing for mining and construction fleets. BSAS India.',
            'canonicalUrl'      => $shopCanonical,
            'products'          => $products,
            'categories'        => array_column($categorySummaries, 'name'),
            'categorySummaries' => $categorySummaries,
            'searchQuery'       => $query,
            'activeCategories'  => $categories,
            'activeCategory'    => $categories[0] ?? '',
            'activeSort'        => $sort,
            'stockStatusSummaries' => $stockStatusSummaries,
            'activeStockStatuses'  => $stockStatuses,
            'priceMin'          => $priceMin,
            'priceMax'          => $priceMax,
            'priceBounds'       => $priceBounds,
            'featuredOnly'      => $featuredOnly,
            'saleOnly'          => $saleOnly,
            'oemSummaries'      => $oemSummaries,
            'activeOemId'       => $activeOemId,
            'vehiclesForSelectedOem' => $vehiclesForSelectedOem,
            'activeVehicleIds'  => $vehicleIds,
            'materialSummaries' => $materialSummaries,
            'activeMaterials'   => $materials,
            'divisionSummaries' => $divisionSummaries,
            'activeDivisionIds' => $divisionIds,
            'labelSummaries'    => $labelSummaries,
            'activeLabelIds'    => $labelIds,
            'cartCount'         => $this->cartCount(),
            'resultCount'       => $totalCount,
            'page'              => $page,
            'pageCount'         => $pageCount,
            'perPage'           => $perPage,
            'totalProducts'     => array_sum(array_column($categorySummaries, 'count')),
            'filterSummary'     => $this->shopFilterSummary($query, $categories, $stockStatuses, $priceMin, $priceMax, $featuredOnly, $saleOnly, $vehicleIds, $materials, $divisionIds, $labelIds),
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

        $compatibleVehicles = db_connect()->query(
            'SELECT v.id, v.name, v.slug FROM vehicles v
             INNER JOIN product_vehicles pv ON pv.vehicle_id = v.id
             WHERE pv.product_id = ? AND v.is_active = 1
             ORDER BY v.name ASC',
            [$product['id']]
        )->getResultArray();

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

        // Stored SEO overrides win when set; otherwise keep the auto-generated values below.
        $metaTitle       = trim((string) ($product['meta_title'] ?? '')) ?: ($product['name'] . ' | BSAS E-Shop');
        $metaDescription = trim((string) ($product['meta_description'] ?? '')) ?: mb_strimwidth($productDesc, 0, 160, '…');
        $canonicalUrl    = trim((string) ($product['canonical_url'] ?? '')) ?: $productUrl;
        $ogImage         = trim((string) ($product['og_image'] ?? '')) ?: ($product['image_url'] ?? base_url('assets/images/photo1.webp'));
        $metaRobots      = trim((string) ($product['robots_meta'] ?? '')) ?: 'index, follow';
        $structuredType  = trim((string) ($product['structured_data_type'] ?? '')) ?: 'Product';

        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => $structuredType,
                    'name'        => $product['name'],
                    'image'       => $ogImage,
                    'description' => mb_strimwidth($productDesc, 0, 300, '…'),
                    'sku'         => $product['sku'] ?? $product['slug'],
                    'mpn'         => $product['part_number'] ?? null,
                    'brand'       => ['@type' => 'Brand', 'name' => 'BSAS'],
                    'offers'      => [
                        '@type'           => 'Offer',
                        'url'             => $productUrl,
                        'priceCurrency'   => 'INR',
                        'price'           => '0',
                        'priceValidUntil' => date('Y') . '-12-31',
                        'availability'    => $availability,
                        'seller'          => ['@type' => 'Organization', 'name' => 'BSAS – Bharat Spares & Services'],
                    ],
                ],
                [
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',   'item' => base_url()],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'E-Shop', 'item' => base_url('e-shop')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $product['name'], 'item' => $productUrl],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->page('product-detail', $product['name'], [
            'metaTitle'       => $metaTitle,
            'metaDescription' => $metaDescription,
            'metaRobots'      => $metaRobots,
            'canonicalUrl'    => $canonicalUrl,
            'ogType'          => 'product',
            'ogImage'         => $ogImage,
            'jsonLd'          => $jsonLd,
            'product'            => $product,
            'relatedProducts'    => $related,
            'compatibleVehicles' => $compatibleVehicles,
            'cartCount'          => $this->cartCount(),
            'active'             => 'shop',
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
        $faqs = [
            ['q' => 'What does BSAS do?', 'a' => 'BSAS engineers, manufactures, and rebuilds critical components for heavy equipment — drilling rigs, mining machines, and construction equipment. We are an engineering company that takes accountability for performance outcomes, not a trader or a spare parts catalogue.'],
            ['q' => 'How do I get a quote from BSAS?', 'a' => 'Share your machine make, model, component details, or a sample/drawing with our team. We will assess the requirement and revert with an engineered recommendation, not just a price.'],
            ['q' => 'What information should I provide to get the most accurate quote?', 'a' => 'Machine model, component part number or description, quantity needed, application details, and if available — OEM specifications or samples. The more detail, the more precise our engineering assessment.'],
            ['q' => 'Does BSAS work with companies of all sizes, or only large mines?', 'a' => 'BSAS works with operators of all scales — from single-machine contractors to large fleet operators. Our engineering approach adapts to the requirement, not the company size.'],
            ['q' => 'Can BSAS reverse-engineer components where drawings are unavailable?', 'a' => 'Yes. Reverse engineering is a core BSAS capability. If drawings or references are unavailable, we can reverse-engineer from samples or field measurements to manufacture components that restore your machine to lifecycle-ready condition.'],
            ['q' => 'What is the difference between BSAS engineered spares and what a trader supplies?', 'a' => 'A trader supplies what is available — with no control over design, no accountability beyond the sale. BSAS designs, validates, manufactures, and takes performance responsibility. One engineering brain. Complete responsibility.'],
            ['q' => 'How do I know BSAS parts will perform in harsh conditions?', 'a' => 'All components are designed and validated for specific applications and operating environments. We combine field feedback, OEM benchmarking, and engineering validation to ensure performance, not just fit.'],
            ['q' => 'Do you benchmark against OEM specifications?', 'a' => 'Yes. OEM benchmarking is a standard part of our engineering process. In many cases, we identify and correct design limitations that cause premature failure in Indian mining and construction conditions.'],
            ['q' => 'What quality processes does BSAS follow?', 'a' => 'Our process covers design validation, material selection, heat treatment, machining tolerances, and assembly inspection — executed through BSAS-approved facilities with full engineering oversight.'],
            ['q' => 'What is the BSAS 3R Programme?', 'a' => 'Reuse, Repair, Re-engineer. The 3R Programme is BSAS\'s approach to sustainable equipment lifecycle management — making the engineering decision on whether to rebuild, remanufacture, or replace based on performance data and application knowledge, not just cost.'],
            ['q' => 'What are typical lead times for engineered components?', 'a' => 'Lead times vary by component complexity. Standard items from stocked inventory are available immediately. Custom-engineered components typically range from 2–8 weeks depending on machining requirements and validation steps.'],
            ['q' => 'Can BSAS support full machine lifecycle management?', 'a' => 'Yes. From initial component supply through to scheduled overhauls, refurbishments, and end-of-life assessments, BSAS provides engineering-backed support across the complete machine lifecycle.'],
            ['q' => 'How does BSAS ensure accountability after delivery?', 'a' => 'We stand behind every component we supply. If a BSAS-engineered part fails in-application under normal operating conditions, we investigate, learn, and take corrective action. This is part of what we mean by engineering accountability.'],
            ['q' => 'Are BSAS components more expensive than standard spares?', 'a' => 'The upfront cost may be comparable or slightly higher than unvalidated alternatives. However, when you factor in extended service life, reduced unplanned downtime, and engineering support — the total cost of ownership is consistently lower.'],
        ];

        $jsonLd = json_encode([
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(static fn(array $faq): array => [
                '@type'          => 'Question',
                'name'           => $faq['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
            ], $faqs),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->page('faq', 'Frequently Asked Questions', [
            'metaTitle'       => 'FAQs – BSAS Spare Parts, Equipment & Services',
            'metaDescription' => 'Find answers to common questions about BSAS products, spare parts ordering, lead times, OEM compatibility, refurbishment services, and the BSAS e-shop.',
            'jsonLd'          => $jsonLd,
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

        $albumUrl  = site_url('gallery/' . $album['slug']);
        $albumJsonLd = json_encode([
            '@context'         => 'https://schema.org',
            '@type'            => 'BreadcrumbList',
            'itemListElement'  => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',    'item' => base_url()],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Gallery', 'item' => base_url('gallery')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $album['name'], 'item' => $albumUrl],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->page('gallery-album', $album['name'], [
            'metaTitle'       => $album['name'] . ' | BSAS Gallery',
            'metaDescription' => mb_strimwidth($albumDesc, 0, 160, '…'),
            'ogImage'         => $album['cover_image_url'] ?? base_url('assets/images/photo1.webp'),
            'jsonLd'          => $albumJsonLd,
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

        $quantity = max(1, (int) $this->request->getPost('quantity'));
        $this->addToCartStorage((int) $product['id'], $quantity);

        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setJSON([
                'success'     => true,
                'cartCount'   => $this->cartCount(),
                'productName' => $product['name'],
            ]);
        }

        session()->setFlashdata('success', 'Product added to cart.');

        return redirect()->back();
    }

    public function updateCart()
    {
        $quantities = $this->request->getPost('quantities') ?? [];
        $this->updateCartStorage($quantities);
        session()->setFlashdata('success', 'Cart updated.');

        return redirect()->to('/cart');
    }

    public function removeFromCart()
    {
        $this->removeFromCartStorage((int) $this->request->getPost('product_id'));
        session()->setFlashdata('success', 'Item removed from cart.');

        return redirect()->to('/cart');
    }

    public function quickQuote()
    {
        $throttler = \Config\Services::throttler();
        if (! $throttler->check(md5($this->request->getIPAddress() . 'qq'), 5, MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON([
                'success' => false,
                'errors'  => ['Too many submissions. Please wait a moment before trying again.'],
            ]);
        }

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

        $name    = (string) $this->request->getPost('name');
        $email   = (string) $this->request->getPost('email');
        $machine = (string) $this->request->getPost('machine');
        $require = (string) $this->request->getPost('requirement');

        $model = $this->quoteRequests();
        $saved = $model->insert([
            'request_type' => 'quick-quote',
            'name'         => $name,
            'email'        => $email,
            'company'      => $gst,
            'phone'        => '',
            'message'      => 'Machine: ' . $machine . "\n\n" . $require,
            'source_page'  => 'home',
        ]);

        if (! $saved) {
            log_message('error', 'QuickQuote DB insert failed: ' . json_encode($model->errors()));
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'errors'  => ['Could not save your request. Please try again or contact us directly.'],
            ]);
        }

        $this->notifyLead(
            "Quick Quote — {$name}",
            "Name:       {$name}\nEmail:      {$email}\nGSTIN/VAT:  {$gst}\nMachine:    {$machine}\n\nRequirement:\n{$require}"
        );

        return $this->response->setJSON(['success' => true]);
    }

    public function supportQuote()
    {
        $throttler = \Config\Services::throttler();
        if (! $throttler->check(md5($this->request->getIPAddress() . 'support'), 5, MINUTE)) {
            session()->setFlashdata('error', 'Too many submissions. Please wait a moment before trying again.');
            return redirect()->to('/support');
        }

        $rules = [
            'name' => 'required|min_length[2]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'required|min_length[8]|max_length[20]',
            'message' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/support')->withInput()->with('errors', $this->validator->getErrors());
        }

        $sName  = (string) $this->request->getPost('name');
        $sEmail = (string) $this->request->getPost('email');
        $sPhone = (string) $this->request->getPost('phone');
        $sCo    = (string) $this->request->getPost('company');
        $sDesg  = (string) $this->request->getPost('designation');
        $sConcern = (string) $this->request->getPost('concerns');
        $sMsg   = (string) $this->request->getPost('message');

        $this->quoteRequests()->insert([
            'request_type' => 'support',
            'name'         => $sName,
            'company'      => $sCo,
            'designation'  => $sDesg,
            'email'        => $sEmail,
            'phone'        => $sPhone,
            'concern'      => $sConcern,
            'source_page'  => 'support',
            'message'      => $sMsg,
        ]);

        $this->notifyLead(
            "Support Request — {$sName}",
            "Name:        {$sName}\nEmail:       {$sEmail}\nPhone:       {$sPhone}\nCompany:     {$sCo}\nDesignation: {$sDesg}\nConcern:     {$sConcern}\n\nMessage:\n{$sMsg}"
        );

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

        $this->clearCartStorage();
        session()->setFlashdata('success', 'Your cart quote request has been submitted.');

        return redirect()->to('/cart');
    }

    public function brochureRequest()
    {
        $throttler = \Config\Services::throttler();
        if (! $throttler->check(md5($this->request->getIPAddress() . 'brochure'), 3, MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON(['success' => false, 'message' => 'Too many requests. Please wait.']);
        }

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
            'title'  => $title,
            'view'   => 'pages/' . $view,
            'active' => $extra['active'] ?? $view,
            'errors' => session()->getFlashdata('errors') ?? [],
            '_sc'    => \App\Libraries\SiteCredit::token(),
        ]);
    }

    public function sitemap()
    {
        $baseUrl  = rtrim(base_url(), '/');
        $products = $this->products()->active()->select('slug, updated_at')->findAll();
        $albums   = $this->galleryAlbumModel()->active()->select('slug, updated_at')->findAll();

        $urls = [
            ['loc' => $baseUrl . '/',            'changefreq' => 'weekly',  'priority' => '1.0'],
            ['loc' => $baseUrl . '/about-us',    'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/spare-parts', 'changefreq' => 'monthly', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/equipments',  'changefreq' => 'monthly', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/services',    'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/e-shop',      'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => $baseUrl . '/gallery',     'changefreq' => 'weekly',  'priority' => '0.6'],
            ['loc' => $baseUrl . '/support',     'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/faq',         'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $baseUrl . '/3r',          'changefreq' => 'monthly', 'priority' => '0.6'],
        ];

        foreach ($products as $p) {
            $urls[] = [
                'loc'        => $baseUrl . '/e-shop/product/' . $p['slug'],
                'lastmod'    => substr((string) ($p['updated_at'] ?? date('Y-m-d')), 0, 10),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        foreach ($albums as $a) {
            $urls[] = [
                'loc'        => $baseUrl . '/gallery/' . $a['slug'],
                'lastmod'    => substr((string) ($a['updated_at'] ?? date('Y-m-d')), 0, 10),
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n"
                  . '    <loc>' . htmlspecialchars($url['loc']) . "</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n"
                  . "    <priority>{$url['priority']}</priority>\n"
                  . "  </url>\n";
        }

        $xml .= '</urlset>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    private function notifyLead(string $subject, string $body): void
    {
        $cfg     = config('Email');
        $toEmail = $cfg->leadsEmail ?? '';
        if ($toEmail === '') {
            return;
        }

        try {
            $mailer = \Config\Services::email();
            $mailer->setTo($toEmail);
            $mailer->setSubject('[BSAS Lead] ' . $subject);
            $mailer->setMessage($body);
            $mailer->send();
        } catch (\Throwable $e) {
            log_message('error', 'Lead email failed: ' . $e->getMessage());
        }
    }

    private function products(): ProductModel
    {
        return new ProductModel();
    }

    private function vehicleModel(): \App\Models\VehicleModel
    {
        return new \App\Models\VehicleModel();
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

    /** Returns a ProductModel builder with search/filter conditions applied. */
    private function buildShopQuery(
        string $query,
        array $categories = [],
        array $stockStatuses = [],
        ?float $priceMin = null,
        ?float $priceMax = null,
        bool $featuredOnly = false,
        bool $saleOnly = false,
        array $vehicleIds = [],
        array $materials = [],
        array $divisionIds = [],
        array $labelIds = []
    ): ProductModel {
        // Deliberately not using ProductModel::active() here — it bakes in
        // ORDER BY sort_order, name, which would outrank whatever sort the
        // caller applies below (price, name desc, etc. would silently have no
        // effect once sort_order/name already fully order the result set).
        $builder = $this->products()->where('is_active', 1);

        if ($stockStatuses !== []) {
            $builder->whereIn('stock_status', $stockStatuses);
        }

        if ($query !== '') {
            $builder->groupStart()
                ->like('name', $query)
                ->orLike('sku', $query)
                ->orLike('short_description', $query)
                ->orLike('description', $query)
                ->orLike('meta_keyword', $query)
                ->groupEnd();
        }

        if ($categories !== []) {
            $builder->whereIn('category', $categories);
        }

        if ($priceMin !== null) {
            $builder->where('price >=', $priceMin);
        }

        if ($priceMax !== null) {
            $builder->where('price <=', $priceMax);
        }

        if ($featuredOnly) {
            $builder->where('is_featured', 1);
        }

        if ($saleOnly) {
            $builder->where('compare_at_price IS NOT NULL', null, false)
                ->where('compare_at_price > price', null, false);
        }

        if ($vehicleIds !== []) {
            $productIds = array_column(
                db_connect()->query(
                    'SELECT DISTINCT product_id FROM product_vehicles WHERE vehicle_id IN (' . implode(',', array_map('intval', $vehicleIds)) . ')'
                )->getResultArray(),
                'product_id'
            );
            $builder->whereIn('id', $productIds !== [] ? $productIds : [0]);
        }

        if ($materials !== []) {
            $builder->whereIn('material', $materials);
        }

        if ($divisionIds !== []) {
            $categoryIds = array_column(
                db_connect()->query(
                    'SELECT id FROM categories WHERE division_id IN (' . implode(',', array_map('intval', $divisionIds)) . ')'
                )->getResultArray(),
                'id'
            );
            $builder->whereIn('category_id', $categoryIds !== [] ? $categoryIds : [0]);
        }

        if ($labelIds !== []) {
            $productIds = array_column(
                db_connect()->query(
                    'SELECT DISTINCT product_id FROM product_labels WHERE label_id IN (' . implode(',', array_map('intval', $labelIds)) . ')'
                )->getResultArray(),
                'product_id'
            );
            $builder->whereIn('id', $productIds !== [] ? $productIds : [0]);
        }

        return $builder;
    }

    /** Attaches a `labels` array (label names) to each product row, batched in one query. */
    private function attachLabelNames(array $products): array
    {
        $ids = array_column($products, 'id');
        if ($ids === []) {
            return $products;
        }

        $rows = db_connect()->query(
            'SELECT pl.product_id, l.name FROM product_labels pl
             INNER JOIN labels l ON l.id = pl.label_id
             WHERE pl.product_id IN (' . implode(',', array_map('intval', $ids)) . ')'
        )->getResultArray();

        $byProduct = [];
        foreach ($rows as $row) {
            $byProduct[(int) $row['product_id']][] = $row['name'];
        }

        foreach ($products as &$product) {
            $product['labels'] = $byProduct[(int) $product['id']] ?? [];
        }

        return $products;
    }

    /**
     * SQL fragment restricting a products query to only those linked (via product_vehicles)
     * to one of the given vehicle IDs. Empty string when no vehicle is selected.
     */
    private function vehicleScopeSql(array $vehicleIds, string $column = 'id'): string
    {
        if ($vehicleIds === []) {
            return '';
        }

        return ' AND ' . $column . ' IN (SELECT DISTINCT product_id FROM product_vehicles WHERE vehicle_id IN ('
            . implode(',', array_map('intval', $vehicleIds)) . '))';
    }

    /** Min/max price across active, orderable (price > 0) products — used for the sidebar range hint. */
    private function priceBounds(array $vehicleIds = []): array
    {
        $row = db_connect()->query(
            'SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM products WHERE is_active = 1 AND price > 0'
            . $this->vehicleScopeSql($vehicleIds)
        )->getRowArray();

        return [
            'min' => (float) ($row['min_price'] ?? 0),
            'max' => (float) ($row['max_price'] ?? 0),
        ];
    }

    /** Product counts per stock status, active products only, in a fixed display order. */
    private function stockStatusSummaries(array $vehicleIds = []): array
    {
        $rows = db_connect()->query("
            SELECT stock_status, COUNT(*) AS cnt
            FROM products
            WHERE is_active = 1
        " . $this->vehicleScopeSql($vehicleIds) . "
            GROUP BY stock_status
        ")->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['stock_status']] = (int) $row['cnt'];
        }

        $labels = [
            'in_stock'      => 'In Stock',
            'made_to_order' => 'Made to Order',
            'out_of_stock'  => 'Out of Stock',
        ];

        $summaries = [];
        foreach ($labels as $value => $label) {
            if (($counts[$value] ?? 0) > 0) {
                $summaries[] = ['value' => $value, 'label' => $label, 'count' => $counts[$value]];
            }
        }

        return $summaries;
    }

    /** Distinct materials (name + product count) across active products, alphabetical. */
    private function materialSummaries(array $vehicleIds = []): array
    {
        $rows = db_connect()->query("
            SELECT material AS name, COUNT(*) AS cnt
            FROM products
            WHERE is_active = 1 AND material IS NOT NULL AND material <> ''
        " . $this->vehicleScopeSql($vehicleIds) . "
            GROUP BY material
            HAVING COUNT(*) > 0
            ORDER BY material ASC
        ")->getResultArray();

        return array_map(
            static fn(array $r): array => ['name' => (string) $r['name'], 'count' => (int) $r['cnt']],
            $rows
        );
    }

    /** Division summaries (id + name + product count via each division's categories), active only. */
    private function divisionSummaries(array $vehicleIds = []): array
    {
        $rows = db_connect()->query("
            SELECT d.id, d.name, COUNT(p.id) AS cnt
            FROM divisions d
            INNER JOIN categories c ON c.division_id = d.id
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
        " . $this->vehicleScopeSql($vehicleIds, 'p.id') . "
            WHERE d.is_active = 1
            GROUP BY d.id, d.name, d.sort_order
            HAVING COUNT(p.id) > 0
            ORDER BY d.sort_order ASC, d.name ASC
        ")->getResultArray();

        return array_map(
            static fn(array $r): array => ['id' => (int) $r['id'], 'name' => (string) $r['name'], 'count' => (int) $r['cnt']],
            $rows
        );
    }

    /** Label summaries (id + name + product count via the product_labels pivot), active only. */
    private function labelSummaries(array $vehicleIds = []): array
    {
        $rows = db_connect()->query("
            SELECT l.id, l.name, COUNT(pl.product_id) AS cnt
            FROM labels l
            INNER JOIN product_labels pl ON pl.label_id = l.id
            INNER JOIN products p ON p.id = pl.product_id AND p.is_active = 1
        " . $this->vehicleScopeSql($vehicleIds, 'p.id') . "
            WHERE l.is_active = 1
            GROUP BY l.id, l.name, l.sort_order
            HAVING COUNT(pl.product_id) > 0
            ORDER BY l.sort_order ASC, l.name ASC
        ")->getResultArray();

        return array_map(
            static fn(array $r): array => ['id' => (int) $r['id'], 'name' => (string) $r['name'], 'count' => (int) $r['cnt']],
            $rows
        );
    }

    /**
     * OEM summaries (id + name + product count reachable through that OEM's vehicles),
     * active only. Used for the storefront's cascading OEM -> Vehicle filter step.
     */
    private function oemSummaries(): array
    {
        $rows = db_connect()->query("
            SELECT o.id, o.name, COUNT(DISTINCT pv.product_id) AS cnt
            FROM oems o
            INNER JOIN vehicles v ON v.oem_id = o.id AND v.is_active = 1
            LEFT JOIN product_vehicles pv ON pv.vehicle_id = v.id
            WHERE o.is_active = 1
            GROUP BY o.id, o.name, o.sort_order
            HAVING COUNT(DISTINCT pv.product_id) > 0
            ORDER BY o.sort_order ASC, o.name ASC
        ")->getResultArray();

        return array_map(
            static fn(array $r): array => ['id' => (int) $r['id'], 'name' => (string) $r['name'], 'count' => (int) $r['cnt']],
            $rows
        );
    }

    /**
     * Category summaries (name + product count) in admin-defined sort_order.
     * Single JOIN query replaces the previous two-ORM-call approach.
     * Falls back to a product-only aggregation if the categories table isn't migrated yet.
     * The caller derives the category name list with array_column($result, 'name').
     */
    private function categorySummaries(array $vehicleIds = []): array
    {
        $db = db_connect();

        try {
            // Registered categories in admin sort order, joined with active product counts.
            $rows = $db->query("
                SELECT c.name, COUNT(p.id) AS `count`
                FROM categories c
                LEFT JOIN products p ON p.category = c.name AND p.is_active = 1
            " . $this->vehicleScopeSql($vehicleIds, 'p.id') . "
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
            " . $this->vehicleScopeSql($vehicleIds) . "
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
            " . $this->vehicleScopeSql($vehicleIds) . "
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

    private function shopFilterSummary(
        string $query,
        array $categories = [],
        array $stockStatuses = [],
        ?float $priceMin = null,
        ?float $priceMax = null,
        bool $featuredOnly = false,
        bool $saleOnly = false,
        array $vehicleIds = [],
        array $materials = [],
        array $divisionIds = [],
        array $labelIds = []
    ): string {
        $category = $categories !== [] ? implode(', ', $categories) : '';

        $extras = [];
        if ($stockStatuses !== []) {
            $stockLabels = ['in_stock' => 'in stock', 'made_to_order' => 'made to order', 'out_of_stock' => 'out of stock'];
            $extras[] = implode('/', array_map(static fn(string $s): string => $stockLabels[$s] ?? $s, $stockStatuses));
        }
        if ($materials !== []) {
            $extras[] = implode(', ', $materials) . ' material';
        }
        if ($divisionIds !== []) {
            $names = array_column(
                db_connect()->query('SELECT name FROM divisions WHERE id IN (' . implode(',', array_map('intval', $divisionIds)) . ')')->getResultArray(),
                'name'
            );
            if ($names !== []) {
                $extras[] = implode(', ', $names) . ' division';
            }
        }
        if ($labelIds !== []) {
            $names = array_column(
                db_connect()->query('SELECT name FROM labels WHERE id IN (' . implode(',', array_map('intval', $labelIds)) . ')')->getResultArray(),
                'name'
            );
            if ($names !== []) {
                $extras[] = implode(', ', $names) . ' label';
            }
        }
        if ($vehicleIds !== []) {
            $names = array_column($this->vehicleModel()->whereIn('id', $vehicleIds)->findAll(), 'name');
            if ($names !== []) {
                $extras[] = 'fits ' . implode(', ', $names);
            }
        }
        if ($priceMin !== null || $priceMax !== null) {
            $extras[] = match (true) {
                $priceMin !== null && $priceMax !== null => '₹' . number_format($priceMin, 0) . '–₹' . number_format($priceMax, 0),
                $priceMin !== null => 'over ₹' . number_format($priceMin, 0),
                default             => 'under ₹' . number_format($priceMax, 0),
            };
        }
        if ($featuredOnly) {
            $extras[] = 'featured';
        }
        if ($saleOnly) {
            $extras[] = 'on sale';
        }
        $extraSuffix = $extras !== [] ? ' (' . implode(', ', $extras) . ')' : '';

        if ($query !== '' && $category !== '') {
            return 'Showing matches for "' . $query . '" in ' . $category . $extraSuffix . '.';
        }

        if ($query !== '') {
            return 'Showing matches for "' . $query . '" across the full catalogue' . $extraSuffix . '.';
        }

        if ($category !== '') {
            return 'Showing all products in ' . $category . $extraSuffix . '.';
        }

        if ($extras !== []) {
            return 'Showing products across the full catalogue' . $extraSuffix . '.';
        }

        return 'Browse the full BSAS catalogue and shortlist products for a consolidated quote request.';
    }
}
