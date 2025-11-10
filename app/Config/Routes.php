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
// Generic dashboard route — redirects to manager or staff dashboard based on session
$routes->get('dashboard', 'Dashboard::index');
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
$routes->get('dashboard/manager/stockmovement', function () {
    return view('dashboard/manager/stockmovement');
});

$routes->get('dashboard/manager/workforcemanagement', function () {
    return view('dashboard/manager/workforcemanagement');
});

$routes->get('dashboard/manager/workforce', 'WorkforceController::index');
$routes->get('api/workforce', 'WorkforceController::listUsers');
$routes->post('api/workforce', 'WorkforceController::create');
$routes->put('api/workforce/(:num)', 'WorkforceController::update/$1');
$routes->delete('api/workforce/(:num)', 'WorkforceController::delete/$1');

$routes->get('dashboard/staff/barcode', function () {
    return view('dashboard/staff/barcodescan');
});

// Warehouses
$routes->get('dashboard/manager/warehouses', 'WarehouseController::index');
$routes->get('api/warehouse/list', 'WarehouseController::list');
$routes->post('api/warehouse/create', 'WarehouseController::create');

// Transfers
$routes->post('api/transfer/create', 'TransferController::create');

// Barcode scan API
$routes->post('api/barcode/scan', 'BarcodeController::scan');

// Invoices
$routes->get('api/invoice/list', 'InvoiceController::list');
$routes->post('api/invoice/create', 'InvoiceController::create');


// IT Administrator Routes
$routes->get('dashboard', 'Dashboard::admin');
$routes->get('user-management', 'Admin::userManagement');
$routes->get('access-control', 'Admin::accessControl');
$routes->get('system-logs', 'Admin::systemLogs');
$routes->get('backup-recovery', 'Admin::backupRecovery');
$routes->get('system-configuration', 'Admin::systemConfiguration');
$routes->get('reports', 'Admin::reports');
$routes->get('notifications', 'Admin::notifications');
$routes->get('profile', 'Admin::profile');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
