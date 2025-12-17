-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 04:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warehouse_group4`
--

-- --------------------------------------------------------

--
-- Table structure for table `inbound_receipts`
--

CREATE TABLE `inbound_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference_no` varchar(100) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `warehouse_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','PACKING','DELIVERING','DELIVERED','Approved','Rejected') DEFAULT 'Pending',
  `total_items` int(11) DEFAULT 0,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inbound_receipts`
--

INSERT INTO `inbound_receipts` (`id`, `reference_no`, `supplier_name`, `warehouse_id`, `status`, `total_items`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 'PO-1234', 'Construction Materials Ltd', 1, 'Approved', 1, 3, '2025-11-30 16:24:28', '2025-11-12 22:36:36', '2025-11-30 16:24:28'),
(2, 'PO-1210', 'Paint Solutions Inc', 1, 'Approved', 1, 3, '2025-12-09 00:27:40', '2025-11-12 22:36:36', '2025-12-09 00:27:40'),
(3, 'PO-1205', 'Pipe & Plumbing Co', 1, 'Approved', 1, 3, '2025-11-13 06:42:57', '2025-11-12 22:36:36', '2025-11-13 06:42:57');

-- --------------------------------------------------------

--
-- Table structure for table `inbound_receipt_items`
--

CREATE TABLE `inbound_receipt_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `receipt_id` int(11) UNSIGNED NOT NULL,
  `item_id` int(11) UNSIGNED NOT NULL,
  `warehouse_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inbound_receipt_items`
--

INSERT INTO `inbound_receipt_items` (`id`, `receipt_id`, `item_id`, `warehouse_id`, `quantity`, `unit_cost`) VALUES
(1, 1, 3, 1, 50, 25.00),
(2, 2, 8, 1, 200, 15.50),
(3, 3, 7, 1, 200, 8.75);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'in',
  `expiry` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `sku`, `category`, `location`, `warehouse_id`, `version`, `quantity`, `status`, `expiry`, `created_at`, `updated_at`) VALUES
