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
$routes->post('support/quote', 'Website::supportQuote');
$routes->post('product-quote/(:segment)', 'Website::productQuote/$1');
$routes->post('cart/request-quote', 'Website::cartQuote');
$routes->post('brochure/request', 'Website::brochureRequest');
$routes->get('brochure/download', 'Website::downloadBrochure');

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
$routes->get('admin/leads', 'AdminController::leads');
$routes->get('admin/categories', 'AdminController::categories');
$routes->post('admin/categories', 'AdminController::createCategory');
$routes->post('admin/categories/(:num)', 'AdminController::updateCategory/$1');
$routes->post('admin/categories/(:num)/delete', 'AdminController::deleteCategory/$1');
$routes->get('admin/sku/suggest', 'AdminController::suggestSku');
