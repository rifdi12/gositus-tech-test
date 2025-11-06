<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home redirect to dashboard
$routes->get('/', function() {
    return redirect()->to('/dashboard');
});

// Authentication routes
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::loginProcess');
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::registerProcess');
$routes->get('/logout', 'Auth::logout');

// Dashboard routes (require authentication)
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/favorites', 'Dashboard::favorites');
$routes->post('/favorites/toggle', 'Dashboard::toggleFavorite');
$routes->get('/profile', 'Dashboard::profile');

// Books management routes (admin only)
$routes->get('/books/upload', 'Books::upload');
$routes->post('/books/store', 'Books::store');
$routes->get('/books/edit/(:num)', 'Books::edit/$1');
$routes->post('/books/update/(:num)', 'Books::update/$1');
$routes->delete('/books/delete/(:num)', 'Books::delete/$1');
$routes->get('/books/detail/(:num)', 'Books::detail/$1');

// AI Chat routes
$routes->post('/ai/chat', 'AiChat::chat');
$routes->get('/ai/book/(:num)', 'AiChat::bookInfo/$1');
$routes->get('/ai/suggestions/(:num)', 'AiChat::suggestions/$1');
$routes->get('/ai/status', 'AiChat::status');
