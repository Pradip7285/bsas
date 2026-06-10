<?php

namespace App\Controllers;

use App\Libraries\SimpleSpreadsheetReader;
use App\Models\BrochureLeadModel;
use App\Models\CategoryModel;
use App\Models\GalleryAlbumModel;
use App\Models\GalleryItemModel;
use App\Models\ProductModel;
use App\Models\QuoteRequestItemModel;
use App\Models\QuoteRequestModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Exceptions\PageNotFoundException;

class AdminController extends Controller
{
    public function login()
    {
        if ($this->isAuthenticated()) {
            return redirect()->to('/admin');
        }

        return view('admin/login', [
            'errors' => session()->getFlashdata('errors') ?? [],
            'message' => session()->getFlashdata('message'),
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/admin/login')->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $expectedUsername = (string) env('admin.username');
        $expectedPasswordHash = (string) env('admin.passwordHash');

        if ($expectedUsername === '' || $expectedPasswordHash === '') {
            return redirect()->to('/admin/login')->with('message', 'Admin credentials are not configured in the environment.');
        }

        if (! hash_equals($expectedUsername, $username) || ! password_verify($password, $expectedPasswordHash)) {
            return redirect()->to('/admin/login')->withInput()->with('errors', ['Invalid admin credentials.']);
        }

        session()->set('is_admin_authenticated', true);
        session()->set('admin_username', $username);

        return redirect()->to('/admin');
    }

    public function logout()
    {
        session()->remove(['is_admin_authenticated', 'admin_username']);

        return redirect()->to('/admin/login')->with('message', 'You have been signed out.');
    }

    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $productSearch = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $category = trim((string) $this->request->getGet('category'));

        $builder = $this->products()->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');

        if ($productSearch !== '') {
            $builder->groupStart()
                ->like('name', $productSearch)
                ->orLike('sku', $productSearch)
                ->orLike('category', $productSearch)
                ->groupEnd();
        }

        if ($status === 'active') {
            $builder->where('is_active', 1);
        } elseif ($status === 'hidden') {
            $builder->where('is_active', 0);
        } else {
            $status = '';
        }

        if ($category !== '') {
            $builder->where('category', $category);
        }

        $recentQuotes = $this->quoteRequests()->orderBy('created_at', 'DESC')->findAll(8);
        $recentQuoteIds = array_map(static fn(array $quote): int => (int) $quote['id'], $recentQuotes);
        $recentQuoteItems = $recentQuoteIds === []
            ? []
            : $this->quoteItems()->whereIn('quote_request_id', $recentQuoteIds)->orderBy('created_at', 'ASC')->findAll();

        // Cap the dashboard product list at 50 rows. The full filterable list lives
        // at /admin/products. Without a limit, this query loads every product row
        // into PHP memory on every dashboard load.
        $dashboardProducts = $builder->limit(50)->findAll();

        // Use the categories table (small, indexed) for the filter dropdown instead
        // of DISTINCT on the products VARCHAR column.
        $categoryNames = array_column($this->loadCategories(), 'name');

        return view('admin/dashboard', [
            'products' => $dashboardProducts,
            'quotes' => $recentQuotes,
            'quoteItemsByRequest' => $this->groupQuoteItemsByRequest($recentQuoteItems),
            'brochureLeads' => $this->brochureLeads()->orderBy('created_at', 'DESC')->findAll(8),
            'productSearch' => $productSearch,
            'activeStatus' => $status,
            'activeCategory' => $category,
            'categories' => $categoryNames,
            'stats' => $this->dashboardStats(),
            'catalogAudit' => $this->catalogAudit(),
            'categoryBreakdown' => $this->productCategoryBreakdown(),
            'quoteBreakdown' => $this->quoteRequestBreakdown(),
            'performanceSnapshot' => $this->performanceSnapshot(),
            'activityTimeline' => $this->activityTimeline(),
            'topRequestedProducts' => $this->topRequestedProducts(),
            'sourcePageBreakdown' => $this->sourcePageBreakdown(),
            'importSummary' => session()->getFlashdata('importSummary'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function leads()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $quoteType = trim((string) $this->request->getGet('type'));
        $leadSearch = trim((string) $this->request->getGet('q'));

        $builder = $this->quoteRequests()->orderBy('created_at', 'DESC');

        if ($quoteType !== '') {
            $builder->where('request_type', $quoteType);
        }

        if ($leadSearch !== '') {
            $builder->groupStart()
                ->like('name', $leadSearch)
                ->orLike('company', $leadSearch)
                ->orLike('phone', $leadSearch)
                ->orLike('email', $leadSearch)
                ->orLike('concern', $leadSearch)
                ->groupEnd();
        }

        $quotes = $builder->findAll();
        $quoteIds = array_map(static fn(array $quote): int => (int) $quote['id'], $quotes);
        $quoteItems = $quoteIds === []
            ? []
            : $this->quoteItems()->whereIn('quote_request_id', $quoteIds)->orderBy('created_at', 'ASC')->findAll();

        return view('admin/leads', [
            'quotes' => $quotes,
            'quoteItemsByRequest' => $this->groupQuoteItemsByRequest($quoteItems),
            'brochureLeads' => $this->brochureLeads()->orderBy('created_at', 'DESC')->findAll(),
            'quoteType' => $quoteType,
            'leadSearch' => $leadSearch,
            'quoteBreakdown' => $this->quoteRequestBreakdown(),
        ]);
    }

    /* ── Products list ───────────────────────────────────────── */

    public function productsList()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $search   = trim((string) $this->request->getGet('q'));
        $status   = trim((string) $this->request->getGet('status'));
        $category = trim((string) $this->request->getGet('category'));

        $builder = $this->products()->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('sku', $search)
                ->orLike('category', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }

        if ($status === 'active') {
            $builder->where('is_active', 1);
        } elseif ($status === 'hidden') {
            $builder->where('is_active', 0);
        } else {
            $status = '';
        }

        if ($category !== '') {
            $builder->where('category', $category);
        }

        return view('admin/products', [
            'products'       => $builder->findAll(),
            'categories'     => $this->loadCategories(),
            'productSearch'  => $search,
            'activeStatus'   => $status,
            'activeCategory' => $category,
            'stats'          => $this->dashboardStats(),
            'catalogAudit'   => $this->catalogAudit(),
        ]);
    }

    /* ── Categories ──────────────────────────────────────────── */

    public function categories()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $counts = $this->categoryModel()->productCounts();

        return view('admin/categories', [
            'categories'    => $this->categoryModel()->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll(),
            'productCounts' => $counts,
            'errors'        => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function galleryAlbums()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $albums = $this->galleryAlbumModel()->orderBy('sort_order', 'ASC')->orderBy('event_date', 'DESC')->findAll();
        $counts = $this->galleryItemCounts();

        return view('admin/gallery-albums', [
            'albums' => $albums,
            'itemCounts' => $counts,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function newGalleryAlbum()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        return view('admin/gallery-album-form', [
            'album' => null,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function createGalleryAlbum()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $payload = $this->validatedGalleryAlbumPayload();

        if ($payload === null) {
            return redirect()->to('/admin/gallery/new')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->galleryAlbumModel()->insert($payload);
        session()->setFlashdata('success', 'Gallery album created.');

        return redirect()->to('/admin/gallery');
    }

    public function editGalleryAlbum(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $album = $this->galleryAlbumModel()->find($id);

        if (! $album) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/gallery-album-form', [
            'album' => $album,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function updateGalleryAlbum(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $album = $this->galleryAlbumModel()->find($id);

        if (! $album) {
            throw PageNotFoundException::forPageNotFound();
        }

        $payload = $this->validatedGalleryAlbumPayload($id);

        if ($payload === null) {
            return redirect()->to('/admin/gallery/' . $id . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->galleryAlbumModel()->update($id, $payload);
        session()->setFlashdata('success', 'Gallery album updated.');

        return redirect()->to('/admin/gallery');
    }

    public function deleteGalleryAlbum(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $album = $this->galleryAlbumModel()->find($id);

        if (! $album) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->galleryAlbumModel()->delete($id);
        session()->setFlashdata('success', 'Gallery album deleted.');

        return redirect()->to('/admin/gallery');
    }

    public function galleryItems(int $albumId)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $album = $this->galleryAlbumModel()->find($albumId);

        if (! $album) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/gallery-items', [
            'album' => $album,
            'items' => $this->galleryItemModel()->forAlbum($albumId)->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll(),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function createGalleryItem(int $albumId)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if (! $this->galleryAlbumModel()->find($albumId)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $payload = $this->validatedGalleryItemPayload($albumId);

        if ($payload === null) {
            return redirect()->to('/admin/gallery/' . $albumId . '/items')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->galleryItemModel()->insert($payload);
        session()->setFlashdata('success', 'Gallery image added.');

        return redirect()->to('/admin/gallery/' . $albumId . '/items');
    }

    public function updateGalleryItem(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $item = $this->galleryItemModel()->find($id);

        if (! $item) {
            throw PageNotFoundException::forPageNotFound();
        }

        $payload = $this->validatedGalleryItemPayload((int) $item['album_id']);

        if ($payload === null) {
            return redirect()->to('/admin/gallery/' . (int) $item['album_id'] . '/items')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->galleryItemModel()->update($id, $payload);
        session()->setFlashdata('success', 'Gallery image updated.');

        return redirect()->to('/admin/gallery/' . (int) $item['album_id'] . '/items');
    }

    public function deleteGalleryItem(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $item = $this->galleryItemModel()->find($id);

        if (! $item) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->galleryItemModel()->delete($id);
        session()->setFlashdata('success', 'Gallery image deleted.');

        return redirect()->to('/admin/gallery/' . (int) $item['album_id'] . '/items');
    }

    public function createCategory()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Category name is required.'])->setStatusCode(422);
            }

            return redirect()->to('/admin/categories')->with('errors', ['Category name is required.']);
        }

        $existing = $this->categoryModel()->findByName($name);

        if ($existing) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['id' => $existing['id'], 'name' => $existing['name'], 'exists' => true]);
            }

            return redirect()->to('/admin/categories')->with('errors', ['A category with this name already exists.']);
        }

        $slug = $this->categoryModel()->uniqueSlug(url_title($name, '-', true));
        $this->categoryModel()->insert([
            'name'       => $name,
            'slug'       => $slug,
            'is_active'  => 1,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
            'description'=> trim((string) $this->request->getPost('description')),
        ]);

        $newId = (int) $this->categoryModel()->getInsertID();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['id' => $newId, 'name' => $name]);
        }

        session()->setFlashdata('success', 'Category "' . $name . '" created.');

        return redirect()->to('/admin/categories');
    }

    public function updateCategory(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $cat = $this->categoryModel()->find($id);

        if (! $cat) {
            return redirect()->to('/admin/categories')->with('errors', ['Category not found.']);
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            return redirect()->to('/admin/categories')->with('errors', ['Category name is required.']);
        }

        $slug = $this->categoryModel()->uniqueSlug(url_title($name, '-', true), $id);

        $oldName = (string) ($cat['name'] ?? '');

        $this->categoryModel()->update($id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => trim((string) $this->request->getPost('description')),
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_active'   => $this->request->getPost('is_active') === '0' ? 0 : 1,
        ]);

        // Sync products bound by FK.
        $this->products()->where('category_id', $id)->set(['category' => $name])->update();

        // Also sync text-only linked products (pre-migration or bulk-imported rows with no category_id).
        if ($oldName !== '' && $oldName !== $name) {
            $this->products()
                ->where('category_id', null)
                ->where('category', $oldName)
                ->set(['category' => $name])
                ->update();
        }

        session()->setFlashdata('success', 'Category updated. Product display names have been re-synced.');

        return redirect()->to('/admin/categories');
    }

    public function deleteCategory(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $cat = $this->categoryModel()->find($id);

        if (! $cat) {
            return redirect()->to('/admin/categories')->with('errors', ['Category not found.']);
        }

        // Detach products rather than blocking deletion
        $this->products()->where('category_id', $id)->set(['category_id' => null])->update();

        $this->categoryModel()->delete($id);
        session()->setFlashdata('success', 'Category deleted. Products using it have been detached.');

        return redirect()->to('/admin/categories');
    }

    /* ── SKU suggestion (AJAX) ───────────────────────────────── */

    public function suggestSku()
    {
        if (! $this->isAuthenticated()) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $categoryId = (int) $this->request->getGet('category_id');
        $catName    = '';

        if ($categoryId > 0) {
            $cat     = $this->categoryModel()->find($categoryId);
            $catName = $cat ? (string) $cat['name'] : '';
        }

        $sku = $this->products()->nextSkuForPrefix($catName ?: 'GEN');

        return $this->response->setJSON(['sku' => $sku]);
    }

    /* ── Products ──────────────────────────────────────────────── */

    public function newProduct()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        return view('admin/product-form', [
            'product'    => null,
            'categories' => $this->loadCategories(),
            'errors'     => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function createProduct()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $payload = $this->validatedPayload();

        if ($payload === null) {
            return redirect()->to('/admin/products/new')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->products()->insert($payload);
        session()->setFlashdata('success', 'Product created.');

        return redirect()->to('/admin/products');
    }

    public function editProduct(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $product = $this->products()->find($id);

        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Resolve category_id from the category string for products that pre-date
        // the categories migration (category_id is null but category string exists).
        if (empty($product['category_id']) && ! empty($product['category'])) {
            try {
                $cat = $this->categoryModel()->findByName((string) $product['category']);
                if ($cat) {
                    $product['category_id'] = (int) $cat['id'];
                }
            } catch (\Throwable $e) {
                // categories table not migrated yet — leave category_id null
            }
        }

        return view('admin/product-form', [
            'product'    => $product,
            'categories' => $this->loadCategories(),
            'errors'     => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function updateProduct(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $product = $this->products()->find($id);
        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $payload = $this->validatedPayload($id);

        if ($payload === null) {
            return redirect()->to('/admin/products/' . $id . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->products()->update($id, $payload);
        session()->setFlashdata('success', 'Product updated.');

        return redirect()->to('/admin/products');
    }

    public function deleteProduct(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $product = $this->products()->find($id);
        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->products()->delete($id);
        session()->setFlashdata('success', 'Product deleted.');

        return redirect()->to('/admin/products');
    }

    public function bulkProducts()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        return view('admin/bulk-products', [
            'errors' => session()->getFlashdata('errors') ?? [],
            'importSummary' => session()->getFlashdata('importSummary'),
        ]);
    }

    public function importProducts()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $file = $this->request->getFile('spreadsheet');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return redirect()->to('/admin/products/bulk')->with('errors', ['Please upload a valid CSV or XLSX file.']);
        }

        $extension = strtolower((string) $file->getExtension());
        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            return redirect()->to('/admin/products/bulk')->with('errors', ['Unsupported file type. Upload a CSV or XLSX spreadsheet.']);
        }

        $uploadDirectory = WRITEPATH . 'uploads';
        if (! is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0775, true);
        }

        $tempPath = WRITEPATH . 'uploads/' . $file->getRandomName();
        $file->move(dirname($tempPath), basename($tempPath));

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $rows    = $this->spreadsheetReader()->read($tempPath);
            $summary = $this->importProductRows($rows);
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            return redirect()->to('/admin/products/bulk')->with('errors', [$exception->getMessage()]);
        } finally {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }

        session()->setFlashdata('importSummary', $summary);
        session()->setFlashdata('success', 'Bulk import completed.');

        return redirect()->to('/admin');
    }

    public function downloadProductTemplate()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $headers = [
            'name',
            'slug',
            'sku',
            'category',
            'short_description',
            'description',
            'image_url',
            'price_label',
            'sort_order',
            'is_active',
        ];

        $sampleRows = [
            [
                'Hydraulic Pump Service Kit',
                'hydraulic-pump-service-kit',
                'BSAS-HP-001',
                'Service Kits',
                'Seal, bearing, and wear-part kit for high-duty hydraulic pump overhauls.',
                'Prepared for mining and drilling duty cycles, this kit consolidates essential overhaul components.',
                '/assets/images/sparePart.webp',
                'Quote on request',
                '10',
                '1',
            ],
            [
                'Feed Beam Wear Pad Set',
                'feed-beam-wear-pad-set',
                'BSAS-FB-014',
                'Spare Parts',
                'Precision-machined wear pads for drilling rig feed beam stability and service life.',
                'Designed to reduce play and improve feed guidance in demanding drill rig operations.',
                '/assets/images/mpr-rig.webp',
                'Fast dispatch',
                '20',
                '1',
            ],
        ];

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="bsas-product-import-template.csv"')
            ->setBody($content);
    }

    public function exportProducts()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $products = $this->products()->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();
        $headers = [
            'name',
            'slug',
            'sku',
            'category',
            'short_description',
            'description',
            'image_url',
            'price_label',
            'sort_order',
            'is_active',
        ];

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $headers);
        foreach ($products as $product) {
            fputcsv($handle, [
                $product['name'],
                $product['slug'],
                $product['sku'],
                $product['category'],
                $product['short_description'],
                $product['description'],
                $product['image_url'],
                $product['price_label'],
                (string) $product['sort_order'],
                (string) $product['is_active'],
            ]);
        }
        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="bsas-products-export.csv"')
            ->setBody($content);
    }

    private function validatedPayload(?int $ignoreId = null): ?array
    {
        $rules = [
            'name'              => 'required|min_length[3]',
            'sku'               => 'permit_empty|max_length[80]',
            'short_description' => 'permit_empty|max_length[2000]',
            'description'       => 'permit_empty',
            'image_url'         => 'permit_empty|max_length[255]',
            'price_label'       => 'permit_empty|max_length[80]',
            'sort_order'        => 'permit_empty|integer',
            'is_active'         => 'permit_empty|in_list[0,1]',
            'stock_status'      => 'permit_empty|in_list[in_stock,made_to_order,out_of_stock]',
            'lead_time'         => 'permit_empty|max_length[60]',
            'min_order_qty'     => 'permit_empty|integer|greater_than[0]',
            'weight'            => 'permit_empty|max_length[60]',
            'dimensions'        => 'permit_empty|max_length[100]',
            'material'          => 'permit_empty|max_length[100]',
            'compatibility'     => 'permit_empty',
            'datasheet_url'     => 'permit_empty|max_length[500]',
            'is_featured'       => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return null;
        }

        $name = trim((string) $this->request->getPost('name'));
        $slug = trim((string) $this->request->getPost('slug'));
        $slug = $slug !== '' ? url_title($slug, '-', true) : url_title($name, '-', true);
        $slug = $this->uniqueSlug($slug, $ignoreId);
        $sku  = trim((string) $this->request->getPost('sku'));

        // --- Category resolution ---
        // Priority 1: dropdown selection (category_id from categories table).
        // Priority 2: plain text fallback (pre-migration or no categories yet).
        $categoryId   = (int) $this->request->getPost('category_id');
        $categoryName = '';
        $resolvedId   = null;

        if ($categoryId > 0) {
            try {
                $cat = $this->categoryModel()->find($categoryId);
                if ($cat) {
                    $categoryName = (string) $cat['name'];
                    $resolvedId   = $categoryId;
                }
            } catch (\Throwable $e) {
                // categories table not yet migrated — fall through to text fallback
            }
        }

        if ($categoryName === '') {
            $categoryName = trim((string) $this->request->getPost('category'));
        }

        if ($categoryName === '') {
            $this->validator->setError('category_id', 'Please select or enter a product category.');

            return null;
        }

        // File upload takes priority; fall back to the URL text field.
        // If a file was supplied but rejected (wrong type), bail so the error is shown.
        $imageUrl = $this->handleImageUpload('image_file');
        if ($imageUrl === null && $this->validator->getError('image_file') !== '') {
            return null;
        }
        $imageUrl = $imageUrl ?? trim((string) $this->request->getPost('image_url'));

        // Base payload — always safe, even before migrations run.
        $payload = [
            'name'              => $name,
            'slug'              => $slug,
            'sku'               => $sku,
            'category'          => $categoryName,
            'short_description' => trim((string) $this->request->getPost('short_description')),
            'description'       => trim((string) $this->request->getPost('description')),
            'image_url'         => $imageUrl,
            'price_label'       => trim((string) $this->request->getPost('price_label')),
            'sort_order'        => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_active'         => $this->request->getPost('is_active') === '0' ? 0 : 1,
        ];

        // Only write category_id when the column exists (migration has been run).
        if ($resolvedId !== null && $this->columnExists('products', 'category_id')) {
            $payload['category_id'] = $resolvedId;
        }

        // Advanced fields — guarded so they're silently skipped if the migration hasn't run yet.
        $specsRaw  = trim((string) $this->request->getPost('specifications'));
        $specsJson = null;
        if ($specsRaw !== '') {
            $decoded = json_decode($specsRaw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $specsJson = json_encode($decoded);
            }
        }
        $advanced = [
            'stock_status'   => $this->request->getPost('stock_status') ?: 'in_stock',
            'lead_time'      => trim((string) $this->request->getPost('lead_time')),
            'min_order_qty'  => max(1, (int) ($this->request->getPost('min_order_qty') ?: 1)),
            'weight'         => trim((string) $this->request->getPost('weight')),
            'dimensions'     => trim((string) $this->request->getPost('dimensions')),
            'material'       => trim((string) $this->request->getPost('material')),
            'compatibility'  => trim((string) $this->request->getPost('compatibility')),
            'datasheet_url'  => trim((string) $this->request->getPost('datasheet_url')),
            'specifications' => $specsJson,
            'is_featured'    => $this->request->getPost('is_featured') === '1' ? 1 : 0,
        ];
        foreach ($advanced as $col => $value) {
            if ($this->columnExists('products', $col)) {
                $payload[$col] = $value;
            }
        }

        return $payload;
    }

    /** Check whether a column exists in a table without throwing if it does not. */
    private function columnExists(string $table, string $column): bool
    {
        try {
            return \Config\Database::connect()->fieldExists($column, $table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Load the categories list safely.
     * Returns an empty array if the categories table has not been migrated yet.
     */
    private function loadCategories(): array
    {
        try {
            return $this->categoryModel()->active()->findAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $suffix = 1;

        while (true) {
            $existing = $this->products()->where('slug', $slug)->first();
            if (! $existing || ($ignoreId !== null && (int) $existing['id'] === $ignoreId)) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }

    private function validatedGalleryAlbumPayload(?int $ignoreId = null): ?array
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[160]',
            'eyebrow' => 'permit_empty|max_length[120]',
            'location' => 'permit_empty|max_length[180]',
            'summary' => 'permit_empty',
            'intro_text' => 'permit_empty',
            'cover_image_url' => 'permit_empty|max_length[255]',
            'hero_image_url' => 'permit_empty|max_length[255]',
            'event_date' => 'permit_empty|valid_date[Y-m-d]',
            'sort_order' => 'permit_empty|integer',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return null;
        }

        $name = trim((string) $this->request->getPost('name'));
        $slug = trim((string) $this->request->getPost('slug'));
        $slug = $slug !== '' ? url_title($slug, '-', true) : url_title($name, '-', true);
        $slug = $this->galleryAlbumModel()->uniqueSlug($slug, $ignoreId);

        $coverImageUrl = $this->handleImageUpload('cover_image_file');
        if ($coverImageUrl === null && $this->validator->getError('cover_image_file') !== '') {
            return null;
        }
        $coverImageUrl = $coverImageUrl ?? trim((string) $this->request->getPost('cover_image_url'));

        $heroImageUrl = $this->handleImageUpload('hero_image_file');
        if ($heroImageUrl === null && $this->validator->getError('hero_image_file') !== '') {
            return null;
        }
        $heroImageUrl = $heroImageUrl ?? trim((string) $this->request->getPost('hero_image_url'));

        return [
            'name' => $name,
            'slug' => $slug,
            'eyebrow' => trim((string) $this->request->getPost('eyebrow')),
            'location' => trim((string) $this->request->getPost('location')),
            'summary' => trim((string) $this->request->getPost('summary')),
            'intro_text' => trim((string) $this->request->getPost('intro_text')),
            'cover_image_url' => $coverImageUrl,
            'hero_image_url' => $heroImageUrl,
            'event_date' => trim((string) $this->request->getPost('event_date')) ?: null,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_active' => $this->request->getPost('is_active') === '0' ? 0 : 1,
        ];
    }

    private function validatedGalleryItemPayload(int $albumId): ?array
    {
        $rules = [
            'title' => 'required|min_length[2]|max_length[160]',
            'caption' => 'permit_empty',
            'image_url' => 'permit_empty|max_length[255]',
            'badge_label' => 'permit_empty|max_length[120]',
            'display_style' => 'permit_empty|in_list[standard,wide,tall]',
            'sort_order' => 'permit_empty|integer',
            'is_featured' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return null;
        }

        $imageUrl = $this->handleImageUpload('image_file');
        if ($imageUrl === null && $this->validator->getError('image_file') !== '') {
            return null;
        }
        $imageUrl = $imageUrl ?? trim((string) $this->request->getPost('image_url'));

        if ($imageUrl === '') {
            $this->validator->setError('image_url', 'Please upload an image file or provide an image URL.');
            return null;
        }

        return [
            'album_id' => $albumId,
            'title' => trim((string) $this->request->getPost('title')),
            'caption' => trim((string) $this->request->getPost('caption')),
            'image_url' => $imageUrl,
            'badge_label' => trim((string) $this->request->getPost('badge_label')),
            'display_style' => trim((string) $this->request->getPost('display_style')) ?: 'standard',
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_featured' => $this->request->getPost('is_featured') === '1' ? 1 : 0,
            'is_active' => $this->request->getPost('is_active') === '0' ? 0 : 1,
        ];
    }

    private function handleImageUpload(string $fieldName): ?string
    {
        $file = $this->request->getFile($fieldName);

        if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext     = strtolower((string) $file->getExtension());

        if (! in_array($ext, $allowed, true)) {
            $this->validator->setError(
                $fieldName,
                'Invalid file type ".' . $ext . '". Allowed types: jpg, jpeg, png, webp, gif.'
            );
            return null;
        }

        $uploadDir = FCPATH . 'uploads';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        return '/uploads/' . $newName;
    }

    private function galleryItemCounts(): array
    {
        try {
            $rows = \Config\Database::connect()->query(
                'SELECT album_id, COUNT(*) AS cnt FROM gallery_items GROUP BY album_id'
            )->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['album_id']] = (int) $row['cnt'];
        }

        return $counts;
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

    private function categoryModel(): CategoryModel
    {
        return new CategoryModel();
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

    private function spreadsheetReader(): SimpleSpreadsheetReader
    {
        return new SimpleSpreadsheetReader();
    }

    private function dashboardStats(): array
    {
        $db = \Config\Database::connect();

        $pRow = $db->query(
            'SELECT COUNT(*) AS total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS hidden
             FROM products'
        )->getRowArray();

        $qRow = $db->query(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN request_type = 'cart' THEN 1 ELSE 0 END) AS cart
             FROM quote_requests"
        )->getRowArray();

        $brochureCount = (int) ($db->query(
            'SELECT COUNT(*) AS cnt FROM brochure_leads'
        )->getRowArray()['cnt'] ?? 0);

        return [
            'totalProducts'  => (int) ($pRow['total']  ?? 0),
            'activeProducts' => (int) ($pRow['active'] ?? 0),
            'hiddenProducts' => (int) ($pRow['hidden'] ?? 0),
            'quoteRequests'  => (int) ($qRow['total']  ?? 0),
            'cartQuotes'     => (int) ($qRow['cart']   ?? 0),
            'brochureLeads'  => $brochureCount,
        ];
    }

    private function groupQuoteItemsByRequest(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $grouped[(int) $item['quote_request_id']][] = $item;
        }

        return $grouped;
    }

    private function catalogAudit(): array
    {
        $db  = \Config\Database::connect();
        $row = $db->query(
            "SELECT
                SUM(CASE WHEN TRIM(COALESCE(sku,''))         = '' THEN 1 ELSE 0 END) AS missing_sku,
                SUM(CASE WHEN TRIM(COALESCE(image_url,''))   = '' THEN 1 ELSE 0 END) AS missing_image,
                SUM(CASE WHEN TRIM(COALESCE(short_description,'')) != ''
                           OR  TRIM(COALESCE(description,''))       != ''
                         THEN 1 ELSE 0 END) AS with_desc
             FROM products"
        )->getRowArray();

        return [
            'missingSku'       => (int) ($row['missing_sku']    ?? 0),
            'missingImage'     => (int) ($row['missing_image']  ?? 0),
            'withDescriptions' => (int) ($row['with_desc']      ?? 0),
        ];
    }

    private function productCategoryBreakdown(): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->query(
            "SELECT COALESCE(NULLIF(TRIM(category),''), 'Uncategorized') AS category,
                    COUNT(*) AS count
             FROM products
             GROUP BY COALESCE(NULLIF(TRIM(category),''), 'Uncategorized')
             ORDER BY count DESC
             LIMIT 6"
        )->getResultArray();

        return array_map(
            static fn($r) => ['category' => $r['category'], 'count' => (int) $r['count']],
            $rows
        );
    }

    private function quoteRequestBreakdown(): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->query(
            "SELECT LOWER(TRIM(COALESCE(request_type,''))) AS type, COUNT(*) AS cnt
             FROM quote_requests
             GROUP BY LOWER(TRIM(COALESCE(request_type,'')))"
        )->getResultArray();

        $counts = ['product' => 0, 'cart' => 0, 'support' => 0, 'other' => 0];
        foreach ($rows as $row) {
            $type = (string) $row['type'];
            if (array_key_exists($type, $counts)) {
                $counts[$type] = (int) $row['cnt'];
            } else {
                $counts['other'] += (int) $row['cnt'];
            }
        }

        return $counts;
    }

    private function performanceSnapshot(): array
    {
        $db            = \Config\Database::connect();
        $todayStr      = date('Y-m-d');
        $currentStart  = date('Y-m-d', strtotime('-6 days'));
        $previousStart = date('Y-m-d', strtotime('-13 days'));
        $previousEnd   = date('Y-m-d', strtotime('-7 days'));

        $qRow = $db->query(
            'SELECT SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) AS cur,
                    SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) AS prev
             FROM quote_requests WHERE created_at >= ?',
            [$currentStart, $todayStr, $previousStart, $previousEnd, $previousStart]
        )->getRowArray();

        $bRow = $db->query(
            'SELECT SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) AS cur,
                    SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 ELSE 0 END) AS prev
             FROM brochure_leads WHERE created_at >= ?',
            [$currentStart, $todayStr, $previousStart, $previousEnd, $previousStart]
        )->getRowArray();

        $recentProducts = (int) ($db->query(
            'SELECT COUNT(*) AS cnt FROM products WHERE DATE(created_at) BETWEEN ? AND ?',
            [$currentStart, $todayStr]
        )->getRowArray()['cnt'] ?? 0);

        $currentQuotes    = (int) ($qRow['cur']  ?? 0);
        $previousQuotes   = (int) ($qRow['prev'] ?? 0);
        $currentBrochures  = (int) ($bRow['cur']  ?? 0);
        $previousBrochures = (int) ($bRow['prev'] ?? 0);

        return [
            'last7Quotes'           => $currentQuotes,
            'quoteChangePercent'    => $this->percentageChange($previousQuotes, $currentQuotes),
            'last7Brochures'        => $currentBrochures,
            'brochureChangePercent' => $this->percentageChange($previousBrochures, $currentBrochures),
            'last7Products'         => $recentProducts,
            'healthScore'           => $this->catalogHealthScore(),
        ];
    }

    private function activityTimeline(int $days = 14): array
    {
        $db    = \Config\Database::connect();
        $today = new \DateTimeImmutable('today');
        $start = $today->modify('-' . ($days - 1) . ' days');
        $since = $start->format('Y-m-d');

        $timeline = [];
        for ($i = 0; $i < $days; $i++) {
            $date  = $start->modify('+' . $i . ' days');
            $key   = $date->format('Y-m-d');
            $timeline[$key] = [
                'date'      => $key,
                'label'     => $date->format('d M'),
                'quotes'    => 0,
                'brochures' => 0,
                'products'  => 0,
            ];
        }

        $fill = static function (array &$tl, array $rows, string $bucket): void {
            foreach ($rows as $row) {
                if (isset($tl[$row['d']])) {
                    $tl[$row['d']][$bucket] = (int) $row['cnt'];
                }
            }
        };

        $fill($timeline, $db->query(
            'SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM quote_requests WHERE created_at >= ? GROUP BY DATE(created_at)',
            [$since]
        )->getResultArray(), 'quotes');

        $fill($timeline, $db->query(
            'SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM brochure_leads WHERE created_at >= ? GROUP BY DATE(created_at)',
            [$since]
        )->getResultArray(), 'brochures');

        $fill($timeline, $db->query(
            'SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM products WHERE created_at >= ? GROUP BY DATE(created_at)',
            [$since]
        )->getResultArray(), 'products');

        return array_values($timeline);
    }

    private function topRequestedProducts(int $limit = 5): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->query(
            "SELECT COALESCE(NULLIF(TRIM(product_name),''), 'Unnamed Product') AS name,
                    COUNT(*) AS requests,
                    SUM(quantity) AS quantity
             FROM quote_request_items
             GROUP BY COALESCE(NULLIF(TRIM(product_name),''), 'Unnamed Product')
             ORDER BY quantity DESC, requests DESC, name ASC
             LIMIT ?",
            [$limit]
        )->getResultArray();

        return array_map(
            static fn($r) => [
                'name'     => $r['name'],
                'requests' => (int) $r['requests'],
                'quantity' => (int) $r['quantity'],
            ],
            $rows
        );
    }

