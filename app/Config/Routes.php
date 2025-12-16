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
$routes->get('dashboard/viewer', 'Dashboard::viewer');
$routes->get('dashboard/admin', 'Dashboard::admin');
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

// Staff tasks (Warehouse Staff barcode workflow)
$routes->get('api/staff-tasks/pending', 'StaffTaskController::getPendingTasks');
$routes->post('api/staff-tasks/scan-item', 'StaffTaskController::scanTaskItem');

// Warehouses
$routes->get('dashboard/manager/warehouses', 'WarehouseController::index');
$routes->get('api/warehouse/list', 'WarehouseController::list');
$routes->post('api/warehouse/create', 'WarehouseController::create');
$routes->get('api/warehouse/analytics', 'WarehouseController::getAnalytics');
$routes->get('warehouses/seed-test-data', 'WarehouseController::seedWarehousesTestData');

// Transfers
$routes->post('api/transfer/create', 'TransferController::create');
$routes->get('api/transfer/history', 'TransferController::getHistory');
$routes->get('api/transfer/pending', 'TransferController::getPending');
$routes->post('api/transfer/approve/(:num)', 'TransferController::approve/$1');

// Barcode scan API
$routes->post('api/barcode/scan', 'BarcodeController::scan');

// Recent scans (staging area for staff barcode scans)
$routes->post('api/recent-scans/add', 'StaffTaskController::addRecentScan');
$routes->get('api/recent-scans/list', 'StaffTaskController::listRecentScans');
$routes->post('api/recent-scans/remove/(:num)', 'StaffTaskController::removeRecentScan/$1');
$routes->post('api/recent-scans/save', 'StaffTaskController::saveAndUpdateScans');

// Invoices
$routes->get('api/invoice/list', 'InvoiceController::list');
$routes->post('api/invoice/create', 'InvoiceController::create');

// Inventory API endpoints
$routes->get('api/inventory/stats', 'Inventory::getStats');
$routes->get('api/inventory/by-warehouse/(:num)', 'Inventory::getByWarehouse/$1');
$routes->get('api/inventory/low-stock', 'Inventory::getLowStock');
$routes->get('api/inventory/all-with-warehouse', 'Inventory::getAllWithWarehouse');
$routes->post('api/inventory/update-stock', 'Inventory::updateStock');

// Test data seeder for development
$routes->get('seed-test-data', 'TestDataController::seedTestData');


// IT Administrator Routes
$routes->group('', ['filter' => 'itAdmin'], static function ($routes) {
	// IT Administrator pages
	$routes->get('admin', 'Admin::index');
	$routes->get('admin/user-management', 'Admin::userManagement');
	$routes->get('admin/access-control', 'Admin::accessControl');
	$routes->get('system-logs', 'Admin::systemLogs');
	$routes->get('backup-recovery', 'Admin::backupRecovery');
	$routes->get('system-configuration', 'Admin::systemConfiguration');
	$routes->get('reports', 'Admin::reports');
	$routes->get('notifications', 'Admin::notifications');
	$routes->get('profile', 'Admin::profile');

	// IT Administrator APIs
	$routes->get('api/admin/users', 'Admin::listUsers');
	$routes->post('api/admin/users', 'Admin::createUser');
	$routes->put('api/admin/users/(:num)', 'Admin::updateUser/$1');
	$routes->delete('api/admin/users/(:num)', 'Admin::deleteUser/$1');
	$routes->post('api/admin/users/(:num)/reset-password', 'Admin::resetPassword/$1');
	$routes->post('api/admin/users/(:num)/status', 'Admin::setUserStatus/$1');
	$routes->get('api/admin/backups', 'Admin::listBackups');
	$routes->post('api/admin/backups', 'Admin::createBackup');
	$routes->get('api/admin/backups/(:segment)/download', 'Admin::downloadBackup/$1');
	$routes->post('api/admin/backups/restore', 'Admin::restoreBackup');
	$routes->get('api/admin/roles', 'Admin::listRoles');
	$routes->post('api/admin/roles/(:segment)', 'Admin::setRolePermissions/$1');
	$routes->get('api/admin/audit-logs', 'Admin::listAuditLogs');
	$routes->get('api/admin/overview', 'Admin::overview');
	$routes->get('api/admin/system-settings', 'Admin::getSystemSettings');
	$routes->post('api/admin/system-settings', 'Admin::saveSystemSettings');
	$routes->get('api/admin/warehouses', 'Admin::warehouses');
	$routes->post('api/admin/current-warehouse', 'Admin::setCurrentWarehouse');
});

// Tickets API
$routes->get('api/tickets', 'TicketController::index');
$routes->post('api/tickets', 'TicketController::create');
$routes->get('api/tickets/(:num)', 'TicketController::show/$1');
$routes->put('api/tickets/(:num)', 'TicketController::update/$1');
$routes->post('api/tickets/(:num)/assign', 'TicketController::assign/$1');
$routes->post('api/tickets/(:num)/status', 'TicketController::setStatus/$1');
$routes->post('api/tickets/(:num)/comment', 'TicketController::comment/$1');

// Assets API
$routes->get('api/assets', 'AssetController::index');
$routes->post('api/assets', 'AssetController::create');
$routes->put('api/assets/(:num)', 'AssetController::update/$1');
$routes->post('api/assets/(:num)/assign', 'AssetController::assign/$1');
$routes->post('api/assets/(:num)/return', 'AssetController::returnAsset/$1');
$routes->get('api/assets/(:num)/history', 'AssetController::history/$1');

// Jobs/Queue API
$routes->post('api/jobs', 'JobController::enqueue');
$routes->get('api/jobs', 'JobController::index');


// Stock Movement and Staff Task Integration Routes
$routes->post('stockmovements/approveInboundReceipt', 'stockmovements::approveInboundReceipt');
$routes->post('stockmovements/approveOutboundReceipt', 'stockmovements::approveOutboundReceipt');
$routes->get('stockmovements/getMovementHistory', 'stockmovements::getMovementHistory');
$routes->get('stockmovements/getPendingMovements', 'stockmovements::getPendingMovements');
$routes->get('stockmovements/getPendingInboundReceipts', 'stockmovements::getPendingInboundReceipts');
$routes->get('stockmovements/getPendingOutboundReceipts', 'stockmovements::getPendingOutboundReceipts');

// Receipt Management Routes
$routes->get('api/receipts/inbound/pending', 'stockmovements::getPendingInboundReceipts');
$routes->get('api/receipts/outbound/pending', 'stockmovements::getPendingOutboundReceipts');
$routes->post('api/receipts/inbound/(:num)/approve', 'stockmovements::approveInboundReceipt/$1');
$routes->post('api/receipts/outbound/(:num)/approve', 'stockmovements::approveOutboundReceipt/$1');

// Staff Task API Routes
$routes->get('api/staff-tasks/pending', 'StaffTaskController::getPendingTasks');
$routes->post('api/staff-tasks/complete/(:num)', 'StaffTaskController::completeTask/$1');
$routes->post('api/staff-tasks/find-by-barcode', 'StaffTaskController::getTaskByBarcode');
$routes->post('api/staff-tasks/scan-item', 'StaffTaskController::scanTaskItem');
$routes->get('api/staff-tasks/stats', 'StaffTaskController::getTaskStats');
$routes->get('api/staff-tasks/history', 'StaffTaskController::getTaskHistory');

// Test Data Routes for Staff Task Integration
$routes->get('seed-staff-tasks-test-data', 'TestDataController::seedStaffTasksTestData');
$routes->get('clear-staff-tasks-test-data', 'TestDataController::clearStaffTasksTestData');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
