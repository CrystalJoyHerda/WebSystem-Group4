<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
	require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// Application Routes
$routes->get('/', 'Home::index');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->get('dashboard/manager', 'Dashboard::manager');
$routes->get('dashboard/staff', 'Dashboard::staff');
$routes->get('debug/session', 'Debug::session');
$routes->match(['get','post'], 'auth/dbfetch', 'Auth::dbfetch');
// Inventory page for managers
$routes->get('inventory', 'Inventory::index');
// Create new inventory item
$routes->post('inventory/create', 'Inventory::create');
// Delete an inventory item
$routes->post('inventory/delete/(:num)', 'Inventory::delete/$1');
// Update an inventory item
$routes->post('inventory/update/(:num)', 'Inventory::update/$1');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