    private function sourcePageBreakdown(int $limit = 5): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->query(
            "SELECT COALESCE(NULLIF(TRIM(source_page),''), 'Direct submission') AS source,
                    COUNT(*) AS cnt
             FROM quote_requests
             GROUP BY COALESCE(NULLIF(TRIM(source_page),''), 'Direct submission')
             ORDER BY cnt DESC
             LIMIT ?",
            [$limit]
        )->getResultArray();

        return array_map(
            static fn($r) => ['source' => $r['source'], 'count' => (int) $r['cnt']],
            $rows
        );
    }

    private function catalogHealthScore(): int
    {
        $db  = \Config\Database::connect();
        $row = $db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN TRIM(COALESCE(sku,''))         != '' THEN 1 ELSE 0 END) AS has_sku,
                SUM(CASE WHEN TRIM(COALESCE(image_url,''))   != '' THEN 1 ELSE 0 END) AS has_image,
                SUM(CASE WHEN TRIM(COALESCE(short_description,'')) != ''
                           OR  TRIM(COALESCE(description,''))       != ''
                         THEN 1 ELSE 0 END) AS has_desc
             FROM products"
        )->getRowArray();

        $total = (int) ($row['total'] ?? 0);
        if ($total === 0) {
            return 0;
        }

        $points = (int) ($row['active']    ?? 0)
                + (int) ($row['has_sku']   ?? 0)
                + (int) ($row['has_image'] ?? 0)
                + (int) ($row['has_desc']  ?? 0);

        return (int) round(($points / ($total * 4)) * 100);
    }

    private function percentageChange(int $previous, int $current): int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    private function importProductRows(array $rows): array
    {
        if ($rows === []) {
            throw new \RuntimeException('The uploaded spreadsheet is empty.');
        }

        $header = array_map(static fn($value): string => strtolower(trim((string) $value)), array_shift($rows));
        $required = ['name', 'category'];

        foreach ($required as $requiredColumn) {
            if (! in_array($requiredColumn, $header, true)) {
                throw new \RuntimeException('Missing required column: ' . $requiredColumn);
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $lineNumber = $index + 2;
            $data = $this->mapImportRow($header, $row);

            if ($data['name'] === '' || $data['category'] === '') {
                $skipped++;
                $errors[] = 'Row ' . $lineNumber . ': name and category are required.';
                continue;
            }

            $payload = $this->sanitizeImportedPayload($data);
            $existing = $this->findExistingProductForImport($payload);

            if ($existing) {
                $payload['slug'] = $this->resolveImportSlug($payload['slug'], (int) $existing['id'], $payload['name']);
                $this->products()->update((int) $existing['id'], $payload);
                $updated++;
                continue;
            }

            $payload['slug'] = $this->resolveImportSlug($payload['slug'], null, $payload['name']);
            $this->products()->insert($payload);
            $created++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function mapImportRow(array $header, array $row): array
    {
        $mapped = [];

        foreach ($header as $index => $column) {
            $mapped[$column] = trim((string) ($row[$index] ?? ''));
        }

        return $mapped;
    }

    private function sanitizeImportedPayload(array $data): array
    {
        $categoryName = trim((string) ($data['category'] ?? ''));
        $categoryId   = null;

        if ($categoryName !== '') {
            $cat        = $this->categoryModel()->findOrCreate($categoryName);
            $categoryId = $cat ? (int) $cat['id'] : null;
        }

        return [
            'name'              => $data['name'] ?? '',
            'slug'              => trim((string) ($data['slug'] ?? '')),
            'sku'               => trim((string) ($data['sku'] ?? '')),
            'category'          => $categoryName,
            'category_id'       => $categoryId,
            'short_description' => trim((string) ($data['short_description'] ?? '')),
            'description'       => trim((string) ($data['description'] ?? '')),
            'image_url'         => trim((string) ($data['image_url'] ?? '')),
            'price_label'       => trim((string) ($data['price_label'] ?? '')),
            'sort_order'        => is_numeric($data['sort_order'] ?? null) ? (int) $data['sort_order'] : 0,
            'is_active'         => in_array((string) ($data['is_active'] ?? '1'), ['0', 'false', 'FALSE'], true) ? 0 : 1,
        ];
    }

    private function findExistingProductForImport(array $payload): ?array
    {
        if ($payload['sku'] !== '') {
            $existing = $this->products()->where('sku', $payload['sku'])->first();
            if ($existing) {
                return $existing;
            }
        }

        if ($payload['slug'] !== '') {
            $existing = $this->products()->where('slug', url_title($payload['slug'], '-', true))->first();
            if ($existing) {
                return $existing;
            }
        }

        return $this->products()->where('name', $payload['name'])->first();
    }

    private function resolveImportSlug(string $slug, ?int $ignoreId, string $name): string
    {
        $base = $slug !== '' ? $slug : $name;
        $base = url_title($base, '-', true);

        return $this->uniqueSlug($base, $ignoreId);
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

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
