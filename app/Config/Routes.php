<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Website::home');
$routes->get('about-us', 'Website::about');
$routes->get('spare-parts', 'Website::spareParts');
$routes->get('equipments', 'Website::equipment');
$routes->get('services', 'Website::services');
$routes->get('e-shop', 'Website::shop');
$routes->get('e-shop/product/(:segment)', 'Website::product/$1');
$routes->get('cart', 'Website::cart');
$routes->post('cart/add', 'Website::addToCart');
$routes->post('cart/update', 'Website::updateCart');
$routes->post('cart/remove', 'Website::removeFromCart');
$routes->get('3r', 'Website::threeR');
$routes->get('support', 'Website::support');
$routes->get('faq', 'Website::faq');
$routes->get('privacy-policy', 'Website::privacy');
$routes->get('gallery', 'Website::gallery');
$routes->get('gallery/(:segment)', 'Website::galleryAlbum/$1');
$routes->post('support/quote', 'Website::supportQuote');
$routes->post('product-quote/(:segment)', 'Website::productQuote/$1');
$routes->post('cart/request-quote', 'Website::cartQuote');
$routes->post('brochure/request', 'Website::brochureRequest');
$routes->post('quick-quote', 'Website::quickQuote');
$routes->get('sitemap.xml', 'Website::sitemap');
$routes->get('brochure/download', 'Website::downloadBrochure');

// ── Customer auth ──
$routes->get('register', 'Customer\AuthController::showRegister');
$routes->post('register', 'Customer\AuthController::register');
$routes->get('login', 'Customer\AuthController::showLogin');
$routes->post('login', 'Customer\AuthController::login');
$routes->get('logout', 'Customer\AuthController::logout');
$routes->get('verify-email/(:segment)', 'Customer\AuthController::verifyEmail/$1');
$routes->get('forgot-password', 'Customer\AuthController::showForgotPassword');
$routes->post('forgot-password', 'Customer\AuthController::forgotPassword');
$routes->get('reset-password/(:segment)', 'Customer\AuthController::showResetPassword/$1');
$routes->post('reset-password', 'Customer\AuthController::resetPassword');
$routes->get('auth/google', 'Customer\AuthController::googleRedirect');
$routes->get('auth/google/callback', 'Customer\AuthController::googleCallback');
$routes->post('otp/request', 'Customer\AuthController::requestOtp');
$routes->post('otp/verify', 'Customer\AuthController::verifyOtp');

// ── Checkout (customerAuth-filtered) ──
$routes->get('checkout/address', 'OrderController::checkoutAddress', ['filter' => 'customerAuth']);
$routes->post('checkout/address', 'OrderController::checkoutSaveAddress', ['filter' => 'customerAuth']);
$routes->get('checkout/review', 'OrderController::checkoutReview', ['filter' => 'customerAuth']);
$routes->post('checkout/place', 'OrderController::placeOrder', ['filter' => 'customerAuth']);
$routes->get('checkout/confirmation/(:segment)', 'OrderController::orderConfirmation/$1', ['filter' => 'customerAuth']);

// ── Customer account (customerAuth-filtered) ──
$routes->get('account', 'Customer\AccountController::dashboard', ['filter' => 'customerAuth']);
$routes->get('account/orders', 'Customer\AccountController::orders', ['filter' => 'customerAuth']);
$routes->get('account/orders/(:segment)', 'Customer\AccountController::orderDetail/$1', ['filter' => 'customerAuth']);
$routes->get('account/addresses', 'Customer\AccountController::addresses', ['filter' => 'customerAuth']);
$routes->post('account/addresses', 'Customer\AccountController::addAddress', ['filter' => 'customerAuth']);
$routes->post('account/addresses/(:num)', 'Customer\AccountController::updateAddress/$1', ['filter' => 'customerAuth']);
$routes->post('account/addresses/(:num)/delete', 'Customer\AccountController::deleteAddress/$1', ['filter' => 'customerAuth']);
$routes->get('account/profile', 'Customer\AccountController::profile', ['filter' => 'customerAuth']);
$routes->post('account/profile', 'Customer\AccountController::updateProfile', ['filter' => 'customerAuth']);
$routes->post('account/password', 'Customer\AccountController::updatePassword', ['filter' => 'customerAuth']);