(3, 'Portland Cement 50kg', 'CEM-50-001', 'Building Materials', 'A-1-01', 1, NULL, 200, 'in', NULL, '2025-09-12 04:38:45', '2025-09-12 04:38:45'),
(4, 'Timber Plank 2x4', 'TMR-24-003', 'Lumber & Wood Products', 'B-2-02', 1, NULL, 0, 'out', NULL, '2025-09-12 04:38:45', '2025-09-12 04:38:45'),
(5, 'Steel Rebar 12mm', 'STL-RB-12', 'Steel & Metal Products', 'C-3-03', 1, NULL, 500, 'in', NULL, '2025-09-12 04:38:45', '2025-09-12 04:38:45'),
(6, 'Copper Wire Roll', 'ELC-WR-01', 'Electrical Supplies', 'A', 1, NULL, 10, 'in', NULL, '2025-09-12 04:38:45', '2025-12-17 04:56:04'),
(7, 'PVC Pipe 2in', 'PLB-PVC-2', 'Plumbing Supplies', '', 1, NULL, 566, 'in', NULL, '2025-09-12 04:38:45', '2025-10-16 06:51:16'),
(8, 'Exterior Paint 5L', 'PNT-5L-004', 'Paints & Finishes', NULL, 1, NULL, 350, 'in', '2027-06-30', '2025-09-12 04:38:45', '2025-10-16 06:51:29'),
(9, 'Cordless Drill', 'TLS-DRL-01', 'Tools & Equipment', NULL, 1, NULL, 0, 'out', NULL, '2025-09-12 04:38:45', '2025-10-16 06:51:37'),
(10, 'Safety Helmet', 'SFT-HLM-01', 'Safety Gear', 'A-3-02', 1, NULL, 48, 'in', NULL, '2025-09-12 04:38:45', '2025-09-12 04:38:45'),
(11, 'Hex Bolts M10', 'HB-M10-002', 'Fasteners & Hardware', '', 1, NULL, 200, 'in', NULL, '2025-09-12 04:38:45', '2025-11-13 05:24:45'),
(12, 'Roof Shingles', 'RF-SHN-01', 'Roofing Materials', 'C-2-01', 1, NULL, 56, 'in', NULL, '2025-09-12 04:38:45', '2025-09-12 04:38:45'),
(13, 'Vinyl Flooring Plank', 'FLR-VNL-01', 'Flooring Materials', 'D-3-03', 1, NULL, 240, 'in', NULL, '2025-09-12 04:38:45', '2025-09-12 04:38:45'),
(16, 'Cement', 'CEM-001', 'Building Materials', NULL, 2, NULL, 200, 'in', NULL, '2025-12-09 04:33:54', '2025-12-09 04:33:54'),
(17, 'Sand', 'SND-001', 'Building Materials', NULL, 2, NULL, 50, 'in', NULL, '2025-12-09 04:33:54', '2025-12-09 04:33:54'),
(18, 'Gravel', 'GRV-001', 'Building Materials', NULL, 2, NULL, 60, 'in', NULL, '2025-12-09 04:33:54', '2025-12-09 04:33:54'),
(19, 'Bricks', 'BRK-001', 'Building Materials', 'A', 1, NULL, 4965, 'in', NULL, '2025-12-09 04:33:54', '2025-12-16 01:51:04'),
(20, 'Steel rods', 'STL-001', 'Building Materials', NULL, 2, NULL, 800, 'in', NULL, '2025-12-09 04:33:54', '2025-12-09 04:33:54'),
(21, 'Wood planks', 'WD-001', 'Building Materials', NULL, 2, NULL, 200, 'in', NULL, '2025-12-09 04:33:54', '2025-12-09 04:33:54'),
(22, 'Paint', 'PNT-001', 'Building Materials', NULL, 2, NULL, 40, 'in', NULL, '2025-12-09 04:33:54', '2025-12-09 04:33:54'),
(23, 'Tiles', 'TLS-001', 'Building Materials', NULL, 2, NULL, 1200, 'in', NULL, '2025-12-09 04:33:54', '2025-12-09 04:33:54'),
(24, 'Bricks', 'BRK-001', 'Building Materials', 'A', 2, 1, 100, 'in', '2025-12-15', '2025-12-16 01:51:58', '2025-12-16 01:51:58'),
(25, 'Steel Bars', 'STL-BAR-001', 'Construction Materials', NULL, 3, NULL, 150, 'in', NULL, '2025-12-15 17:59:29', '2025-12-15 17:59:29'),
(26, 'Nails Box', 'NLS-BOX-001', 'Hardware', NULL, 3, NULL, 75, 'in', NULL, '2025-12-15 17:59:29', '2025-12-15 17:59:29'),
(27, 'Wood Planks', 'WD-PLK-001', 'Lumber', NULL, 3, NULL, 200, 'in', NULL, '2025-12-15 17:59:29', '2025-12-15 17:59:29'),
(28, 'Screws Set', 'SCR-SET-001', 'Hardware', NULL, 3, NULL, 120, 'in', NULL, '2025-12-15 17:59:29', '2025-12-15 17:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference` varchar(100) NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payable` tinyint(1) NOT NULL DEFAULT 1,
  `vendor_client` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'OPEN',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2025-09-04-053624', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1756966089, 1),
(3, '2025-09-04-120000', 'App\\Database\\Migrations\\CreateInventoryTable', 'default', 'App', 1757097321, 2),
(4, '2025-10-06-123621', 'App\\Database\\Migrations\\CreateStocksTable', 'default', 'App', 1762280338, 3),
(5, '2025-11-04-000001', 'App\\Database\\Migrations\\CreateWarehouses', 'default', 'App', 1762280338, 3),
(6, '2025-11-04-000002', 'App\\Database\\Migrations\\CreateTransfers', 'default', 'App', 1762280338, 3),
(7, '2025-11-04-000003', 'App\\Database\\Migrations\\CreateInvoices', 'default', 'App', 1762280338, 3),
(8, '2025-11-04-000004', 'App\\Database\\Migrations\\UpdateInventoryAddWarehouseAndVersion', 'default', 'App', 1762280338, 3),
(9, '2025-11-07-113206', 'App\\Database\\Migrations\\CreateProgressTable', 'default', 'App', 1762581438, 4),
(10, '2025-11-07-120555', 'App\\Database\\Migrations\\CreateStockMovementTable', 'default', 'App', 1762581438, 4),
(11, '2025-11-07-130604', 'App\\Database\\Migrations\\CreatePutAwayTaskTable', 'default', 'App', 1762581438, 4),
(12, '2025-11-07-132924', 'App\\Database\\Migrations\\CreateVendorsTable', 'default', 'App', 1762581438, 4),
(13, '2025-11-07-133123', 'App\\Database\\Migrations\\CreateInvoicesTable', 'default', 'App', 1762581438, 4),
(14, '2025-11-07-133202', 'App\\Database\\Migrations\\CreatePurchaseOrdersTable', 'default', 'App', 1762581438, 4),
(15, '2025-11-07-133249', 'App\\Database\\Migrations\\CreatePaymentsTable', 'default', 'App', 1762581438, 4),
(16, '2025-11-07-133337', 'App\\Database\\Migrations\\CreatePendingApprovalsTable', 'default', 'App', 1762581438, 4),
(17, '2025-11-07-133431', 'App\\Database\\Migrations\\CreateExpensesTable', 'default', 'App', 1762581438, 4),
(18, '2025-11-07-133522', 'App\\Database\\Migrations\\CreateReportsTable', 'default', 'App', 1762581438, 4),
(19, '2025-11-07-133644', 'App\\Database\\Migrations\\CreateDecrepancyTable', 'default', 'App', 1762581438, 4),
(20, '2025-11-07-133824', 'App\\Database\\Migrations\\CreateCollectionsTable', 'default', 'App', 1762581438, 4),
(21, '2025-11-07-134534', 'App\\Database\\Migrations\\CreateInboundShipmentsTables', 'default', 'App', 1762581438, 4),
(22, '2025-11-07-134617', 'App\\Database\\Migrations\\CreateOutboundShipmentsTables', 'default', 'App', 1762581438, 4),
(23, '2025-11-07-135314', 'App\\Database\\Migrations\\CreateSuppliersTable', 'default', 'App', 1762581438, 4);

-- --------------------------------------------------------

--
-- Table structure for table `outbound_receipts`
--

CREATE TABLE `outbound_receipts` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference_no` varchar(100) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `warehouse_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Approved','SCANNED','DELIVERING','DELIVERED','Rejected') DEFAULT 'Pending',
  `total_items` int(11) DEFAULT 0,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outbound_receipts`
--

INSERT INTO `outbound_receipts` (`id`, `reference_no`, `customer_name`, `warehouse_id`, `status`, `total_items`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 'SO-5678', 'ABC Construction Corp', 1, 'Approved', 1, 3, '2025-11-30 16:24:37', '2025-11-12 22:36:36', '2025-11-30 16:24:37'),
(2, 'SO-5599', 'Electrical Works Ltd', 1, 'SCANNED', 1, 3, '2025-12-09 00:57:26', '2025-11-12 22:36:36', '2025-12-17 05:07:46'),
(3, 'SO-5588', 'Woodwork Construction Ltd', 1, 'Approved', 1, 3, '2025-11-13 06:44:11', '2025-11-12 22:36:36', '2025-11-13 06:44:11'),
(15, 'OUT-20251217-7887', 'Manual Outbound', 1, 'Approved', 1, 3, '2025-12-17 05:45:38', '2025-12-17 02:03:54', '2025-12-17 05:45:38'),
(16, 'OUT-20251217-8563', 'Manual Outbound', 1, 'Approved', 1, 3, '2025-12-17 05:49:26', '2025-12-17 05:15:15', '2025-12-17 05:49:26'),
(17, 'OUT-20251217-9252', 'Manual Outbound', 1, 'Approved', 1, 3, '2025-12-17 05:49:40', '2025-12-17 05:23:10', '2025-12-17 05:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `outbound_receipt_items`
--

CREATE TABLE `outbound_receipt_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `receipt_id` int(11) UNSIGNED NOT NULL,
  `item_id` int(11) UNSIGNED NOT NULL,
  `warehouse_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outbound_receipt_items`
--

INSERT INTO `outbound_receipt_items` (`id`, `receipt_id`, `item_id`, `warehouse_id`, `quantity`) VALUES
(1, 1, 11, 1, 100),
(2, 2, 6, 1, 40),
(3, 3, 4, 1, 60),
(4, 15, 13, 1, 20),
(5, 16, 19, 1, 10),
(6, 17, 12, 1, 12);

-- --------------------------------------------------------

--
-- Table structure for table `picking_packing_tasks`
--

CREATE TABLE `picking_packing_tasks` (
  `id` int(10) UNSIGNED NOT NULL,
  `receipt_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `task_type` enum('PICKING','PACKING') NOT NULL,
  `status` enum('Pending','In Progress','Picked','Packed') DEFAULT 'Pending',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `picked_quantity` int(10) UNSIGNED DEFAULT NULL,
  `packed_quantity` int(10) UNSIGNED DEFAULT NULL,
  `box_count` int(10) UNSIGNED DEFAULT 1,
  `started_at` datetime DEFAULT NULL,
  `completed_by` int(10) UNSIGNED DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `picking_packing_tasks`
--

INSERT INTO `picking_packing_tasks` (`id`, `receipt_id`, `item_id`, `task_type`, `status`, `assigned_to`, `picked_quantity`, `packed_quantity`, `box_count`, `started_at`, `completed_by`, `completed_at`, `created_at`, `updated_at`) VALUES
(4, 3, 4, 'PICKING', 'In Progress', 12, NULL, NULL, 1, '2025-12-17 04:54:57', NULL, NULL, '2025-12-17 04:54:57', '2025-12-17 04:54:57'),
(5, 1, 11, 'PICKING', 'In Progress', 12, NULL, NULL, 1, '2025-12-17 04:55:15', NULL, NULL, '2025-12-17 04:55:15', '2025-12-17 04:55:15'),
(6, 2, 6, 'PICKING', 'Picked', 12, 40, NULL, 1, '2025-12-17 04:55:18', 12, '2025-12-17 05:07:30', '2025-12-17 04:55:18', '2025-12-17 05:07:30'),
(17, 2, 6, 'PACKING', 'Packed', NULL, 40, 40, 2, NULL, 12, '2025-12-17 05:07:46', '2025-12-17 05:07:30', '2025-12-17 05:07:46'),
(29, 15, 13, 'PICKING', 'Picked', 12, 20, NULL, 1, '2025-12-17 05:45:53', 12, '2025-12-17 05:46:01', '2025-12-17 05:45:38', '2025-12-17 05:46:01'),
(30, 15, 13, 'PACKING', 'Pending', NULL, 20, NULL, 1, NULL, NULL, NULL, '2025-12-17 05:46:01', '2025-12-17 05:46:01'),
(31, 16, 19, 'PICKING', 'Pending', NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-12-17 05:49:26', '2025-12-17 05:49:26'),
(32, 17, 12, 'PICKING', 'Pending', NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-12-17 05:49:40', '2025-12-17 05:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `progress_id` int(11) UNSIGNED NOT NULL,
  `id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `put_away_tasks`
--

CREATE TABLE `put_away_tasks` (
  `task_id` int(11) UNSIGNED NOT NULL,
  `id` int(11) UNSIGNED NOT NULL,
  `item_details` varchar(255) NOT NULL,
  `location` varchar(100) NOT NULL,
  `priority` enum('low','medium','high') NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recent_scans`
--

CREATE TABLE `recent_scans` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_sku` varchar(64) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `movement_type` enum('IN','OUT') DEFAULT 'IN',
  `status` varchar(32) DEFAULT 'Pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_tasks`
--

CREATE TABLE `staff_tasks` (
  `id` int(11) UNSIGNED NOT NULL,
  `movement_id` int(11) UNSIGNED DEFAULT NULL,
  `reference_no` varchar(100) NOT NULL,
  `warehouse_id` int(11) UNSIGNED NOT NULL,
  `item_id` int(11) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) UNSIGNED DEFAULT 1,
  `movement_type` enum('IN','OUT') NOT NULL,
  `status` enum('Pending','Scanned','Completed','Failed','RED STOCK') DEFAULT 'Pending',
  `assigned_by` int(11) UNSIGNED DEFAULT NULL,
  `completed_by` int(11) UNSIGNED DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_tasks`
--

INSERT INTO `staff_tasks` (`id`, `movement_id`, `reference_no`, `warehouse_id`, `item_id`, `item_name`, `item_sku`, `quantity`, `movement_type`, `status`, `assigned_by`, `completed_by`, `completed_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'SO-5678', 1, 3, 'Hex Bolts M10', 'HB-M10-002', 100, 'OUT', '', NULL, NULL, NULL, 'Outbound shipment approved - scan items to confirm dispatch', '2025-11-13 05:04:48', '2025-11-13 06:26:06'),
(2, 8, 'PO-1205', 1, 7, 'PVC Pipe 2in', 'PLB-PVC-2', 200, 'IN', 'Completed', NULL, NULL, '2025-11-13 05:24:13', 'Completed via barcode scan: PLB-PVC-2', '2025-11-13 05:12:23', '2025-11-13 05:24:13'),
(3, 9, 'SO-5599', 1, 6, 'Copper Wire Roll', 'ELC-WR-01', 40, 'OUT', 'Completed', NULL, NULL, '2025-11-13 05:26:08', 'Completed via barcode scan: ELC-WR-01', '2025-11-13 05:25:48', '2025-11-13 05:26:08'),
(4, 11, 'SO-5588', 1, 4, 'Timber Plank 2x4', 'TMR-24-003', 60, 'OUT', '', NULL, NULL, NULL, 'Outbound shipment approved - scan items to confirm dispatch', '2025-11-13 06:27:21', '2025-11-13 06:27:45'),
(5, 13, 'PO-1205', 1, 7, 'PVC Pipe 2in', 'PLB-PVC-2', 200, 'IN', '', 3, NULL, NULL, 'Inbound receipt approved - scan items to confirm receipt', '2025-11-13 06:42:57', '2025-11-13 06:43:25'),
(6, 15, 'SO-5588', 1, 4, 'Timber Plank 2x4', 'TMR-24-003', 60, 'OUT', '', 3, NULL, NULL, 'Outbound shipment approved - scan items to confirm dispatch', '2025-11-13 06:44:11', '2025-11-13 06:44:29'),
(7, 17, 'PO-1234', 1, 3, 'Portland Cement 50kg', 'CEM-50-001', 50, 'IN', '', 3, NULL, NULL, 'Inbound receipt approved - scan items to confirm receipt', '2025-11-30 16:24:28', '2025-11-30 16:26:44'),
(8, 18, 'SO-5678', 1, 11, 'Hex Bolts M10', 'HB-M10-002', 100, 'OUT', '', 3, NULL, NULL, 'Outbound shipment approved - scan items to confirm dispatch', '2025-11-30 16:24:37', '2025-12-09 00:12:05'),
(9, 20, 'PO-1210', 1, 8, 'Exterior Paint 5L', 'PNT-5L-004', 200, 'IN', '', 3, NULL, NULL, 'Inbound receipt approved - scan items to confirm receipt', '2025-12-09 00:27:40', '2025-12-09 00:28:09'),
(10, 22, 'SO-5599', 1, 6, 'Copper Wire Roll', 'ELC-WR-01', 40, 'OUT', '', 3, NULL, NULL, 'Outbound shipment approved - scan items to confirm dispatch', '2025-12-09 00:57:26', '2025-12-09 00:58:16'),
(11, 23, 'OUT-20251209-5626', 1, 9, 'Cordless Drill', 'TLS-DRL-01', 100, 'OUT', '', 3, NULL, NULL, 'remarks', '2025-12-09 02:52:45', '2025-12-09 02:53:32'),
(12, 25, 'OUT-20251209-1656', 1, 7, 'PVC Pipe 2in', 'PLB-PVC-2', 122, 'OUT', '', 19, NULL, NULL, 'restock', '2025-12-09 03:47:12', '2025-12-17 05:12:03'),
(13, 26, 'OUT-20251209-6687', 1, 10, 'Safety Helmet', 'SFT-HLM-01', 132, 'OUT', '', 3, NULL, NULL, 'remarks', '2025-12-09 04:00:57', '2025-12-17 05:14:13'),
(14, 27, 'OUT-20251209-6805', 2, 21, 'Wood planks', 'WD-001', 100, 'OUT', '', 19, NULL, NULL, 'restock', '2025-12-09 04:34:44', '2025-12-09 04:35:12'),
(15, 29, 'OUT-20251209-1445', 2, 18, 'Gravel', 'GRV-001', 122, 'OUT', '', 19, NULL, NULL, '', '2025-12-09 04:37:08', '2025-12-09 04:39:04'),
(16, 30, 'OUT-20251209-5994', 2, 23, 'Tiles', 'TLS-001', 12, 'OUT', 'Pending', 19, NULL, NULL, '', '2025-12-09 04:39:39', '2025-12-09 04:39:39'),
(17, 31, 'OUT-20251217-7887', 1, 13, 'Vinyl Flooring Plank', 'FLR-VNL-01', 20, 'OUT', 'Completed', 3, 12, '2025-12-17 05:45:38', 'Completed via barcode scanning workflow', '2025-12-17 02:03:54', '2025-12-17 05:45:38'),
(18, 35, 'OUT-20251217-8563', 1, 19, 'Bricks', 'BRK-001', 10, 'OUT', 'Completed', 3, 12, '2025-12-17 05:49:26', 'Completed via barcode scanning workflow', '2025-12-17 05:15:15', '2025-12-17 05:49:26'),
(19, 37, 'OUT-20251217-5924', 1, 19, 'Bricks', 'BRK-001', 15, 'OUT', 'Pending', 3, NULL, NULL, '', '2025-12-17 05:18:04', '2025-12-17 05:18:16'),
(20, 39, 'OUT-20251217-1269', 1, 10, 'Safety Helmet', 'SFT-HLM-01', 12, 'OUT', 'Pending', 3, NULL, NULL, '', '2025-12-17 05:20:41', '2025-12-17 05:20:48'),
(21, 41, 'OUT-20251217-9252', 1, 12, 'Roof Shingles', 'RF-SHN-01', 12, 'OUT', 'Completed', 3, 12, '2025-12-17 05:49:40', 'Completed via barcode scanning workflow', '2025-12-17 05:23:10', '2025-12-17 05:49:40'),
(22, 43, 'OUT-20251217-6014', 1, 7, 'PVC Pipe 2in', 'PLB-PVC-2', 12, 'OUT', '', 3, NULL, NULL, '', '2025-12-17 05:29:11', '2025-12-17 05:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `movement_id` int(11) UNSIGNED NOT NULL,
  `transaction_number` varchar(100) NOT NULL,
  `items_in_progress` int(11) NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `movement_type` enum('in','out') NOT NULL,
  `location` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`movement_id`, `transaction_number`, `items_in_progress`, `order_number`, `id`, `quantity`, `company_name`, `movement_type`, `location`, `status`) VALUES
(1, 'TXN-1763010288-321', 1, 'SO-5678', 3, 100, 'ABC Construction Corp', 'out', 'Main Warehouse', 'approved'),
(2, 'TXN-1763010416-355', 1, 'SO-5678', 3, 100, 'ABC Construction Corp', 'out', 'Main Warehouse', 'approved'),
(8, 'TXN-1763010743-209', 1, 'PO-1205', 7, 200, 'Pipe & Plumbing Co', 'in', 'Main Warehouse', 'completed'),
(9, 'TXN-1763011548-444', 1, 'SO-5599', 6, 40, 'Electrical Works Ltd', 'out', 'Main Warehouse', 'completed'),
(10, 'SCAN-12-1763015190-1', 1, 'MANUAL-SCAN', 3, 100, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(11, 'TXN-1763015241-673', 1, 'SO-5588', 4, 60, 'Woodwork Construction Ltd', 'out', 'Main Warehouse', 'approved'),
(12, 'SCAN-12-1763015280-2', 1, 'MANUAL-SCAN', 4, 60, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(13, 'TXN-1763016177-621', 1, 'PO-1205', 7, 200, 'Pipe & Plumbing Co', 'in', 'Main Warehouse', 'approved'),
(14, 'SCAN-12-1763016211-3', 1, 'MANUAL-SCAN', 7, 200, 'WeBuild Construction', 'in', 'Warehouse', 'completed'),
(15, 'TXN-1763016251-310', 1, 'SO-5588', 4, 60, 'Woodwork Construction Ltd', 'out', 'Main Warehouse', 'approved'),
(16, 'SCAN-12-1763016275-4', 1, 'MANUAL-SCAN', 4, 60, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(17, 'TXN-1764519868-892', 1, 'PO-1234', 3, 50, 'Construction Materials Ltd', 'in', 'Main Warehouse', 'approved'),
(18, 'TXN-1764519877-671', 1, 'SO-5678', 11, 100, 'ABC Construction Corp', 'out', 'Main Warehouse', 'approved'),
(19, 'SCAN-12-1764520016-5', 1, 'MANUAL-SCAN', 3, 50, 'WeBuild Construction', 'in', 'Warehouse', 'completed'),
(20, 'TXN-1765240060-997', 1, 'PO-1210', 8, 200, 'Paint Solutions Inc', 'in', 'Main Warehouse', 'approved'),
(21, 'SCAN-12-1765240119-7', 1, 'MANUAL-SCAN', 8, 200, 'WeBuild Construction', 'in', 'Warehouse', 'completed'),
(22, 'TXN-1765241846-954', 1, 'SO-5599', 6, 40, 'Electrical Works Ltd', 'out', 'Main Warehouse', 'red_stock'),
(23, 'TXN-1765248765-557', 1, 'OUT-20251209-5626', 9, 100, 'warehouse A', 'out', 'warehouse A', 'pending'),
(24, 'SCAN-12-1765248821-8', 1, 'MANUAL-SCAN', 9, 100, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(25, 'TXN-1765252032-653', 1, 'OUT-20251209-1656', 7, 122, 'warehouse A', 'out', 'warehouse A', 'pending'),
(26, 'TXN-1765252857-336', 1, 'OUT-20251209-6687', 10, 132, 'Warehouse 2', 'out', 'Warehouse 2', 'red_stock'),
(27, 'TXN-1765254884-391', 1, 'OUT-20251209-6805', 21, 100, 'Warehouse', 'out', 'Warehouse', 'pending'),
(28, 'SCAN-20-1765254930-9', 1, 'MANUAL-SCAN', 21, 100, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(29, 'TXN-1765255028-300', 1, 'OUT-20251209-1445', 18, 122, 'Warehouse 2', 'out', 'Warehouse 2', 'red_stock'),
(30, 'TXN-1765255179-679', 1, 'OUT-20251209-5994', 23, 12, 'Warehouse main', 'out', 'Warehouse main', 'pending'),
(31, 'TXN-1765937034-189', 1, 'OUT-20251217-7887', 13, 20, 'Warehouse 2', 'out', 'Warehouse 2', 'completed'),
(32, 'SO-5599', 0, 'SO-5599', 6, 40, 'Electrical Works Ltd', 'out', 'A', 'completed'),
(33, 'SCAN-12-1765948343-12', 1, 'MANUAL-SCAN', 7, 122, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(34, 'SCAN-12-1765948343-13', 1, 'MANUAL-SCAN', 13, 20, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(35, 'TXN-1765948515-969', 1, 'OUT-20251217-8563', 19, 10, 'Warehouse', 'out', 'Warehouse', 'completed'),
(36, 'SCAN-12-1765948558-14', 1, 'MANUAL-SCAN', 19, 10, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(37, 'TXN-1765948684-251', 1, 'OUT-20251217-5924', 19, 15, 'Warehouse', 'out', 'Warehouse', 'pending'),
(38, 'SCAN-12-1765948699-15', 1, 'MANUAL-SCAN', 19, 15, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(39, 'TXN-1765948841-640', 1, 'OUT-20251217-1269', 10, 12, 'Warehouse', 'out', 'Warehouse', 'pending'),
(40, 'SCAN-12-1765948852-16', 1, 'MANUAL-SCAN', 10, 12, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(41, 'TXN-1765948990-696', 1, 'OUT-20251217-9252', 12, 12, 'Warehouse 2', 'out', 'Warehouse 2', 'completed'),
(42, 'SCAN-12-1765949001-17', 1, 'MANUAL-SCAN', 12, 12, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(43, 'TXN-1765949351-542', 1, 'OUT-20251217-6014', 7, 12, 'Warehouse', 'out', 'Warehouse', 'pending'),
(44, 'SCAN-12-1765949363-18', 1, 'MANUAL-SCAN', 7, 12, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(56, 'SCAN-12-1765950338-19', 1, 'MANUAL-SCAN', 13, 20, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(57, 'OUT-20251217-7887', 0, 'OUT-20251217-7887', 13, 20, 'Manual Outbound', 'out', 'D-3-03', 'completed'),
(58, 'SCAN-12-1765950566-20', 1, 'MANUAL-SCAN', 19, 10, 'WeBuild Construction', 'out', 'Warehouse', 'completed'),
(59, 'SCAN-12-1765950580-21', 1, 'MANUAL-SCAN', 12, 12, 'WeBuild Construction', 'out', 'Warehouse', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) UNSIGNED NOT NULL,
  `item_id` int(11) NOT NULL,
  `from_warehouse_id` int(11) DEFAULT NULL,
  `to_warehouse_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(225) NOT NULL,
  `role` enum('manager','staff','viewer','admin') NOT NULL DEFAULT 'staff',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(3, 'Tally', 'manager@whs.com', '$2y$10$7AGsnbEbTjrUBvxDbNKcfu/LhZu0ARH.kR7LdmaeT/Ldz9sz/g4N.', 'manager', '2025-09-04 06:42:04', '2025-12-17 03:37:33'),
(10, 'Alice Johnson', 'alice@example.com', '$2y$10$2mV18NuxDtemee0g0exy7ej2aK1SwtfV2ZkuFghDg1GqSSv8J9aQe', 'staff', '2025-10-29 23:56:05', '2025-10-29 23:56:05'),
(12, 'Amanie', 'staff@whs.com', '$2y$10$CH.dvCz6QZ4NRF.uSKKCoePWOpUHsjfzXy7A3VbeVhubQO.BSXv0y', 'staff', '2025-11-04 18:21:56', '2025-12-17 03:37:33'),
(13, 'Ogille', 'ogille@example.com', '$2y$10$8fx7W5ama058KcDPHJ1Ak.ibc.MF20Oq0XZZtyeUCP.39mZcR/GFS', 'manager', '2025-11-08 05:37:50', '2025-11-08 05:39:24'),
(18, 'Mama', 'mama@exam.com', '$2y$10$A2EViyC9Mdc7MlzVxA6TNuxB3A1fSwCkLaz48Oy/LBxZELdhnDIMe', 'staff', '2025-11-08 06:05:23', '2025-12-16 00:55:21'),
(19, 'Manager Two', 'manager2@whs.com', '$2y$10$Cl9JBTowjwf1sgNhHQ2NxeA.Duna5rX/3aK97kLFGAckXgp5gE4VG', 'manager', '2025-12-09 03:44:34', '2025-12-09 03:44:34'),
(20, 'Staff Two', 'staff2@whs.com', '$2y$10$3wP5lyyCKINsMI0HxyWIROxBhxFhmeB.tn2ETtHmh2bR5sMP46D4.', 'staff', '2025-12-09 03:44:34', '2025-12-09 03:44:34'),
(22, 'Client', 'client@whs.com', '$2y$10$TbBUWCa.4j.sObnPwFsyE.0FZmHEHhC25dDMQw3AnwPC6PVUa4xIW', 'manager', '2025-12-16 01:09:03', '2025-12-16 03:48:09'),
(24, 'Alex', 'viewer@whs.com', '$2y$10$7E88Irt1652PSEMHQiqm8eBhSZX0C4CrblODjfnFA42FGawCYr9qy', '', '2025-12-17 03:37:33', '2025-12-17 03:37:33'),
(25, 'Admin User', 'admin@warehouse.com', '$2y$10$r43kMAFY6BkANUDrpH0MMOIurOsowJfd6RTg6fQecrSnDVmc8611m', '', '2025-12-16 20:23:25', '2025-12-17 05:54:29');

-- --------------------------------------------------------

--
-- Table structure for table `user_warehouses`
--

CREATE TABLE `user_warehouses` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `warehouse_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_warehouses`
--

INSERT INTO `user_warehouses` (`id`, `user_id`, `warehouse_id`, `created_at`, `updated_at`) VALUES
(1, 19, 2, '2025-12-09 03:46:02', NULL),
(2, 20, 2, '2025-12-09 03:46:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `capacity` int(11) UNSIGNED DEFAULT 0,
  `current_usage` int(11) UNSIGNED DEFAULT 0,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `location`, `contact_info`, `capacity`, `current_usage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Main Warehouse', 'Tagum City', NULL, 10000, 3000, 'active', '2025-11-13 04:41:06', '2025-12-16 01:01:46'),
(2, 'Warehouse A', 'Panabo City', NULL, 8000, 2500, 'active', '2025-11-13 04:41:06', '2025-12-16 01:01:46'),
(3, 'Warehouse B', 'Davao City', NULL, 5000, 1000, 'active', '2025-11-13 04:41:06', '2025-12-16 01:01:46');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_requests`
--

CREATE TABLE `warehouse_requests` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference_no` varchar(100) NOT NULL,
  `requesting_warehouse_id` int(11) UNSIGNED NOT NULL,
  `supplying_warehouse_id` int(11) UNSIGNED NOT NULL,
  `requested_by` int(11) UNSIGNED NOT NULL,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `status` enum('PENDING','APPROVED','SCANNED','DELIVERING','DELIVERED','REJECTED') DEFAULT 'PENDING',
  `outbound_receipt_id` int(11) UNSIGNED DEFAULT NULL,
  `inbound_receipt_id` int(11) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouse_requests`
--

INSERT INTO `warehouse_requests` (`id`, `reference_no`, `requesting_warehouse_id`, `supplying_warehouse_id`, `requested_by`, `approved_by`, `approved_at`, `status`, `outbound_receipt_id`, `inbound_receipt_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'WR-20251216-0001', 2, 3, 3, NULL, NULL, 'PENDING', NULL, NULL, '', '2025-12-16 01:49:20', '2025-12-16 01:49:20'),
(2, 'WR-20251216-0002', 1, 2, 3, NULL, NULL, 'PENDING', NULL, NULL, '', '2025-12-16 02:00:07', '2025-12-16 02:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_request_items`
--

CREATE TABLE `warehouse_request_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `request_id` int(11) UNSIGNED NOT NULL,
  `item_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouse_request_items`
--

INSERT INTO `warehouse_request_items` (`id`, `request_id`, `item_id`, `quantity`, `created_at`) VALUES
(1, 1, 3, 20, '2025-12-16 01:49:20'),
(2, 2, 17, 20, '2025-12-16 02:00:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inbound_receipts`
--
ALTER TABLE `inbound_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `status_idx` (`status`);

--
-- Indexes for table `inbound_receipt_items`
--
ALTER TABLE `inbound_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `outbound_receipts`
--
ALTER TABLE `outbound_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `status_idx2` (`status`);

--
-- Indexes for table `outbound_receipt_items`
--
ALTER TABLE `outbound_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`);

--
-- Indexes for table `picking_packing_tasks`
--
ALTER TABLE `picking_packing_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receipt` (`receipt_id`),
  ADD KEY `idx_item` (`item_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_task_type` (`task_type`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `progress_id_foreign` (`id`);

--
-- Indexes for table `put_away_tasks`
--
ALTER TABLE `put_away_tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `put_away_tasks_id_foreign` (`id`);

--
-- Indexes for table `recent_scans`
--
ALTER TABLE `recent_scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `staff_tasks`
--
ALTER TABLE `staff_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `completed_by` (`completed_by`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `stock_movements_id_foreign` (`id`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_warehouses`
--
ALTER TABLE `user_warehouses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warehouse_requests`
--
ALTER TABLE `warehouse_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_requesting_warehouse` (`requesting_warehouse_id`),
  ADD KEY `idx_supplying_warehouse` (`supplying_warehouse_id`);

--
-- Indexes for table `warehouse_request_items`
--
ALTER TABLE `warehouse_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_id` (`request_id`),
  ADD KEY `idx_item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inbound_receipts`
--
ALTER TABLE `inbound_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inbound_receipt_items`
--
ALTER TABLE `inbound_receipt_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `outbound_receipts`
--
ALTER TABLE `outbound_receipts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `outbound_receipt_items`
--
ALTER TABLE `outbound_receipt_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `picking_packing_tasks`
--
ALTER TABLE `picking_packing_tasks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `put_away_tasks`
--
ALTER TABLE `put_away_tasks`
  MODIFY `task_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recent_scans`
--
ALTER TABLE `recent_scans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `staff_tasks`
--
ALTER TABLE `staff_tasks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `movement_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_warehouses`
--
ALTER TABLE `user_warehouses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `warehouse_requests`
--
ALTER TABLE `warehouse_requests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `warehouse_request_items`
--
ALTER TABLE `warehouse_request_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_id_foreign` FOREIGN KEY (`id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `put_away_tasks`
--
ALTER TABLE `put_away_tasks`
  ADD CONSTRAINT `put_away_tasks_id_foreign` FOREIGN KEY (`id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_tasks`
--
ALTER TABLE `staff_tasks`
  ADD CONSTRAINT `staff_tasks_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_tasks_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_tasks_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_tasks_ibfk_4` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_id_foreign` FOREIGN KEY (`id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