$routes->get('admin/login', 'AdminController::login');
$routes->post('admin/login', 'AdminController::attemptLogin');
$routes->get('admin/logout', 'AdminController::logout');
$routes->get('admin', 'AdminController::index');
$routes->get('admin/products', 'AdminController::productsList');
$routes->get('admin/products/new', 'AdminController::newProduct');
$routes->post('admin/products', 'AdminController::createProduct');
$routes->get('admin/products/(:num)/edit', 'AdminController::editProduct/$1');
$routes->post('admin/products/(:num)', 'AdminController::updateProduct/$1');
$routes->post('admin/products/(:num)/delete', 'AdminController::deleteProduct/$1');
$routes->get('admin/products/bulk', 'AdminController::bulkProducts');
$routes->post('admin/products/bulk', 'AdminController::importProducts');
$routes->get('admin/products/template', 'AdminController::downloadProductTemplate');
$routes->get('admin/products/export', 'AdminController::exportProducts');
$routes->post('admin/products/bulk-action', 'AdminController::bulkAction');
$routes->get('admin/leads', 'AdminController::leads');
$routes->get('admin/categories', 'AdminController::categories');
$routes->post('admin/categories', 'AdminController::createCategory');
$routes->post('admin/categories/(:num)', 'AdminController::updateCategory/$1');
$routes->post('admin/categories/(:num)/delete', 'AdminController::deleteCategory/$1');
$routes->get('admin/vehicles', 'AdminController::vehicles');
$routes->post('admin/vehicles', 'AdminController::createVehicle');
$routes->post('admin/vehicles/(:num)', 'AdminController::updateVehicle/$1');
$routes->post('admin/vehicles/(:num)/delete', 'AdminController::deleteVehicle/$1');
$routes->get('admin/oems', 'AdminController::oems');
$routes->post('admin/oems', 'AdminController::createOem');
$routes->post('admin/oems/(:num)', 'AdminController::updateOem/$1');
$routes->post('admin/oems/(:num)/delete', 'AdminController::deleteOem/$1');
$routes->get('admin/divisions', 'AdminController::divisions');
$routes->post('admin/divisions', 'AdminController::createDivision');
$routes->post('admin/divisions/(:num)', 'AdminController::updateDivision/$1');
$routes->post('admin/divisions/(:num)/delete', 'AdminController::deleteDivision/$1');
$routes->get('admin/labels', 'AdminController::labels');
$routes->post('admin/labels', 'AdminController::createLabel');
$routes->post('admin/labels/(:num)', 'AdminController::updateLabel/$1');
$routes->post('admin/labels/(:num)/delete', 'AdminController::deleteLabel/$1');
$routes->get('admin/gallery', 'AdminController::galleryAlbums');
$routes->get('admin/gallery/new', 'AdminController::newGalleryAlbum');
$routes->post('admin/gallery', 'AdminController::createGalleryAlbum');
$routes->get('admin/gallery/(:num)/edit', 'AdminController::editGalleryAlbum/$1');
$routes->post('admin/gallery/(:num)', 'AdminController::updateGalleryAlbum/$1');
$routes->post('admin/gallery/(:num)/delete', 'AdminController::deleteGalleryAlbum/$1');
$routes->get('admin/gallery/(:num)/items', 'AdminController::galleryItems/$1');
$routes->post('admin/gallery/(:num)/items', 'AdminController::createGalleryItem/$1');
$routes->post('admin/gallery/items/(:num)', 'AdminController::updateGalleryItem/$1');
$routes->post('admin/gallery/items/(:num)/delete', 'AdminController::deleteGalleryItem/$1');
$routes->get('admin/sku/suggest', 'AdminController::suggestSku');

$routes->get('admin/orders', 'Admin\OrderController::index');
$routes->get('admin/orders/(:segment)', 'Admin\OrderController::show/$1');
$routes->post('admin/orders/(:segment)/status', 'Admin\OrderController::updateStatus/$1');
$routes->post('admin/orders/(:segment)/tracking', 'Admin\OrderController::updateTracking/$1');
