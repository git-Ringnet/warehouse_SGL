-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 03, 2025 lúc 07:07 AM
-- Phiên bản máy phục vụ: 10.4.28-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `sgl`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `assemblies`
--

CREATE TABLE `assemblies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `purpose` enum('storage','project') NOT NULL DEFAULT 'storage',
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_serials` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tester_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `assembly_materials`
--

CREATE TABLE `assembly_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assembly_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `target_product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_unit` int(11) NOT NULL DEFAULT 0 COMMENT 'Tracks which unit of a product this material belongs to when multiple product units exist',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `serial` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `serial_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `assembly_products`
--

CREATE TABLE `assembly_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assembly_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `serials` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `change_logs`
--

CREATE TABLE `change_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `time_changed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `change_type` enum('lap_rap','xuat_kho','sua_chua','thu_hoi','nhap_kho','chuyen_kho') NOT NULL,
  `document_code` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `performed_by` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `detailed_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detailed_info`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `company_phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `has_account` tinyint(1) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `account_username` varchar(255) DEFAULT NULL,
  `account_password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`id`, `name`, `company_name`, `phone`, `company_phone`, `email`, `address`, `notes`, `has_account`, `is_locked`, `account_username`, `account_password`, `created_at`, `updated_at`) VALUES
(1, 'VIỄN THÔNG CÀ MAU', 'Anh Sơn', '0944392636', NULL, 'a@mail.com', 'số 286, Trần Hưng Đạo, Phường 5, Cà Mau .', NULL, 0, 0, NULL, NULL, '2025-07-03 03:04:06', '2025-07-03 03:04:46'),
(2, 'VIỄN THÔNG HẬU GIANG', 'Lê Minh Vẹn', '0847890969', NULL, 'b@mail.com', '61 Võ Văn Kiệt, KV4, P5, thành phố Vị Thanh, Hậu Giang', NULL, 0, 0, NULL, NULL, '2025-07-04 03:04:06', '2025-07-04 03:04:46'),
(3, 'VIỄN THÔNG VĨNH LONG', 'Nguyễn Hoàng Thông', '0919002605', NULL, 'c@mail.com', 'Ấp Thanh Mỹ 1, xã Thanh Đức, huyện Long Hồ, tỉnh Vĩnh Long', NULL, 0, 0, NULL, NULL, '2025-07-05 03:04:06', '2025-07-05 03:04:46'),
(4, 'CÔNG TY CỔ PHẦN ĐIỆN TỬ TIN HỌC VIỄN THÔNG THẾ HỆ MỚI', 'Anh Trung', '0913492700', NULL, 'd@mail.com', '56 Đinh Tiên Hoàng, Phường Lộc Thọ, Thành phố Nha Trang, Tỉnh Khánh Hòa', NULL, 0, 0, NULL, NULL, '2025-07-06 03:04:06', '2025-07-06 03:04:46'),
(5, 'TRUNG TÂM KINH DOANH VNPT BÀ RỊA - VŨNG TÀU - CHI NHÁNH TỔNG CÔNG TY DỊCH VỤ VIỄN THÔNG', 'Anh Thắng VNPT-BR', '0941866288', NULL, 'e@mail.com', '198 Xô Viết Nghệ Tĩnh, Phường Thắng Tam, Thành phố Vũng Tàu, Tỉnh Bà Rịa - Vũng Tàu', NULL, 0, 0, NULL, NULL, '2025-07-07 03:04:06', '2025-07-07 03:04:46'),
(6, 'TỔNG CÔNG TY DỊCH VỤ VIỄN THÔNG TRUNG TÂM KINH DOANH VNPT CẦN THƠ', 'Trần Hoàng Luận', '0829220063', NULL, 'f@mail.com', 'Số 02 Nguyễn Trãi, Phường Tân An, Quận Ninh Kiều, Cần Thơ', NULL, 0, 0, NULL, NULL, '2025-07-08 03:04:06', '2025-07-08 03:04:46'),
(7, 'TRUYỀN HÌNH CÁP TẠI ĐỒNG THÁP (SCTV)-PGD HỒNG NGỰ', 'Anh Nhẹ', '0901811330', NULL, 'g@mail.com', '109 Trương Hán Siêu, P. Mỹ Phú, TP.Cao Lãnh', NULL, 0, 0, NULL, NULL, '2025-07-09 03:04:06', '2025-07-09 03:04:46'),
(8, 'PHÒNG VĂN HÓA VÀ THÔNG TIN HUYỆN BÌNH ĐẠI', 'Huỳnh Thanh ÂN', '0348999839', NULL, 'h@mail.com', 'Trường THPT Thạnh Phước. Ấp Phước Hòa, xã Thạnh Phước, huyện Bình Đại, tỉnh Bến Tre.', NULL, 0, 0, NULL, NULL, '2025-07-10 03:04:06', '2025-07-10 03:04:46'),
(9, 'CN TRÀ VINH - CÔNG TY TRÁCH NHIỆM HỮU HẠN TRUYỀN HÌNH CÁP SAIGONTOURIST (SCTV)', 'Anh Tuấn', '0907295919', NULL, 'j@mail.com', 'Số 06 Hùng Vương , Phường 5, TP. Trà Vinh', NULL, 0, 0, NULL, NULL, '2025-07-11 03:04:06', '2025-07-11 03:04:46'),
(10, 'ĐÀI TRUYỀN THANH XÃ LỘC QUẢNG', 'Nguyễn Thuỳ Dương', '0388358226', NULL, 'k@mail.com', 'Đài truyền thanh, Thôn 4, xã Lộc Quảng - huyện Bảo Lâm - tỉnh Lâm Đồng', NULL, 0, 0, NULL, NULL, '2025-07-12 03:04:06', '2025-07-12 03:04:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer_maintenance_requests`
--

CREATE TABLE `customer_maintenance_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_code` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `project_description` text DEFAULT NULL,
  `request_date` date NOT NULL,
  `maintenance_reason` text NOT NULL,
  `maintenance_details` text DEFAULT NULL,
  `expected_completion_date` date DEFAULT NULL,
  `estimated_cost` decimal(15,2) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('pending','approved','rejected','in_progress','completed','canceled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `damaged_materials`
--

CREATE TABLE `damaged_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `repair_id` bigint(20) UNSIGNED NOT NULL,
  `device_code` varchar(100) NOT NULL,
  `material_code` varchar(100) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `serial` varchar(255) DEFAULT NULL,
  `damage_description` text DEFAULT NULL,
  `reported_by` bigint(20) UNSIGNED NOT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `device_codes`
--

CREATE TABLE `device_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispatch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` varchar(255) NOT NULL,
  `serial_main` varchar(255) NOT NULL,
  `serial_components` varchar(255) DEFAULT NULL,
  `serial_sim` varchar(255) DEFAULT NULL,
  `access_code` varchar(255) DEFAULT NULL,
  `iot_id` varchar(255) DEFAULT NULL,
  `mac_4g` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dispatches`
--

CREATE TABLE `dispatches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispatch_code` varchar(255) NOT NULL,
  `dispatch_date` date NOT NULL,
  `dispatch_type` enum('project','rental','warranty') NOT NULL,
  `dispatch_detail` enum('all','contract','backup') NOT NULL,
  `project_receiver` varchar(255) NOT NULL,
  `warranty_period` varchar(255) DEFAULT NULL,
  `company_representative_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dispatch_note` text DEFAULT NULL,
  `status` enum('pending','approved','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dispatch_items`
--

CREATE TABLE `dispatch_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispatch_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` enum('material','product','good') NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `category` enum('contract','backup','general') NOT NULL DEFAULT 'general',
  `serial_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`serial_numbers`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dispatch_replacements`
--

CREATE TABLE `dispatch_replacements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `replacement_code` varchar(255) NOT NULL,
  `original_dispatch_item_id` bigint(20) UNSIGNED NOT NULL,
  `replacement_dispatch_item_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `replacement_date` datetime NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dispatch_returns`
--

CREATE TABLE `dispatch_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `return_code` varchar(255) NOT NULL,
  `dispatch_item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `return_date` datetime NOT NULL,
  `reason_type` enum('warranty','return','replacement') NOT NULL,
  `reason` text DEFAULT NULL,
  `condition` enum('good','damaged','broken') NOT NULL DEFAULT 'good',
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `scope_value` varchar(255) DEFAULT NULL,
  `scope_type` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `employees`
--

INSERT INTO `employees` (`id`, `username`, `password`, `name`, `avatar`, `email`, `phone`, `address`, `notes`, `role`, `role_id`, `department`, `scope_value`, `scope_type`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$12$5QSRMDBfgbzP0YIHdUy0/Of0GtvlaQZuBlBTCneIMcoAhqpZ7PGGe', 'Quản trị viên', NULL, 'admin@example.com', '0123456789', 'Hà Nội, Việt Nam', NULL, 'admin', NULL, NULL, NULL, NULL, 'active', 1, '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(2, 'nhanvien', '$2y$12$uwjGeXqFemu6e8k0o15DC.2t.scOB3gSF9A9l8y6OaCLNCM3ZdqsO', 'Nhân viên', NULL, 'nhanvien@example.com', '0987654321', 'Hồ Chí Minh, Việt Nam', NULL, 'Nhân viên', NULL, NULL, NULL, NULL, 'active', 1, '2025-07-03 05:01:28', '2025-07-03 05:01:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `goods`
--

CREATE TABLE `goods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `serial` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `inventory_warehouses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`inventory_warehouses`)),
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `goods`
--

INSERT INTO `goods` (`id`, `code`, `name`, `category`, `unit`, `serial`, `notes`, `image_path`, `inventory_warehouses`, `status`, `is_hidden`, `created_at`, `updated_at`) VALUES
(1, 'CPU-HP', 'Máy tính HP Pro Tower 280 G9', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(2, 'MH-HP', 'Màn hình HP P204V', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(3, 'DD01', 'Đầu đọc CD/DVD/USB', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(4, 'MIXER', 'Bàn trộn tín hiệu Mixer MG06X', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(5, 'MICRO', 'Micro Behringer BM1', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(6, 'CHANMIC', 'Chân Micro để bàn', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(7, 'Microlab', 'Loa  kiểm âm Microlab', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(8, 'THM', 'Loa thế mới HS 3568', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(9, 'S630', 'Loa Toa SC630', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23'),
(10, 'TCN30', 'Loa vành nhựa TCN30', 'Hàng Hóa', 'Cái', NULL, NULL, NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:05:23', '2025-07-03 05:05:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `good_images`
--

CREATE TABLE `good_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `good_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `good_supplier`
--

CREATE TABLE `good_supplier` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `good_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_imports`
--

CREATE TABLE `inventory_imports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `import_code` varchar(255) NOT NULL,
  `import_date` date NOT NULL,
  `order_code` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_import_materials`
--

CREATE TABLE `inventory_import_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_import_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(255) NOT NULL DEFAULT 'material',
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `serial` varchar(255) DEFAULT NULL,
  `serial_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Danh sách các số serial' CHECK (json_valid(`serial_numbers`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_code` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `proposer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warranty_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_address` varchar(255) NOT NULL DEFAULT '',
  `maintenance_date` date NOT NULL,
  `maintenance_type` enum('regular','emergency','preventive') NOT NULL DEFAULT 'regular',
  `maintenance_reason` text NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','in_progress','completed','canceled') NOT NULL DEFAULT 'pending',
  `reject_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `maintenance_request_products`
--

CREATE TABLE `maintenance_request_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maintenance_request_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `maintenance_request_staff`
--

CREATE TABLE `maintenance_request_staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maintenance_request_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `materials`
--

CREATE TABLE `materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `inventory_warehouses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of warehouse IDs to calculate inventory' CHECK (json_valid(`inventory_warehouses`)),
  `status` enum('active','deleted') NOT NULL DEFAULT 'active',
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `materials`
--

INSERT INTO `materials` (`id`, `code`, `name`, `category`, `unit`, `notes`, `inventory_warehouses`, `status`, `is_hidden`, `created_at`, `updated_at`) VALUES
(22, 'ANDROID', 'Android (X96)', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(23, 'L300', 'L300', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(24, 'L300-ONLYLAN', 'L300 Only LAN No 4G', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(25, 'IOT', 'IOT', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(26, 'BCS-2KENH', 'Bo công suất 2 kênh', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(27, 'LINEIN', 'Line in (Mic)', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(28, 'LINEOUT', 'Line out', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(29, 'PI', 'Pi', 'Linh kiện', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(30, 'TERMINAL-2', 'Terminal 2', 'Vật tư', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(31, 'TERMINAL-3', 'Terminal 3', 'Vật tư', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(32, 'TERMINAL-4', 'Terminal 4', 'Vật tư', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(33, 'TERMINAL-6', 'Terminal 6', 'Vật tư', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(34, 'TERMINAL-10', 'Terminal 10', 'Vật tư', 'Cái', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(35, 'DAYDIEN-2x1.5', 'Dây nguồn box IPAB (2 lõi)', 'Vật tư', 'Mét', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(36, 'DAYDIEN-4X1.0', 'Dây tín hiệu box IPAB (4 lõi)', 'Vật tư', 'Mét', NULL, '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58'),
(37, 'DAY-NASA', 'Dây cáp truyền thanh Nasa', 'Vật tư', 'Mét', ' Triển khai các site Vĩnh Long', '[\"all\"]', 'active', 0, '2025-07-03 05:06:58', '2025-07-03 05:06:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `material_images`
--

CREATE TABLE `material_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `material_replacement_history`
--

CREATE TABLE `material_replacement_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `repair_id` bigint(20) UNSIGNED NOT NULL,
  `device_code` varchar(100) NOT NULL,
  `material_code` varchar(100) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `old_serials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`old_serials`)),
  `new_serials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`new_serials`)),
  `quantity` int(11) NOT NULL DEFAULT 1,
  `source_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `target_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `replaced_by` bigint(20) UNSIGNED NOT NULL,
  `replaced_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `material_supplier`
--

CREATE TABLE `material_supplier` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_07_20_000000_create_materials_table', 1),
(5, '2024_07_21_000000_create_products_table', 1),
(6, '2024_07_22_000000_create_serials_table', 1),
(7, '2024_07_23_000000_create_warehouses_table', 1),
(8, '2024_07_24_000000_create_warehouse_materials_table', 1),
(9, '2024_07_25_000000_create_assemblies_table', 1),
(10, '2024_07_25_000001_create_assembly_materials_table', 1),
(11, '2024_07_28_000000_create_material_images_table', 1),
(12, '2025_01_17_add_status_and_hidden_fields_to_products_table', 1),
(13, '2025_01_20_000000_add_status_and_hidden_fields_to_warehouses_table', 1),
(14, '2025_06_02_023512_create_customers_table', 1),
(15, '2025_06_02_033928_create_suppliers_table', 1),
(16, '2025_06_02_063949_create_employees_table', 1),
(17, '2025_06_02_081500_create_roles_table', 1),
(18, '2025_06_02_081526_create_permissions_table', 1),
(19, '2025_06_02_081839_create_role_permission_table', 1),
(20, '2025_06_02_081924_create_user_logs_table', 1),
(21, '2025_06_02_082011_add_role_id_to_employees_table', 1),
(22, '2025_06_04_023404_create_warehouse_transfers_table', 1),
(23, '2025_06_04_023443_create_warehouse_transfer_materials_table', 1),
(24, '2025_06_04_085219_create_software_table', 1),
(25, '2025_06_04_085402_create_software_directory', 1),
(26, '2025_06_05_050816_add_company_fields_to_customers_table', 1),
(27, '2025_06_05_062112_add_account_info_to_customers_table', 1),
(28, '2025_06_05_083712_add_password_to_customers_table', 1),
(29, '2025_06_05_093607_modify_employees_table', 1),
(30, '2025_06_06_022113_add_serial_number_to_warehouse_materials_table', 1),
(31, '2025_06_09_044651_add_is_locked_to_customers_table', 1),
(32, '2025_06_09_045426_add_customer_id_and_active_to_users_table', 1),
(33, '2025_06_09_045834_add_username_and_role_to_users_table', 1),
(34, '2025_06_09_052527_add_representative_to_suppliers_table', 1),
(35, '2025_06_09_095303_add_department_and_avatar_to_employees_table', 1),
(36, '2025_06_10_062350_add_manual_fields_to_software_table', 1),
(37, '2025_06_11_000001_create_goods_table', 1),
(38, '2025_06_11_023851_add_status_and_hidden_fields_to_materials_table', 1),
(39, '2025_06_11_074445_add_status_and_supplier_ids_to_goods_table', 1),
(40, '2025_06_11_080135_create_good_supplier_table', 1),
(41, '2025_06_12_075016_create_product_materials_table', 1),
(42, '2025_06_12_075055_create_product_images_table', 1),
(43, '2025_06_12_081103_create_material_supplier_table', 1),
(44, '2025_06_12_093243_add_missing_fields_to_products_table', 1),
(45, '2025_06_18_051439_add_warehouse_id_to_serials_table', 1),
(46, '2025_06_18_053117_add_serial_id_column_to_assembly_materials_table', 1),
(47, '2025_06_20_000000_create_testings_table', 1),
(48, '2025_06_20_000001_create_testing_items_table', 1),
(49, '2025_06_20_000002_create_testing_details_table', 1),
(50, '2025_06_20_031430_add_rental_id_to_dispatches_table', 1),
(51, '2025_06_21_000000_add_roles_to_testings_table', 1),
(52, '2025_06_24_163138_create_change_logs_table', 1),
(53, '2025_07_02_000000_create_inventory_imports_table', 1),
(54, '2025_07_05_000000_create_projects_table', 1),
(55, '2025_07_06_094332_create_dispatches_table', 1),
(56, '2025_07_07_000000_create_rentals_table', 1),
(57, '2025_07_08_000000_update_rentals_table', 1),
(58, '2025_07_09_000000_update_rentals_table_structure', 1),
(59, '2025_07_13_094530_create_dispatch_items_table', 1),
(60, '2025_07_13_105956_create_warranties_table', 1),
(61, '2025_07_13_105957_create_dispatch_replacements_table', 1),
(62, '2025_07_14_create_dispatch_returns_table', 1),
(63, '2025_07_27_000001_create_inventory_import_materials_table', 1),
(64, '2025_07_30_101930_add_serial_numbers_to_inventory_import_materials', 1),
(65, '2025_08_01_000000_add_warehouse_id_to_inventory_import_materials', 1),
(66, '2025_08_01_000001_make_warehouse_id_nullable_in_inventory_imports', 1),
(67, '2025_08_01_000002_create_good_images_table', 1),
(68, '2025_08_11_103234_add_employee_id_to_projects_table', 1),
(69, '2025_08_11_103242_add_employee_id_to_rentals_table', 1),
(70, '2025_09_10_072819_create_project_role_table', 1),
(71, '2025_09_10_072840_create_rental_role_table', 1),
(72, '2026_01_21_000000_add_role_id_to_users_table', 1),
(73, '2026_06_13_085842_add_tester_id_and_update_assigned_to_in_assemblies_table', 1),
(74, '2026_06_13_090836_add_purpose_and_project_id_to_assemblies_table', 1),
(75, '2026_06_15_105056_add_item_type_and_warehouse_id_to_inventory_import_materials_table', 1),
(76, '2026_06_16_094000_create_assembly_products_table', 1),
(77, '2026_06_16_095846_create_repairs_table', 1),
(78, '2026_06_16_095900_create_repair_items_table', 1),
(79, '2026_06_17_072353_add_target_product_id_to_assembly_materials_table', 1),
(80, '2026_06_20_000000_create_project_requests_table', 1),
(81, '2026_06_20_000001_create_project_request_items_table', 1),
(82, '2026_06_23_021323_create_material_replacement_history_table', 1),
(83, '2026_06_23_021533_add_rejection_fields_to_repair_items_table', 1),
(84, '2026_06_23_154156_update_repairs_table_status_enum', 1),
(85, '2026_06_24_032638_create_customer_maintenance_requests_table', 1),
(86, '2026_06_24_120048_create_damaged_materials_table', 1),
(87, '2026_06_27_110536_create_notifications_table', 1),
(88, '2026_06_30_132142_add_device_type_to_repair_items_table', 1),
(89, '2026_06_30_171235_add_product_unit_to_assembly_materials_table', 1),
(90, '2026_06_31_000019_drop_material_id_foreign_key_from_inventory_import_materials', 1),
(91, '2026_07_01_000000_create_device_codes_table', 1),
(92, '2026_07_01_000000_create_maintenance_requests_table', 1),
(93, '2026_07_01_000001_create_maintenance_request_products_table', 1),
(94, '2026_07_01_000002_create_maintenance_request_staff_table', 1),
(95, '2026_07_01_145203_add_maintenance_request_id_to_repairs_table', 1),
(96, '2026_07_01_145355_update_project_address_in_maintenance_requests_table', 1),
(97, '2026_07_01_145755_add_is_default_to_warehouses_table', 1),
(98, '2026_07_01_153836_add_warranty_id_to_maintenance_requests_table', 1),
(99, '2026_07_03_003912_update_approved_by_in_customer_maintenance_requests_table', 1),
(100, '2026_07_16_094735_add_assembly_id_to_testings_table', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `icon` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `related_type` varchar(255) DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `description`, `group`, `created_at`, `updated_at`) VALUES
(1, 'employees.view', 'Xem danh sách nhân viên', 'Xem thông tin nhân viên trong hệ thống', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(2, 'employees.view_detail', 'Xem chi tiết nhân viên', 'Xem thông tin chi tiết của nhân viên', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(3, 'employees.create', 'Thêm nhân viên', 'Tạo nhân viên mới', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(4, 'employees.edit', 'Sửa nhân viên', 'Chỉnh sửa thông tin nhân viên', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(5, 'employees.delete', 'Xóa nhân viên', 'Xóa nhân viên khỏi hệ thống', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(6, 'employees.toggle_active', 'Khóa và mở khóa nhân viên', 'Khóa hoặc mở khóa tài khoản nhân viên', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(7, 'customers.view', 'Xem danh sách khách hàng', 'Xem thông tin khách hàng', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(8, 'customers.view_detail', 'Xem chi tiết khách hàng', 'Xem thông tin chi tiết của khách hàng', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(9, 'customers.create', 'Thêm khách hàng', 'Tạo khách hàng mới', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(10, 'customers.edit', 'Sửa khách hàng', 'Chỉnh sửa thông tin khách hàng', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(11, 'customers.delete', 'Xóa khách hàng', 'Xóa khách hàng khỏi hệ thống', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(12, 'customers.manage', 'Quản lý khách hàng', 'Kích hoạt/vô hiệu hóa tài khoản khách hàng', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(13, 'customers.export', 'Xuất dữ liệu khách hàng', 'Xuất danh sách khách hàng ra file Excel/PDF', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(14, 'suppliers.view', 'Xem danh sách nhà cung cấp', 'Xem thông tin nhà cung cấp', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(15, 'suppliers.view_detail', 'Xem chi tiết nhà cung cấp', 'Xem thông tin chi tiết nhà cung cấp', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(16, 'suppliers.create', 'Thêm nhà cung cấp', 'Tạo nhà cung cấp mới', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(17, 'suppliers.edit', 'Sửa nhà cung cấp', 'Chỉnh sửa thông tin nhà cung cấp', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(18, 'suppliers.delete', 'Xóa nhà cung cấp', 'Xóa nhà cung cấp khỏi hệ thống', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(19, 'suppliers.export', 'Xuất dữ liệu nhà cung cấp', 'Xuất danh sách nhà cung cấp ra file Excel/PDF', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(20, 'warehouses.view', 'Xem danh sách kho hàng', 'Xem thông tin kho hàng', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(21, 'warehouses.view_detail', 'Xem chi tiết kho hàng', 'Xem thông tin chi tiết và tồn kho của kho hàng', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(22, 'warehouses.create', 'Thêm kho hàng', 'Tạo kho hàng mới', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(23, 'warehouses.edit', 'Sửa kho hàng', 'Chỉnh sửa thông tin kho hàng', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(24, 'warehouses.delete', 'Xóa kho hàng', 'Xóa kho hàng khỏi hệ thống', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(25, 'warehouses.export', 'Xuất file kho hàng', 'Xuất danh sách kho hàng ra file Excel, PDF', 'Quản lý hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(26, 'materials.view', 'Xem danh sách vật tư', 'Xem thông tin vật tư', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(27, 'materials.view_detail', 'Xem chi tiết vật tư', 'Xem thông tin chi tiết và hình ảnh của vật tư', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(28, 'materials.create', 'Thêm vật tư', 'Tạo vật tư mới', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(29, 'materials.edit', 'Sửa vật tư', 'Chỉnh sửa thông tin vật tư', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(30, 'materials.delete', 'Xóa vật tư', 'Xóa vật tư khỏi hệ thống', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(31, 'materials.export', 'Xuất file vật tư', 'Xuất danh sách vật tư ra file', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(32, 'products.view', 'Xem danh sách thành phẩm', 'Xem thông tin thành phẩm', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(33, 'products.view_detail', 'Xem chi tiết thành phẩm', 'Xem thông tin chi tiết và hình ảnh của thành phẩm', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(34, 'products.create', 'Thêm thành phẩm', 'Tạo thành phẩm mới', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(35, 'products.edit', 'Sửa thành phẩm', 'Chỉnh sửa thông tin thành phẩm', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(36, 'products.delete', 'Xóa thành phẩm', 'Xóa thành phẩm khỏi hệ thống', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(37, 'products.export', 'Xuất file thành phẩm', 'Xuất danh sách thành phẩm ra file', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(38, 'goods.view', 'Xem danh sách hàng hóa', 'Xem thông tin hàng hóa', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(39, 'goods.view_detail', 'Xem chi tiết hàng hóa', 'Xem thông tin chi tiết và hình ảnh của hàng hóa', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(40, 'goods.create', 'Thêm hàng hóa', 'Tạo hàng hóa mới', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(41, 'goods.edit', 'Sửa hàng hóa', 'Chỉnh sửa thông tin hàng hóa', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(42, 'goods.delete', 'Xóa hàng hóa', 'Xóa hàng hóa khỏi hệ thống', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(43, 'goods.export', 'Xuất file hàng hóa', 'Xuất danh sách hàng hóa ra file', 'Quản lý tài sản', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(44, 'inventory_imports.view', 'Xem nhập kho', 'Xem danh sách phiếu nhập kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(45, 'inventory_imports.create', 'Thêm nhập kho', 'Tạo phiếu nhập kho mới', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(46, 'inventory_imports.view_detail', 'Xem chi tiết nhập kho', 'Xem chi tiết phiếu nhập kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(47, 'inventory_imports.edit', 'Sửa nhập kho', 'Chỉnh sửa phiếu nhập kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(48, 'inventory_imports.delete', 'Xóa nhập kho', 'Xóa phiếu nhập kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(49, 'inventory.view', 'Xem danh sách xuất kho', 'Xem danh sách phiếu xuất kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(50, 'inventory.create', 'Tạo phiếu xuất kho', 'Tạo mới phiếu xuất kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(51, 'inventory.view_detail', 'Xem chi tiết xuất kho', 'Xem chi tiết phiếu xuất kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(52, 'inventory.edit', 'Sửa phiếu xuất kho', 'Chỉnh sửa phiếu xuất kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(53, 'inventory.delete', 'Xóa phiếu xuất kho', 'Xóa phiếu xuất kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(54, 'inventory.approve', 'Duyệt phiếu xuất kho', 'Duyệt phiếu xuất kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(55, 'inventory.cancel', 'Hủy phiếu xuất kho', 'Hủy phiếu xuất kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(56, 'warehouse-transfers.view', 'Xem chuyển kho', 'Xem thông tin chuyển kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(57, 'warehouse-transfers.view_detail', 'Xem chi tiết chuyển kho', 'Xem chi tiết phiếu chuyển kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(58, 'warehouse-transfers.create', 'Thêm chuyển kho', 'Tạo chuyển kho mới', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(59, 'warehouse-transfers.edit', 'Sửa chuyển kho', 'Chỉnh sửa thông tin chuyển kho', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(60, 'warehouse-transfers.delete', 'Xóa chuyển kho', 'Xóa chuyển kho khỏi hệ thống', 'Vận hành kho', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(61, 'assembly.view', 'Xem lắp ráp', 'Xem thông tin lắp ráp', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(62, 'assembly.view_detail', 'Xem chi tiết lắp ráp', 'Xem chi tiết phiếu lắp ráp', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(63, 'assembly.create', 'Thêm lắp ráp', 'Tạo lắp ráp mới', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(64, 'assembly.edit', 'Sửa lắp ráp', 'Chỉnh sửa thông tin lắp ráp', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(65, 'assembly.delete', 'Xóa lắp ráp', 'Xóa phiếu lắp ráp', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(66, 'assembly.export', 'Xuất file lắp ráp', 'Xuất phiếu lắp ráp ra file Excel, PDF', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(67, 'testing.view', 'Xem kiểm thử', 'Xem danh sách phiếu kiểm thử', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(68, 'testing.view_detail', 'Xem chi tiết kiểm thử', 'Xem chi tiết phiếu kiểm thử', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(69, 'testing.create', 'Thêm kiểm thử', 'Tạo phiếu kiểm thử mới', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(70, 'testing.edit', 'Sửa kiểm thử', 'Chỉnh sửa thông tin phiếu kiểm thử', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(71, 'testing.delete', 'Xóa kiểm thử', 'Xóa phiếu kiểm thử', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(72, 'testing.approve', 'Duyệt kiểm thử', 'Duyệt phiếu kiểm thử', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(73, 'testing.reject', 'Từ chối kiểm thử', 'Từ chối phiếu kiểm thử', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(74, 'testing.receive', 'Tiếp nhận kiểm thử', 'Tiếp nhận phiếu kiểm thử để thực hiện', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(75, 'testing.complete', 'Hoàn thành kiểm thử', 'Đánh dấu hoàn thành phiếu kiểm thử', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(76, 'testing.update_inventory', 'Cập nhật kho kiểm thử', 'Cập nhật kết quả kiểm thử vào kho', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(77, 'testing.print', 'In phiếu kiểm thử', 'In phiếu kiểm thử ra PDF', 'Sản xuất & Kiểm thử', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(78, 'repairs.view', 'Xem sửa chữa', 'Xem thông tin sửa chữa', 'Bảo trì & Sửa chữa', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(79, 'repairs.view_detail', 'Xem chi tiết sửa chữa', 'Xem chi tiết phiếu sửa chữa', 'Bảo trì & Sửa chữa', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(80, 'repairs.create', 'Thêm sửa chữa', 'Tạo phiếu sửa chữa mới', 'Bảo trì & Sửa chữa', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(81, 'repairs.edit', 'Sửa phiếu sửa chữa', 'Chỉnh sửa thông tin phiếu sửa chữa', 'Bảo trì & Sửa chữa', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(82, 'repairs.delete', 'Xóa phiếu sửa chữa', 'Xóa phiếu sửa chữa khỏi hệ thống', 'Bảo trì & Sửa chữa', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(83, 'warranties.view', 'Xem bảo hành', 'Xem thông tin bảo hành', 'Bảo trì & Sửa chữa', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(84, 'warranties.view_detail', 'Xem chi tiết bảo hành', 'Xem chi tiết phiếu bảo hành', 'Bảo trì & Sửa chữa', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(85, 'projects.view', 'Xem dự án', 'Xem thông tin dự án', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(86, 'projects.view_detail', 'Xem chi tiết dự án', 'Xem chi tiết dự án', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(87, 'projects.create', 'Thêm dự án', 'Tạo dự án mới', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(88, 'projects.edit', 'Sửa dự án', 'Chỉnh sửa thông tin dự án', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(89, 'projects.delete', 'Xóa dự án', 'Xóa dự án khỏi hệ thống', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(90, 'rentals.view', 'Xem cho thuê', 'Xem thông tin cho thuê', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(91, 'rentals.view_detail', 'Xem chi tiết cho thuê', 'Xem chi tiết cho thuê', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(92, 'rentals.create', 'Thêm cho thuê', 'Tạo cho thuê mới', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(93, 'rentals.edit', 'Sửa cho thuê', 'Chỉnh sửa thông tin cho thuê', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(94, 'rentals.delete', 'Xóa cho thuê', 'Xóa cho thuê khỏi hệ thống', 'Quản lý dự án', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(95, 'requests.view', 'Xem phiếu yêu cầu', 'Xem danh sách phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(96, 'requests.view_detail', 'Xem chi tiết phiếu yêu cầu', 'Xem chi tiết phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(97, 'requests.edit', 'Sửa phiếu yêu cầu', 'Chỉnh sửa phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(98, 'requests.delete', 'Xóa phiếu yêu cầu', 'Xóa phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(99, 'requests.approve', 'Duyệt phiếu yêu cầu', 'Phê duyệt phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(100, 'requests.reject', 'Từ chối phiếu yêu cầu', 'Từ chối phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(101, 'requests.export', 'Xuất file phiếu yêu cầu', 'Xuất phiếu yêu cầu ra file Excel/PDF', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(102, 'requests.copy', 'Sao chép phiếu yêu cầu', 'Sao chép phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(103, 'requests.update_status', 'Cập nhật trạng thái', 'Cập nhật trạng thái phiếu yêu cầu', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(104, 'requests.project.create', 'Tạo phiếu đề xuất dự án', 'Tạo mới phiếu đề xuất triển khai dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(105, 'requests.project.edit', 'Sửa phiếu đề xuất dự án', 'Chỉnh sửa phiếu đề xuất dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(106, 'requests.project.delete', 'Xóa phiếu đề xuất dự án', 'Xóa phiếu đề xuất dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(107, 'requests.project.approve', 'Duyệt phiếu đề xuất dự án', 'Phê duyệt phiếu đề xuất dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(108, 'requests.project.reject', 'Từ chối phiếu đề xuất dự án', 'Từ chối phiếu đề xuất dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(109, 'requests.maintenance.create', 'Tạo phiếu bảo trì dự án', 'Tạo mới phiếu bảo trì dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(110, 'requests.maintenance.edit', 'Sửa phiếu bảo trì dự án', 'Chỉnh sửa phiếu bảo trì dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(111, 'requests.maintenance.delete', 'Xóa phiếu bảo trì dự án', 'Xóa phiếu bảo trì dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(112, 'requests.maintenance.approve', 'Duyệt phiếu bảo trì dự án', 'Phê duyệt phiếu bảo trì dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(113, 'requests.maintenance.reject', 'Từ chối phiếu bảo trì dự án', 'Từ chối phiếu bảo trì dự án', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(114, 'requests.customer-maintenance.create', 'Tạo phiếu bảo trì khách hàng', 'Tạo mới phiếu yêu cầu bảo trì của khách hàng', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(115, 'requests.customer-maintenance.edit', 'Sửa phiếu bảo trì khách hàng', 'Chỉnh sửa phiếu bảo trì của khách hàng', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(116, 'requests.customer-maintenance.delete', 'Xóa phiếu bảo trì khách hàng', 'Xóa phiếu bảo trì của khách hàng', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(117, 'requests.customer-maintenance.approve', 'Duyệt phiếu bảo trì khách hàng', 'Phê duyệt phiếu bảo trì của khách hàng', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(118, 'requests.customer-maintenance.reject', 'Từ chối phiếu bảo trì khách hàng', 'Từ chối phiếu bảo trì của khách hàng', 'Phiếu yêu cầu', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(119, 'software.view', 'Xem phần mềm', 'Xem danh sách phần mềm và license', 'Phần mềm & License', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(120, 'software.view_detail', 'Xem chi tiết phần mềm', 'Xem chi tiết phần mềm và license', 'Phần mềm & License', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(121, 'software.create', 'Thêm phần mềm', 'Tạo phần mềm/license mới', 'Phần mềm & License', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(122, 'software.edit', 'Sửa phần mềm', 'Chỉnh sửa thông tin phần mềm/license', 'Phần mềm & License', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(123, 'software.delete', 'Xóa phần mềm', 'Xóa phần mềm/license khỏi hệ thống', 'Phần mềm & License', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(124, 'software.download', 'Tải phần mềm', 'Tải phần mềm/license từ hệ thống', 'Phần mềm & License', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(125, 'reports.overview', 'Xem dashboard thống kê', 'Xem dashboard và thống kê tổng quan của hệ thống', 'Báo cáo', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(126, 'reports.inventory', 'Xem báo cáo xuất nhập tồn chi tiết', 'Xem báo cáo chi tiết xuất nhập tồn theo thời gian', 'Báo cáo', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(127, 'reports.export', 'Xuất file báo cáo', 'Xuất báo cáo ra file Excel/PDF', 'Báo cáo', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(128, 'roles.view', 'Xem nhóm quyền', 'Xem danh sách nhóm quyền', 'Phân quyền', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(129, 'roles.view_detail', 'Xem chi tiết nhóm quyền', 'Xem chi tiết nhóm quyền', 'Phân quyền', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(130, 'roles.create', 'Thêm nhóm quyền', 'Tạo nhóm quyền mới', 'Phân quyền', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(131, 'roles.edit', 'Sửa nhóm quyền', 'Chỉnh sửa nhóm quyền', 'Phân quyền', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(132, 'roles.delete', 'Xóa nhóm quyền', 'Xóa nhóm quyền khỏi hệ thống', 'Phân quyền', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(133, 'user-logs.view', 'Xem nhật ký người dùng', 'Xem nhật ký hoạt động người dùng', 'Phân quyền', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(134, 'user-logs.export', 'Xuất nhật ký người dùng', 'Xuất nhật ký người dùng ra file', 'Phân quyền', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(135, 'change-logs.view', 'Xem nhật ký thay đổi', 'Xem nhật ký thay đổi hệ thống', 'Nhật ký hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(136, 'change-logs.view_detail', 'Xem chi tiết nhật ký thay đổi', 'Xem chi tiết nhật ký thay đổi hệ thống', 'Nhật ký hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(137, 'change-logs.export', 'Xuất nhật ký thay đổi', 'Xuất nhật ký thay đổi ra file', 'Nhật ký hệ thống', '2025-07-03 05:01:27', '2025-07-03 05:01:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','deleted') NOT NULL DEFAULT 'active',
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `inventory_warehouses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`inventory_warehouses`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `description`, `status`, `is_hidden`, `created_at`, `updated_at`, `inventory_warehouses`) VALUES
(1, 'SP-20250703-0001', 'Laptop Sony XsR-555 (Bảo hành)', 'Sony', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(2, 'SP-20250703-0002', 'Máy in Asus 18i-349', 'Asus', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(3, 'SP-20250703-0003', 'Máy tính để bàn Asus 1go-264', 'Asus', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(4, 'SP-20250703-0004', 'Máy tính để bàn Apple nAv-273 (Bảo hành)', 'Apple', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(5, 'SP-20250703-0005', 'Máy tính để bàn Sony CD4-852', 'Sony', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(6, 'SP-20250703-0006', 'Máy in Epson wUS-754 (Bảo hành)', 'Epson', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(7, 'SP-20250703-0007', 'Server Dell uIu-931 (Bảo hành)', 'Dell', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(8, 'SP-20250703-0008', 'Máy in Microsoft OZu-524', 'Microsoft', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(9, 'SP-20250703-0009', 'Máy tính để bàn Canon pfD-117', 'Canon', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL),
(10, 'SP-20250703-0010', 'Server Asus SSa-175 (Bảo hành)', 'Asus', 'active', 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_materials`
--

CREATE TABLE `product_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_code` varchar(255) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `warranty_period` int(11) NOT NULL DEFAULT 12,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `project_requests`
--

CREATE TABLE `project_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_code` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `proposer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `implementer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_address` varchar(255) NOT NULL,
  `approval_method` enum('production','warehouse') NOT NULL DEFAULT 'production',
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','in_progress','completed','canceled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `project_request_items`
--

CREATE TABLE `project_request_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_request_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` enum('equipment','material','good') NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `project_role`
--

CREATE TABLE `project_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rentals`
--

CREATE TABLE `rentals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rental_code` varchar(255) NOT NULL,
  `rental_name` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rental_date` date NOT NULL,
  `due_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rental_role`
--

CREATE TABLE `rental_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rental_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `repairs`
--

CREATE TABLE `repairs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `repair_code` varchar(255) NOT NULL COMMENT 'Mã phiếu sửa chữa',
  `warranty_code` varchar(255) DEFAULT NULL COMMENT 'Mã bảo hành',
  `warranty_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID bảo hành',
  `repair_type` enum('maintenance','repair','replacement','upgrade','other') NOT NULL COMMENT 'Loại sửa chữa',
  `repair_date` date NOT NULL COMMENT 'Ngày sửa chữa',
  `technician_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Kỹ thuật viên',
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Kho linh kiện',
  `repair_description` text NOT NULL COMMENT 'Mô tả sửa chữa',
  `repair_notes` text DEFAULT NULL COMMENT 'Ghi chú',
  `repair_photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Hình ảnh sửa chữa' CHECK (json_valid(`repair_photos`)),
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'in_progress' COMMENT 'Trạng thái',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT 'Người tạo',
  `maintenance_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `repair_items`
--

CREATE TABLE `repair_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `repair_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID phiếu sửa chữa',
  `device_code` varchar(255) NOT NULL COMMENT 'Mã thiết bị',
  `device_name` varchar(255) NOT NULL COMMENT 'Tên thiết bị',
  `device_serial` varchar(255) DEFAULT NULL COMMENT 'Serial thiết bị',
  `device_quantity` int(11) NOT NULL DEFAULT 1,
  `device_status` enum('selected','rejected') NOT NULL DEFAULT 'selected' COMMENT 'Trạng thái thiết bị',
  `device_notes` text DEFAULT NULL COMMENT 'Ghi chú thiết bị',
  `rejected_reason` text DEFAULT NULL,
  `rejected_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `device_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Hình ảnh thiết bị' CHECK (json_valid(`device_images`)),
  `device_type` varchar(255) DEFAULT NULL COMMENT 'Type of device (product/good)',
  `device_parts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Vật tư thiết bị' CHECK (json_valid(`device_parts`)),
  `reject_reason` text DEFAULT NULL COMMENT 'Lý do từ chối',
  `reject_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Kho lưu trữ thiết bị từ chối',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `scope` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `scope`, `is_active`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Quản trị viên cao cấp, có toàn quyền trong hệ thống', NULL, 1, 1, '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(2, 'Kho Sản Xuất', 'Nhóm quản lý thiết bị thuộc kho sản xuất', 'warehouse', 1, 0, '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(3, 'Kho Thành Phẩm', 'Nhóm quản lý thiết bị thành phẩm', 'warehouse', 1, 0, '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(4, 'Kho Bảo Hành', 'Nhóm quản lý bảo hành thiết bị', 'warehouse', 1, 0, '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(5, 'Kho Phần Mềm', 'Nhóm quản lý license, phần mềm, mã kích hoạt', 'warehouse', 1, 0, '2025-07-03 05:01:27', '2025-07-03 05:01:27'),
(6, 'Quản Lý Dự Án', 'Nhóm quản lý thiết bị theo địa bàn dự án', 'project', 1, 0, '2025-07-03 05:01:27', '2025-07-03 05:01:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role_permission`
--

CREATE TABLE `role_permission` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `role_permission`
--

INSERT INTO `role_permission` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 2, NULL, NULL),
(3, 1, 3, NULL, NULL),
(4, 1, 4, NULL, NULL),
(5, 1, 5, NULL, NULL),
(6, 1, 6, NULL, NULL),
(7, 1, 7, NULL, NULL),
(8, 1, 8, NULL, NULL),
(9, 1, 9, NULL, NULL),
(10, 1, 10, NULL, NULL),
(11, 1, 11, NULL, NULL),
(12, 1, 12, NULL, NULL),
(13, 1, 13, NULL, NULL),
(14, 1, 14, NULL, NULL),
(15, 1, 15, NULL, NULL),
(16, 1, 16, NULL, NULL),
(17, 1, 17, NULL, NULL),
(18, 1, 18, NULL, NULL),
(19, 1, 19, NULL, NULL),
(20, 1, 20, NULL, NULL),
(21, 1, 21, NULL, NULL),
(22, 1, 22, NULL, NULL),
(23, 1, 23, NULL, NULL),
(24, 1, 24, NULL, NULL),
(25, 1, 25, NULL, NULL),
(26, 1, 26, NULL, NULL),
(27, 1, 27, NULL, NULL),
(28, 1, 28, NULL, NULL),
(29, 1, 29, NULL, NULL),
(30, 1, 30, NULL, NULL),
(31, 1, 31, NULL, NULL),
(32, 1, 32, NULL, NULL),
(33, 1, 33, NULL, NULL),
(34, 1, 34, NULL, NULL),
(35, 1, 35, NULL, NULL),
(36, 1, 36, NULL, NULL),
(37, 1, 37, NULL, NULL),
(38, 1, 38, NULL, NULL),
(39, 1, 39, NULL, NULL),
(40, 1, 40, NULL, NULL),
(41, 1, 41, NULL, NULL),
(42, 1, 42, NULL, NULL),
(43, 1, 43, NULL, NULL),
(44, 1, 44, NULL, NULL),
(45, 1, 45, NULL, NULL),
(46, 1, 46, NULL, NULL),
(47, 1, 47, NULL, NULL),
(48, 1, 48, NULL, NULL),
(49, 1, 49, NULL, NULL),
(50, 1, 50, NULL, NULL),
(51, 1, 51, NULL, NULL),
(52, 1, 52, NULL, NULL),
(53, 1, 53, NULL, NULL),
(54, 1, 54, NULL, NULL),
(55, 1, 55, NULL, NULL),
(56, 1, 56, NULL, NULL),
(57, 1, 57, NULL, NULL),
(58, 1, 58, NULL, NULL),
(59, 1, 59, NULL, NULL),
(60, 1, 60, NULL, NULL),
(61, 1, 61, NULL, NULL),
(62, 1, 62, NULL, NULL),
(63, 1, 63, NULL, NULL),
(64, 1, 64, NULL, NULL),
(65, 1, 65, NULL, NULL),
(66, 1, 66, NULL, NULL),
(67, 1, 67, NULL, NULL),
(68, 1, 68, NULL, NULL),
(69, 1, 69, NULL, NULL),
(70, 1, 70, NULL, NULL),
(71, 1, 71, NULL, NULL),
(72, 1, 72, NULL, NULL),
(73, 1, 73, NULL, NULL),
(74, 1, 74, NULL, NULL),
(75, 1, 75, NULL, NULL),
(76, 1, 76, NULL, NULL),
(77, 1, 77, NULL, NULL),
(78, 1, 78, NULL, NULL),
(79, 1, 79, NULL, NULL),
(80, 1, 80, NULL, NULL),
(81, 1, 81, NULL, NULL),
(82, 1, 82, NULL, NULL),
(83, 1, 83, NULL, NULL),
(84, 1, 84, NULL, NULL),
(85, 1, 85, NULL, NULL),
(86, 1, 86, NULL, NULL),
(87, 1, 87, NULL, NULL),
(88, 1, 88, NULL, NULL),
(89, 1, 89, NULL, NULL),
(90, 1, 90, NULL, NULL),
(91, 1, 91, NULL, NULL),
(92, 1, 92, NULL, NULL),
(93, 1, 93, NULL, NULL),
(94, 1, 94, NULL, NULL),
(95, 1, 95, NULL, NULL),
(96, 1, 96, NULL, NULL),
(97, 1, 97, NULL, NULL),
(98, 1, 98, NULL, NULL),
(99, 1, 99, NULL, NULL),
(100, 1, 100, NULL, NULL),
(101, 1, 101, NULL, NULL),
(102, 1, 102, NULL, NULL),
(103, 1, 103, NULL, NULL),
(104, 1, 104, NULL, NULL),
(105, 1, 105, NULL, NULL),
(106, 1, 106, NULL, NULL),
(107, 1, 107, NULL, NULL),
(108, 1, 108, NULL, NULL),
(109, 1, 109, NULL, NULL),
(110, 1, 110, NULL, NULL),
(111, 1, 111, NULL, NULL),
(112, 1, 112, NULL, NULL),
(113, 1, 113, NULL, NULL),
(114, 1, 114, NULL, NULL),
(115, 1, 115, NULL, NULL),
(116, 1, 116, NULL, NULL),
(117, 1, 117, NULL, NULL),
(118, 1, 118, NULL, NULL),
(119, 1, 119, NULL, NULL),
(120, 1, 120, NULL, NULL),
(121, 1, 121, NULL, NULL),
(122, 1, 122, NULL, NULL),
(123, 1, 123, NULL, NULL),
(124, 1, 124, NULL, NULL),
(125, 1, 125, NULL, NULL),
(126, 1, 126, NULL, NULL),
(127, 1, 127, NULL, NULL),
(128, 1, 128, NULL, NULL),
(129, 1, 129, NULL, NULL),
(130, 1, 130, NULL, NULL),
(131, 1, 131, NULL, NULL),
(132, 1, 132, NULL, NULL),
(133, 1, 133, NULL, NULL),
(134, 1, 134, NULL, NULL),
(135, 1, 135, NULL, NULL),
(136, 1, 136, NULL, NULL),
(137, 1, 137, NULL, NULL),
(138, 2, 1, NULL, NULL),
(139, 2, 7, NULL, NULL),
(140, 2, 9, NULL, NULL),
(141, 2, 10, NULL, NULL),
(142, 2, 11, NULL, NULL),
(143, 2, 14, NULL, NULL),
(144, 2, 16, NULL, NULL),
(145, 2, 17, NULL, NULL),
(146, 2, 18, NULL, NULL),
(147, 2, 20, NULL, NULL),
(148, 2, 21, NULL, NULL),
(149, 2, 25, NULL, NULL),
(150, 2, 26, NULL, NULL),
(151, 2, 27, NULL, NULL),
(152, 2, 28, NULL, NULL),
(153, 2, 29, NULL, NULL),
(154, 2, 30, NULL, NULL),
(155, 2, 31, NULL, NULL),
(156, 2, 32, NULL, NULL),
(157, 2, 33, NULL, NULL),
(158, 2, 34, NULL, NULL),
(159, 2, 35, NULL, NULL),
(160, 2, 36, NULL, NULL),
(161, 2, 37, NULL, NULL),
(162, 2, 38, NULL, NULL),
(163, 2, 39, NULL, NULL),
(164, 2, 40, NULL, NULL),
(165, 2, 41, NULL, NULL),
(166, 2, 42, NULL, NULL),
(167, 2, 43, NULL, NULL),
(168, 2, 44, NULL, NULL),
(169, 2, 45, NULL, NULL),
(170, 2, 46, NULL, NULL),
(171, 2, 47, NULL, NULL),
(172, 2, 48, NULL, NULL),
(173, 2, 49, NULL, NULL),
(174, 2, 50, NULL, NULL),
(175, 2, 51, NULL, NULL),
(176, 2, 52, NULL, NULL),
(177, 2, 53, NULL, NULL),
(178, 2, 54, NULL, NULL),
(179, 2, 56, NULL, NULL),
(180, 2, 57, NULL, NULL),
(181, 2, 58, NULL, NULL),
(182, 2, 59, NULL, NULL),
(183, 2, 60, NULL, NULL),
(184, 2, 61, NULL, NULL),
(185, 2, 62, NULL, NULL),
(186, 2, 63, NULL, NULL),
(187, 2, 64, NULL, NULL),
(188, 2, 65, NULL, NULL),
(189, 2, 66, NULL, NULL),
(190, 2, 67, NULL, NULL),
(191, 2, 68, NULL, NULL),
(192, 2, 69, NULL, NULL),
(193, 2, 70, NULL, NULL),
(194, 2, 71, NULL, NULL),
(195, 2, 72, NULL, NULL),
(196, 2, 73, NULL, NULL),
(197, 2, 74, NULL, NULL),
(198, 2, 75, NULL, NULL),
(199, 2, 76, NULL, NULL),
(200, 2, 77, NULL, NULL),
(201, 2, 95, NULL, NULL),
(202, 2, 96, NULL, NULL),
(203, 2, 97, NULL, NULL),
(204, 2, 98, NULL, NULL),
(205, 2, 99, NULL, NULL),
(206, 2, 100, NULL, NULL),
(207, 2, 101, NULL, NULL),
(208, 2, 102, NULL, NULL),
(209, 2, 103, NULL, NULL),
(210, 2, 104, NULL, NULL),
(211, 2, 105, NULL, NULL),
(212, 2, 106, NULL, NULL),
(213, 2, 107, NULL, NULL),
(214, 2, 108, NULL, NULL),
(215, 2, 109, NULL, NULL),
(216, 2, 110, NULL, NULL),
(217, 2, 111, NULL, NULL),
(218, 2, 112, NULL, NULL),
(219, 2, 113, NULL, NULL),
(220, 2, 114, NULL, NULL),
(221, 2, 115, NULL, NULL),
(222, 2, 116, NULL, NULL),
(223, 2, 117, NULL, NULL),
(224, 2, 118, NULL, NULL),
(225, 2, 125, NULL, NULL),
(226, 2, 126, NULL, NULL),
(227, 2, 127, NULL, NULL),
(228, 3, 1, NULL, NULL),
(229, 3, 7, NULL, NULL),
(230, 3, 9, NULL, NULL),
(231, 3, 10, NULL, NULL),
(232, 3, 11, NULL, NULL),
(233, 3, 14, NULL, NULL),
(234, 3, 16, NULL, NULL),
(235, 3, 17, NULL, NULL),
(236, 3, 18, NULL, NULL),
(237, 3, 20, NULL, NULL),
(238, 3, 21, NULL, NULL),
(239, 3, 25, NULL, NULL),
(240, 3, 26, NULL, NULL),
(241, 3, 27, NULL, NULL),
(242, 3, 28, NULL, NULL),
(243, 3, 29, NULL, NULL),
(244, 3, 30, NULL, NULL),
(245, 3, 31, NULL, NULL),
(246, 3, 32, NULL, NULL),
(247, 3, 33, NULL, NULL),
(248, 3, 34, NULL, NULL),
(249, 3, 35, NULL, NULL),
(250, 3, 36, NULL, NULL),
(251, 3, 37, NULL, NULL),
(252, 3, 38, NULL, NULL),
(253, 3, 39, NULL, NULL),
(254, 3, 40, NULL, NULL),
(255, 3, 41, NULL, NULL),
(256, 3, 42, NULL, NULL),
(257, 3, 43, NULL, NULL),
(258, 3, 44, NULL, NULL),
(259, 3, 45, NULL, NULL),
(260, 3, 46, NULL, NULL),
(261, 3, 47, NULL, NULL),
(262, 3, 48, NULL, NULL),
(263, 3, 49, NULL, NULL),
(264, 3, 50, NULL, NULL),
(265, 3, 51, NULL, NULL),
(266, 3, 52, NULL, NULL),
(267, 3, 53, NULL, NULL),
(268, 3, 54, NULL, NULL),
(269, 3, 56, NULL, NULL),
(270, 3, 57, NULL, NULL),
(271, 3, 58, NULL, NULL),
(272, 3, 59, NULL, NULL),
(273, 3, 60, NULL, NULL),
(274, 3, 95, NULL, NULL),
(275, 3, 96, NULL, NULL),
(276, 3, 97, NULL, NULL),
(277, 3, 98, NULL, NULL),
(278, 3, 99, NULL, NULL),
(279, 3, 100, NULL, NULL),
(280, 3, 101, NULL, NULL),
(281, 3, 102, NULL, NULL),
(282, 3, 103, NULL, NULL),
(283, 3, 104, NULL, NULL),
(284, 3, 105, NULL, NULL),
(285, 3, 106, NULL, NULL),
(286, 3, 107, NULL, NULL),
(287, 3, 108, NULL, NULL),
(288, 3, 109, NULL, NULL),
(289, 3, 110, NULL, NULL),
(290, 3, 111, NULL, NULL),
(291, 3, 112, NULL, NULL),
(292, 3, 113, NULL, NULL),
(293, 3, 114, NULL, NULL),
(294, 3, 115, NULL, NULL),
(295, 3, 116, NULL, NULL),
(296, 3, 117, NULL, NULL),
(297, 3, 118, NULL, NULL),
(298, 3, 125, NULL, NULL),
(299, 3, 126, NULL, NULL),
(300, 3, 127, NULL, NULL),
(301, 4, 1, NULL, NULL),
(302, 4, 7, NULL, NULL),
(303, 4, 9, NULL, NULL),
(304, 4, 10, NULL, NULL),
(305, 4, 11, NULL, NULL),
(306, 4, 14, NULL, NULL),
(307, 4, 16, NULL, NULL),
(308, 4, 17, NULL, NULL),
(309, 4, 18, NULL, NULL),
(310, 4, 20, NULL, NULL),
(311, 4, 21, NULL, NULL),
(312, 4, 25, NULL, NULL),
(313, 4, 26, NULL, NULL),
(314, 4, 27, NULL, NULL),
(315, 4, 28, NULL, NULL),
(316, 4, 29, NULL, NULL),
(317, 4, 30, NULL, NULL),
(318, 4, 31, NULL, NULL),
(319, 4, 32, NULL, NULL),
(320, 4, 33, NULL, NULL),
(321, 4, 34, NULL, NULL),
(322, 4, 35, NULL, NULL),
(323, 4, 36, NULL, NULL),
(324, 4, 37, NULL, NULL),
(325, 4, 38, NULL, NULL),
(326, 4, 39, NULL, NULL),
(327, 4, 40, NULL, NULL),
(328, 4, 41, NULL, NULL),
(329, 4, 42, NULL, NULL),
(330, 4, 43, NULL, NULL),
(331, 4, 44, NULL, NULL),
(332, 4, 45, NULL, NULL),
(333, 4, 46, NULL, NULL),
(334, 4, 47, NULL, NULL),
(335, 4, 48, NULL, NULL),
(336, 4, 49, NULL, NULL),
(337, 4, 50, NULL, NULL),
(338, 4, 51, NULL, NULL),
(339, 4, 52, NULL, NULL),
(340, 4, 53, NULL, NULL),
(341, 4, 54, NULL, NULL),
(342, 4, 56, NULL, NULL),
(343, 4, 57, NULL, NULL),
(344, 4, 58, NULL, NULL),
(345, 4, 59, NULL, NULL),
(346, 4, 60, NULL, NULL),
(347, 4, 78, NULL, NULL),
(348, 4, 79, NULL, NULL),
(349, 4, 80, NULL, NULL),
(350, 4, 81, NULL, NULL),
(351, 4, 82, NULL, NULL),
(352, 4, 83, NULL, NULL),
(353, 4, 84, NULL, NULL),
(354, 4, 95, NULL, NULL),
(355, 4, 96, NULL, NULL),
(356, 4, 97, NULL, NULL),
(357, 4, 98, NULL, NULL),
(358, 4, 99, NULL, NULL),
(359, 4, 100, NULL, NULL),
(360, 4, 101, NULL, NULL),
(361, 4, 102, NULL, NULL),
(362, 4, 103, NULL, NULL),
(363, 4, 104, NULL, NULL),
(364, 4, 105, NULL, NULL),
(365, 4, 106, NULL, NULL),
(366, 4, 107, NULL, NULL),
(367, 4, 108, NULL, NULL),
(368, 4, 109, NULL, NULL),
(369, 4, 110, NULL, NULL),
(370, 4, 111, NULL, NULL),
(371, 4, 112, NULL, NULL),
(372, 4, 113, NULL, NULL),
(373, 4, 114, NULL, NULL),
(374, 4, 115, NULL, NULL),
(375, 4, 116, NULL, NULL),
(376, 4, 117, NULL, NULL),
(377, 4, 118, NULL, NULL),
(378, 4, 125, NULL, NULL),
(379, 4, 126, NULL, NULL),
(380, 4, 127, NULL, NULL),
(381, 5, 1, NULL, NULL),
(382, 5, 7, NULL, NULL),
(383, 5, 9, NULL, NULL),
(384, 5, 10, NULL, NULL),
(385, 5, 11, NULL, NULL),
(386, 5, 14, NULL, NULL),
(387, 5, 16, NULL, NULL),
(388, 5, 17, NULL, NULL),
(389, 5, 18, NULL, NULL),
(390, 5, 20, NULL, NULL),
(391, 5, 21, NULL, NULL),
(392, 5, 25, NULL, NULL),
(393, 5, 26, NULL, NULL),
(394, 5, 27, NULL, NULL),
(395, 5, 28, NULL, NULL),
(396, 5, 29, NULL, NULL),
(397, 5, 30, NULL, NULL),
(398, 5, 31, NULL, NULL),
(399, 5, 32, NULL, NULL),
(400, 5, 33, NULL, NULL),
(401, 5, 34, NULL, NULL),
(402, 5, 35, NULL, NULL),
(403, 5, 36, NULL, NULL),
(404, 5, 37, NULL, NULL),
(405, 5, 38, NULL, NULL),
(406, 5, 39, NULL, NULL),
(407, 5, 40, NULL, NULL),
(408, 5, 41, NULL, NULL),
(409, 5, 42, NULL, NULL),
(410, 5, 43, NULL, NULL),
(411, 5, 44, NULL, NULL),
(412, 5, 45, NULL, NULL),
(413, 5, 46, NULL, NULL),
(414, 5, 47, NULL, NULL),
(415, 5, 48, NULL, NULL),
(416, 5, 49, NULL, NULL),
(417, 5, 50, NULL, NULL),
(418, 5, 51, NULL, NULL),
(419, 5, 52, NULL, NULL),
(420, 5, 53, NULL, NULL),
(421, 5, 54, NULL, NULL),
(422, 5, 56, NULL, NULL),
(423, 5, 57, NULL, NULL),
(424, 5, 58, NULL, NULL),
(425, 5, 59, NULL, NULL),
(426, 5, 60, NULL, NULL),
(427, 5, 95, NULL, NULL),
(428, 5, 96, NULL, NULL),
(429, 5, 97, NULL, NULL),
(430, 5, 98, NULL, NULL),
(431, 5, 99, NULL, NULL),
(432, 5, 100, NULL, NULL),
(433, 5, 101, NULL, NULL),
(434, 5, 102, NULL, NULL),
(435, 5, 103, NULL, NULL),
(436, 5, 104, NULL, NULL),
(437, 5, 105, NULL, NULL),
(438, 5, 106, NULL, NULL),
(439, 5, 107, NULL, NULL),
(440, 5, 108, NULL, NULL),
(441, 5, 109, NULL, NULL),
(442, 5, 110, NULL, NULL),
(443, 5, 111, NULL, NULL),
(444, 5, 112, NULL, NULL),
(445, 5, 113, NULL, NULL),
(446, 5, 114, NULL, NULL),
(447, 5, 115, NULL, NULL),
(448, 5, 116, NULL, NULL),
(449, 5, 117, NULL, NULL),
(450, 5, 118, NULL, NULL),
(451, 5, 119, NULL, NULL),
(452, 5, 120, NULL, NULL),
(453, 5, 121, NULL, NULL),
(454, 5, 122, NULL, NULL),
(455, 5, 123, NULL, NULL),
(456, 5, 124, NULL, NULL),
(457, 5, 125, NULL, NULL),
(458, 5, 126, NULL, NULL),
(459, 5, 127, NULL, NULL),
(460, 6, 1, NULL, NULL),
(461, 6, 7, NULL, NULL),
(462, 6, 9, NULL, NULL),
(463, 6, 10, NULL, NULL),
(464, 6, 11, NULL, NULL),
(465, 6, 14, NULL, NULL),
(466, 6, 16, NULL, NULL),
(467, 6, 17, NULL, NULL),
(468, 6, 18, NULL, NULL),
(469, 6, 20, NULL, NULL),
(470, 6, 26, NULL, NULL),
(471, 6, 32, NULL, NULL),
(472, 6, 85, NULL, NULL),
(473, 6, 86, NULL, NULL),
(474, 6, 87, NULL, NULL),
(475, 6, 88, NULL, NULL),
(476, 6, 89, NULL, NULL),
(477, 6, 90, NULL, NULL),
(478, 6, 91, NULL, NULL),
(479, 6, 92, NULL, NULL),
(480, 6, 93, NULL, NULL),
(481, 6, 94, NULL, NULL),
(482, 6, 95, NULL, NULL),
(483, 6, 96, NULL, NULL),
(484, 6, 97, NULL, NULL),
(485, 6, 98, NULL, NULL),
(486, 6, 99, NULL, NULL),
(487, 6, 100, NULL, NULL),
(488, 6, 101, NULL, NULL),
(489, 6, 102, NULL, NULL),
(490, 6, 103, NULL, NULL),
(491, 6, 104, NULL, NULL),
(492, 6, 105, NULL, NULL),
(493, 6, 106, NULL, NULL),
(494, 6, 107, NULL, NULL),
(495, 6, 108, NULL, NULL),
(496, 6, 109, NULL, NULL),
(497, 6, 110, NULL, NULL),
(498, 6, 111, NULL, NULL),
(499, 6, 112, NULL, NULL),
(500, 6, 113, NULL, NULL),
(501, 6, 114, NULL, NULL),
(502, 6, 115, NULL, NULL),
(503, 6, 116, NULL, NULL),
(504, 6, 117, NULL, NULL),
(505, 6, 118, NULL, NULL),
(506, 6, 125, NULL, NULL),
(507, 6, 126, NULL, NULL),
(508, 6, 127, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `serials`
--

CREATE TABLE `serials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `serial_number` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('product','material','good') NOT NULL DEFAULT 'product',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `warehouse_id` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('sJKkVxa9BB6GQMWRRrBTP50oWX0vFySnYteMSULR', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiT1lEV214TkpIVGZHTml3clozaXRHdHhWQmo5NUJkc0k2RFozWWxJZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zL2xhdGVzdCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo5OiJ1c2VyX3R5cGUiO3M6ODoiZW1wbG95ZWUiO30=', 1751519226);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `software`
--

CREATE TABLE `software` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `manual_path` varchar(255) DEFAULT NULL,
  `manual_name` varchar(255) DEFAULT NULL,
  `manual_size` varchar(255) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `platform` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `description` text DEFAULT NULL,
  `changelog` text DEFAULT NULL,
  `download_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `representative` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `representative`, `phone`, `email`, `address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'CÔNG TY CỔ PHẦN GIẢI PHÁP CÔNG NGHỆ VÀ THƯƠNG MẠI DTEKCO', 'Đặng Văn Tiến', '', 'b@mail.com', 'Số nhà 23, ngách 58/23, phố Trần Bình, Phường Mai Dịch, Quận Cầu Giấy, Thành Phố Hà Nội', NULL, '2025-07-04 03:04:06', '2025-07-04 03:04:46'),
(2, 'CÔNG TY CỔ PHẦN THƯƠNG MẠI VÀ KỸ THUẬT VMV', 'Nguyễn Thị Hằng', '0973710871', 'c@mail.com', 'Lô 2 - 3A, đường Kim Đồng nối dài, Phường Hoàng Văn Thụ, Quận Hoàng Mai, Thành phố Hà Nội, Việt Nam', NULL, '2025-07-05 03:04:06', '2025-07-05 03:04:46'),
(3, 'CÔNG TY CỔ PHẦN THƯƠNG MẠI - DỊCH VỤ PHONG VŨ', '', '', 'd@mail.com', 'Tầng 5, 117-119-121 Nguyễn Du, Phường Bến Thành, Quận 1, Thành phố Hồ Chí Minh, Việt Nam', NULL, '2025-07-06 03:04:06', '2025-07-06 03:04:46'),
(4, 'CÔNG TY TNHH CÔNG NGHỆ THÔNG TIN AN PHÁT', 'Anh Đức', '0932366833', 'e@mail.com', '3C Trần Phú, Phường 04 , Quận 5, Tp. Hồ Chí Minh', NULL, '2025-07-07 03:04:06', '2025-07-07 03:04:46'),
(5, 'CÔNG TY TNHH SẢN XUẤT VÀ ĐẦU TƯ THƯƠNG MẠI QUỐC TẾ NASA', '', '', 'f@mail.com', 'Số 5, ngõ 176 Trương Định, Phường Trương Định, Quận Hai Bà Trưng, Thành Phố Hà Nội, Việt Nam.', NULL, '2025-07-08 03:04:06', '2025-07-08 03:04:46'),
(6, 'CÔNG TY TNHH THƯƠNG MẠI VÀ XÂY LẮP SET TOÀN CẦU', '', '', 'g@mail.com', 'Số 2, ngõ 22, Thôn Thượng, Xã Cự Khê, Huyện Thanh Oai, Thành phố Hà Nội, Việt Nam', NULL, '2025-07-09 03:04:06', '2025-07-09 03:04:46'),
(7, 'CÔNG TY TNHH THƯƠNG MẠI DỊCH VỤ HỢP THÀNH THỊNH', '', '', 'h@mail.com', '406/55 Cộng Hòa, Phường 13, Quận Tân Bình, Thành Phố Hồ Chí Minh, Việt Nam', NULL, '2025-07-10 03:04:06', '2025-07-10 03:04:46'),
(8, 'CÔNG TY TNHH MỘT THÀNH VIÊN NGÔ LONG ÂN', '', '', 'j@mail.com', 'Số 43/1 Lý Chiêu Hoàng, Phường 10, Quận 6, Thành phố Hồ Chí Minh, Việt Nam', NULL, '2025-07-11 03:04:06', '2025-07-11 03:04:46'),
(9, 'CÔNG TY TNHH THƯƠNG MẠI DỊCH VỤ XUẤT NHẬP KHẨU CÁP VIỆT', '', '', 'k@mail.com', '338 Đường HT13, Phường Hiệp Thành, Quận 12, Thành phố Hồ Chí Minh, Việt Nam', NULL, '2025-07-12 03:04:06', '2025-07-12 03:04:46'),
(10, 'CÔNG TY TRÁCH NHIỆM HỮU HẠN ANH KIM', 'Nguyễn Văn Tịnh', '0965190446 ', 'l@mail.com', '59 đường số 9, Phường 9, Quận Gò Vấp, Thành\n phố Hồ Chí Minh, Việt Nam', NULL, '2025-07-13 03:04:06', '2025-07-13 03:04:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `testings`
--

CREATE TABLE `testings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `test_code` varchar(255) NOT NULL,
  `test_type` enum('material','finished_product') NOT NULL,
  `tester_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `receiver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `test_date` date NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `conclusion` text DEFAULT NULL,
  `pass_quantity` int(11) NOT NULL DEFAULT 0,
  `fail_quantity` int(11) NOT NULL DEFAULT 0,
  `fail_reasons` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `is_inventory_updated` tinyint(1) NOT NULL DEFAULT 0,
  `success_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fail_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assembly_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `testing_details`
--

CREATE TABLE `testing_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `testing_id` bigint(20) UNSIGNED NOT NULL,
  `test_item_name` varchar(255) NOT NULL,
  `result` enum('pass','fail','pending') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `testing_items`
--

CREATE TABLE `testing_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `testing_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` enum('material','product','finished_product') NOT NULL,
  `material_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `good_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assembly_id` bigint(20) UNSIGNED DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `result` enum('pass','fail','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `customer_id`, `name`, `email`, `username`, `email_verified_at`, `password`, `role`, `role_id`, `remember_token`, `active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Test User', 'test@example.com', NULL, '2025-07-03 05:01:27', '$2y$12$BpG1su7o7nJIC/d4XauWkuT9mgikA08QEvXV3tkw/7iQitR7Rq2IO', 'user', NULL, 'Fmca6Xi3bc', 1, '2025-07-03 05:01:27', '2025-07-03 05:01:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_logs`
--

CREATE TABLE `user_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `action`, `module`, `description`, `old_data`, `new_data`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'login', 'auth', 'Đăng nhập thành công (nhân viên)', NULL, '{\"username\":\"admin\",\"name\":\"Qu\\u1ea3n tr\\u1ecb vi\\u00ean\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-03 05:02:14', '2025-07-03 05:02:14'),
(2, 1, 'logout', 'auth', 'Đăng xuất thành công (nhân viên)', '{\"username\":\"admin\",\"name\":\"Qu\\u1ea3n tr\\u1ecb vi\\u00ean\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-03 05:02:24', '2025-07-03 05:02:24'),
(3, 1, 'login', 'auth', 'Đăng nhập thành công (nhân viên)', NULL, '{\"username\":\"admin\",\"name\":\"Qu\\u1ea3n tr\\u1ecb vi\\u00ean\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-03 05:02:28', '2025-07-03 05:02:28'),
(4, 1, 'view', 'customers', 'Xem chi tiết khách hàng: ĐÀI TRUYỀN THANH XÃ LỘC QUẢNG - Nguyễn Thuỳ Dương', NULL, '{\"id\":10,\"name\":\"\\u0110\\u00c0I TRUY\\u1ec0N THANH X\\u00c3 L\\u1ed8C QU\\u1ea2NG\",\"company_name\":\"Nguy\\u1ec5n Thu\\u1ef3 D\\u01b0\\u01a1ng\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-03 05:04:22', '2025-07-03 05:04:22'),
(5, 1, 'view', 'materials', 'Xem chi tiết vật tư: Ốc vít 10mm', NULL, '{\"id\":1,\"code\":\"VT001\",\"name\":\"\\u1ed0c v\\u00edt 10mm\",\"category\":\"Linh ki\\u1ec7n\",\"unit\":\"Kg\",\"notes\":\"\\u1ed0c v\\u00edt th\\u00f4ng d\\u1ee5ng\",\"inventory_warehouses\":null,\"status\":\"active\",\"is_hidden\":false,\"created_at\":\"2025-07-03T05:01:28.000000Z\",\"updated_at\":\"2025-07-03T05:01:28.000000Z\",\"images\":[],\"suppliers\":[]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-03 05:06:02', '2025-07-03 05:06:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `manager` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','deleted') NOT NULL DEFAULT 'active',
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `warehouses`
--

INSERT INTO `warehouses` (`id`, `code`, `name`, `address`, `manager`, `description`, `status`, `is_hidden`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'WH001', 'Kho chính', '123 Lê Lợi, Quận 1, TP.HCM', 'Nguyễn Văn A', 'Kho chính trung tâm', 'active', 0, 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(2, 'WH002', 'Kho phụ', '456 Nguyễn Huệ, Quận 3, TP.HCM', 'Trần Thị B', 'Kho phụ gần trung tâm', 'active', 0, 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(3, 'WH003', 'Kho linh kiện', '789 Lý Tự Trọng, Quận 5, TP.HCM', 'Lê Văn C', 'Kho chuyên lưu trữ linh kiện điện tử', 'active', 0, 0, '2025-07-03 05:01:28', '2025-07-03 05:01:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `warehouse_materials`
--

CREATE TABLE `warehouse_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` enum('material','product','good') NOT NULL DEFAULT 'material',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `serial_number` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `warehouse_materials`
--

INSERT INTO `warehouse_materials` (`id`, `warehouse_id`, `material_id`, `item_type`, `quantity`, `serial_number`, `location`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'material', 74, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(2, 1, 2, 'material', 92, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(3, 1, 3, 'material', 51, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(4, 1, 4, 'material', 22, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(5, 1, 5, 'material', 29, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(6, 2, 1, 'material', 99, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(7, 2, 2, 'material', 25, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(8, 2, 3, 'material', 73, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(9, 2, 4, 'material', 25, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(10, 2, 5, 'material', 83, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(11, 3, 1, 'material', 22, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(12, 3, 2, 'material', 35, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(13, 3, 3, 'material', 59, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(14, 3, 4, 'material', 74, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28'),
(15, 3, 5, 'material', 21, NULL, NULL, '2025-07-03 05:01:28', '2025-07-03 05:01:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `warehouse_transfers`
--

CREATE TABLE `warehouse_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_code` varchar(255) NOT NULL,
  `serial` varchar(255) DEFAULT NULL,
  `source_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `destination_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `transfer_date` date NOT NULL,
  `status` enum('pending','in_progress','completed','canceled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `warehouse_transfer_materials`
--

CREATE TABLE `warehouse_transfer_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_transfer_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `serial_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Danh sách các số serial' CHECK (json_valid(`serial_numbers`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `warranties`
--

CREATE TABLE `warranties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `warranty_code` varchar(255) NOT NULL,
  `dispatch_id` bigint(20) UNSIGNED NOT NULL,
  `dispatch_item_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(255) NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `serial_number` text DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `purchase_date` date NOT NULL,
  `warranty_start_date` date NOT NULL,
  `warranty_end_date` date NOT NULL,
  `warranty_period_months` int(11) NOT NULL DEFAULT 12,
  `warranty_type` enum('standard','extended','premium') NOT NULL DEFAULT 'standard',
  `status` enum('active','expired','claimed','void') NOT NULL DEFAULT 'active',
  `warranty_terms` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `assemblies`
--
ALTER TABLE `assemblies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assemblies_code_unique` (`code`),
  ADD KEY `assemblies_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `assemblies_target_warehouse_id_foreign` (`target_warehouse_id`),
  ADD KEY `assemblies_tester_id_foreign` (`tester_id`),
  ADD KEY `assemblies_assigned_employee_id_foreign` (`assigned_employee_id`),
  ADD KEY `assemblies_project_id_foreign` (`project_id`);

--
-- Chỉ mục cho bảng `assembly_materials`
--
ALTER TABLE `assembly_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assembly_materials_assembly_id_foreign` (`assembly_id`),
  ADD KEY `assembly_materials_material_id_foreign` (`material_id`),
  ADD KEY `assembly_materials_serial_id_foreign` (`serial_id`),
  ADD KEY `assembly_materials_target_product_id_foreign` (`target_product_id`);

--
-- Chỉ mục cho bảng `assembly_products`
--
ALTER TABLE `assembly_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assembly_products_assembly_id_foreign` (`assembly_id`),
  ADD KEY `assembly_products_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `change_logs`
--
ALTER TABLE `change_logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `customer_maintenance_requests`
--
ALTER TABLE `customer_maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_maintenance_requests_request_code_unique` (`request_code`),
  ADD KEY `customer_maintenance_requests_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_maintenance_requests_approved_by_foreign` (`approved_by`);

--
-- Chỉ mục cho bảng `damaged_materials`
--
ALTER TABLE `damaged_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_damaged_material` (`repair_id`,`device_code`,`material_code`,`serial`),
  ADD KEY `damaged_materials_reported_by_foreign` (`reported_by`),
  ADD KEY `damaged_materials_repair_id_index` (`repair_id`);

--
-- Chỉ mục cho bảng `device_codes`
--
ALTER TABLE `device_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_codes_dispatch_id_foreign` (`dispatch_id`);

--
-- Chỉ mục cho bảng `dispatches`
--
ALTER TABLE `dispatches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dispatches_dispatch_code_unique` (`dispatch_code`),
  ADD KEY `dispatches_company_representative_id_foreign` (`company_representative_id`),
  ADD KEY `dispatches_created_by_foreign` (`created_by`),
  ADD KEY `dispatches_approved_by_foreign` (`approved_by`),
  ADD KEY `dispatches_dispatch_date_status_index` (`dispatch_date`,`status`),
  ADD KEY `dispatches_dispatch_type_index` (`dispatch_type`);

--
-- Chỉ mục cho bảng `dispatch_items`
--
ALTER TABLE `dispatch_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispatch_items_dispatch_id_item_type_index` (`dispatch_id`,`item_type`),
  ADD KEY `dispatch_items_item_type_item_id_index` (`item_type`,`item_id`),
  ADD KEY `dispatch_items_warehouse_id_index` (`warehouse_id`);

--
-- Chỉ mục cho bảng `dispatch_replacements`
--
ALTER TABLE `dispatch_replacements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dispatch_replacements_replacement_code_unique` (`replacement_code`),
  ADD KEY `dispatch_replacements_original_dispatch_item_id_foreign` (`original_dispatch_item_id`),
  ADD KEY `dispatch_replacements_replacement_dispatch_item_id_foreign` (`replacement_dispatch_item_id`),
  ADD KEY `dispatch_replacements_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `dispatch_returns`
--
ALTER TABLE `dispatch_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dispatch_returns_return_code_unique` (`return_code`),
  ADD KEY `dispatch_returns_dispatch_item_id_foreign` (`dispatch_item_id`),
  ADD KEY `dispatch_returns_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `dispatch_returns_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_username_unique` (`username`),
  ADD KEY `employees_role_id_foreign` (`role_id`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `goods`
--
ALTER TABLE `goods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goods_code_unique` (`code`);

--
-- Chỉ mục cho bảng `good_images`
--
ALTER TABLE `good_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `good_images_good_id_foreign` (`good_id`);

--
-- Chỉ mục cho bảng `good_supplier`
--
ALTER TABLE `good_supplier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `good_supplier_good_id_supplier_id_unique` (`good_id`,`supplier_id`),
  ADD KEY `good_supplier_supplier_id_foreign` (`supplier_id`);

--
-- Chỉ mục cho bảng `inventory_imports`
--
ALTER TABLE `inventory_imports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_imports_import_code_unique` (`import_code`),
  ADD KEY `inventory_imports_supplier_id_foreign` (`supplier_id`),
  ADD KEY `inventory_imports_warehouse_id_foreign` (`warehouse_id`);

--
-- Chỉ mục cho bảng `inventory_import_materials`
--
ALTER TABLE `inventory_import_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_import_materials_inventory_import_id_foreign` (`inventory_import_id`),
  ADD KEY `inventory_import_materials_material_id_foreign` (`material_id`),
  ADD KEY `inventory_import_materials_warehouse_id_foreign` (`warehouse_id`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `maintenance_requests_request_code_unique` (`request_code`),
  ADD KEY `maintenance_requests_proposer_id_foreign` (`proposer_id`),
  ADD KEY `maintenance_requests_customer_id_foreign` (`customer_id`),
  ADD KEY `maintenance_requests_warranty_id_foreign` (`warranty_id`);

--
-- Chỉ mục cho bảng `maintenance_request_products`
--
ALTER TABLE `maintenance_request_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_request_products_maintenance_request_id_foreign` (`maintenance_request_id`),
  ADD KEY `maintenance_request_products_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `maintenance_request_staff`
--
ALTER TABLE `maintenance_request_staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_request_staff_maintenance_request_id_foreign` (`maintenance_request_id`),
  ADD KEY `maintenance_request_staff_employee_id_foreign` (`employee_id`);

--
-- Chỉ mục cho bảng `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `material_images`
--
ALTER TABLE `material_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_images_material_id_foreign` (`material_id`);

--
-- Chỉ mục cho bảng `material_replacement_history`
--
ALTER TABLE `material_replacement_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_replacement_history_source_warehouse_id_foreign` (`source_warehouse_id`),
  ADD KEY `material_replacement_history_target_warehouse_id_foreign` (`target_warehouse_id`),
  ADD KEY `material_replacement_history_replaced_by_foreign` (`replaced_by`),
  ADD KEY `material_replacement_history_repair_id_index` (`repair_id`);

--
-- Chỉ mục cho bảng `material_supplier`
--
ALTER TABLE `material_supplier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_supplier_material_id_supplier_id_unique` (`material_id`,`supplier_id`),
  ADD KEY `material_supplier_supplier_id_foreign` (`supplier_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `product_materials`
--
ALTER TABLE `product_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_materials_product_id_material_id_unique` (`product_id`,`material_id`),
  ADD KEY `product_materials_material_id_foreign` (`material_id`);

--
-- Chỉ mục cho bảng `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_project_code_unique` (`project_code`),
  ADD KEY `projects_customer_id_foreign` (`customer_id`),
  ADD KEY `projects_employee_id_foreign` (`employee_id`);

--
-- Chỉ mục cho bảng `project_requests`
--
ALTER TABLE `project_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_requests_request_code_unique` (`request_code`),
  ADD KEY `project_requests_proposer_id_foreign` (`proposer_id`),
  ADD KEY `project_requests_implementer_id_foreign` (`implementer_id`),
  ADD KEY `project_requests_customer_id_foreign` (`customer_id`);

--
-- Chỉ mục cho bảng `project_request_items`
--
ALTER TABLE `project_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_request_items_project_request_id_foreign` (`project_request_id`);

--
-- Chỉ mục cho bảng `project_role`
--
ALTER TABLE `project_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_role_project_id_role_id_unique` (`project_id`,`role_id`),
  ADD KEY `project_role_role_id_foreign` (`role_id`);

--
-- Chỉ mục cho bảng `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rentals_rental_code_unique` (`rental_code`),
  ADD KEY `rentals_customer_id_foreign` (`customer_id`),
  ADD KEY `rentals_employee_id_foreign` (`employee_id`);

--
-- Chỉ mục cho bảng `rental_role`
--
ALTER TABLE `rental_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rental_role_rental_id_role_id_unique` (`rental_id`,`role_id`),
  ADD KEY `rental_role_role_id_foreign` (`role_id`);

--
-- Chỉ mục cho bảng `repairs`
--
ALTER TABLE `repairs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `repairs_repair_code_unique` (`repair_code`),
  ADD KEY `repairs_warranty_id_foreign` (`warranty_id`),
  ADD KEY `repairs_created_by_foreign` (`created_by`),
  ADD KEY `repairs_technician_id_foreign` (`technician_id`),
  ADD KEY `repairs_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `repairs_repair_code_index` (`repair_code`),
  ADD KEY `repairs_warranty_code_index` (`warranty_code`),
  ADD KEY `repairs_repair_date_index` (`repair_date`),
  ADD KEY `repairs_status_index` (`status`),
  ADD KEY `repairs_maintenance_request_id_foreign` (`maintenance_request_id`);

--
-- Chỉ mục cho bảng `repair_items`
--
ALTER TABLE `repair_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `repair_items_repair_id_index` (`repair_id`),
  ADD KEY `repair_items_device_code_index` (`device_code`),
  ADD KEY `repair_items_device_status_index` (`device_status`),
  ADD KEY `repair_items_rejected_warehouse_id_foreign` (`rejected_warehouse_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Chỉ mục cho bảng `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permission_permission_id_foreign` (`permission_id`);

--
-- Chỉ mục cho bảng `serials`
--
ALTER TABLE `serials`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `testings`
--
ALTER TABLE `testings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `testings_test_code_unique` (`test_code`),
  ADD KEY `testings_tester_id_foreign` (`tester_id`),
  ADD KEY `testings_approved_by_foreign` (`approved_by`),
  ADD KEY `testings_received_by_foreign` (`received_by`),
  ADD KEY `testings_success_warehouse_id_foreign` (`success_warehouse_id`),
  ADD KEY `testings_fail_warehouse_id_foreign` (`fail_warehouse_id`),
  ADD KEY `testings_assigned_to_foreign` (`assigned_to`),
  ADD KEY `testings_receiver_id_foreign` (`receiver_id`),
  ADD KEY `testings_assembly_id_foreign` (`assembly_id`);

--
-- Chỉ mục cho bảng `testing_details`
--
ALTER TABLE `testing_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `testing_details_testing_id_foreign` (`testing_id`);

--
-- Chỉ mục cho bảng `testing_items`
--
ALTER TABLE `testing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `testing_items_testing_id_foreign` (`testing_id`),
  ADD KEY `testing_items_material_id_foreign` (`material_id`),
  ADD KEY `testing_items_product_id_foreign` (`product_id`),
  ADD KEY `testing_items_good_id_foreign` (`good_id`),
  ADD KEY `testing_items_assembly_id_foreign` (`assembly_id`),
  ADD KEY `testing_items_supplier_id_foreign` (`supplier_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_customer_id_foreign` (`customer_id`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Chỉ mục cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_logs_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `warehouse_materials`
--
ALTER TABLE `warehouse_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouse_material_type_unique` (`warehouse_id`,`material_id`,`item_type`);

--
-- Chỉ mục cho bảng `warehouse_transfers`
--
ALTER TABLE `warehouse_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouse_transfers_transfer_code_unique` (`transfer_code`),
  ADD KEY `warehouse_transfers_source_warehouse_id_foreign` (`source_warehouse_id`),
  ADD KEY `warehouse_transfers_destination_warehouse_id_foreign` (`destination_warehouse_id`),
  ADD KEY `warehouse_transfers_material_id_foreign` (`material_id`),
  ADD KEY `warehouse_transfers_employee_id_foreign` (`employee_id`);

--
-- Chỉ mục cho bảng `warehouse_transfer_materials`
--
ALTER TABLE `warehouse_transfer_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_transfer_materials_warehouse_transfer_id_foreign` (`warehouse_transfer_id`),
  ADD KEY `warehouse_transfer_materials_material_id_foreign` (`material_id`);

--
-- Chỉ mục cho bảng `warranties`
--
ALTER TABLE `warranties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warranties_warranty_code_unique` (`warranty_code`),
  ADD KEY `warranties_dispatch_id_foreign` (`dispatch_id`),
  ADD KEY `warranties_dispatch_item_id_foreign` (`dispatch_item_id`),
  ADD KEY `warranties_created_by_foreign` (`created_by`),
  ADD KEY `warranties_item_type_item_id_index` (`item_type`,`item_id`),
  ADD KEY `warranties_serial_number_index` (`serial_number`(768)),
  ADD KEY `warranties_warranty_code_index` (`warranty_code`),
  ADD KEY `warranties_status_index` (`status`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `assemblies`
--
ALTER TABLE `assemblies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `assembly_materials`
--
ALTER TABLE `assembly_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `assembly_products`
--
ALTER TABLE `assembly_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `change_logs`
--
ALTER TABLE `change_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `customer_maintenance_requests`
--
ALTER TABLE `customer_maintenance_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `damaged_materials`
--
ALTER TABLE `damaged_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `device_codes`
--
ALTER TABLE `device_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `dispatches`
--
ALTER TABLE `dispatches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `dispatch_items`
--
ALTER TABLE `dispatch_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `dispatch_replacements`
--
ALTER TABLE `dispatch_replacements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `dispatch_returns`
--
ALTER TABLE `dispatch_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `goods`
--
ALTER TABLE `goods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `good_images`
--
ALTER TABLE `good_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `good_supplier`
--
ALTER TABLE `good_supplier`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `inventory_imports`
--
ALTER TABLE `inventory_imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `inventory_import_materials`
--
ALTER TABLE `inventory_import_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `maintenance_request_products`
--
ALTER TABLE `maintenance_request_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `maintenance_request_staff`
--
ALTER TABLE `maintenance_request_staff`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `materials`
--
ALTER TABLE `materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT cho bảng `material_images`
--
ALTER TABLE `material_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `material_replacement_history`
--
ALTER TABLE `material_replacement_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `material_supplier`
--
ALTER TABLE `material_supplier`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `project_requests`
--
ALTER TABLE `project_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `project_request_items`
--
ALTER TABLE `project_request_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `project_role`
--
ALTER TABLE `project_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `rental_role`
--
ALTER TABLE `rental_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `repairs`
--
ALTER TABLE `repairs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `repair_items`
--
ALTER TABLE `repair_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `role_permission`
--
ALTER TABLE `role_permission`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=509;

--
-- AUTO_INCREMENT cho bảng `serials`
--
ALTER TABLE `serials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `software`
--
ALTER TABLE `software`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `testings`
--
ALTER TABLE `testings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `testing_details`
--
ALTER TABLE `testing_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `testing_items`
--
ALTER TABLE `testing_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `warehouse_materials`
--
ALTER TABLE `warehouse_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `warehouse_transfers`
--
ALTER TABLE `warehouse_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `warehouse_transfer_materials`
--
ALTER TABLE `warehouse_transfer_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `warranties`
--
ALTER TABLE `warranties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `assemblies`
--
ALTER TABLE `assemblies`
  ADD CONSTRAINT `assemblies_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assemblies_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assemblies_target_warehouse_id_foreign` FOREIGN KEY (`target_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assemblies_tester_id_foreign` FOREIGN KEY (`tester_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assemblies_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `assembly_materials`
--
ALTER TABLE `assembly_materials`
  ADD CONSTRAINT `assembly_materials_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_materials_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_materials_serial_id_foreign` FOREIGN KEY (`serial_id`) REFERENCES `serials` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assembly_materials_target_product_id_foreign` FOREIGN KEY (`target_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `assembly_products`
--
ALTER TABLE `assembly_products`
  ADD CONSTRAINT `assembly_products_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `customer_maintenance_requests`
--
ALTER TABLE `customer_maintenance_requests`
  ADD CONSTRAINT `customer_maintenance_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_maintenance_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `damaged_materials`
--
ALTER TABLE `damaged_materials`
  ADD CONSTRAINT `damaged_materials_repair_id_foreign` FOREIGN KEY (`repair_id`) REFERENCES `repairs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `damaged_materials_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `device_codes`
--
ALTER TABLE `device_codes`
  ADD CONSTRAINT `device_codes_dispatch_id_foreign` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `dispatches`
--
ALTER TABLE `dispatches`
  ADD CONSTRAINT `dispatches_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dispatches_company_representative_id_foreign` FOREIGN KEY (`company_representative_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `dispatches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `dispatch_items`
--
ALTER TABLE `dispatch_items`
  ADD CONSTRAINT `dispatch_items_dispatch_id_foreign` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispatch_items_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Các ràng buộc cho bảng `dispatch_replacements`
--
ALTER TABLE `dispatch_replacements`
  ADD CONSTRAINT `dispatch_replacements_original_dispatch_item_id_foreign` FOREIGN KEY (`original_dispatch_item_id`) REFERENCES `dispatch_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispatch_replacements_replacement_dispatch_item_id_foreign` FOREIGN KEY (`replacement_dispatch_item_id`) REFERENCES `dispatch_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispatch_replacements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `dispatch_returns`
--
ALTER TABLE `dispatch_returns`
  ADD CONSTRAINT `dispatch_returns_dispatch_item_id_foreign` FOREIGN KEY (`dispatch_item_id`) REFERENCES `dispatch_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispatch_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dispatch_returns_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Các ràng buộc cho bảng `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `good_images`
--
ALTER TABLE `good_images`
  ADD CONSTRAINT `good_images_good_id_foreign` FOREIGN KEY (`good_id`) REFERENCES `goods` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `good_supplier`
--
ALTER TABLE `good_supplier`
  ADD CONSTRAINT `good_supplier_good_id_foreign` FOREIGN KEY (`good_id`) REFERENCES `goods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `good_supplier_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `inventory_imports`
--
ALTER TABLE `inventory_imports`
  ADD CONSTRAINT `inventory_imports_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_imports_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `inventory_import_materials`
--
ALTER TABLE `inventory_import_materials`
  ADD CONSTRAINT `inventory_import_materials_inventory_import_id_foreign` FOREIGN KEY (`inventory_import_id`) REFERENCES `inventory_imports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_import_materials_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maintenance_requests_proposer_id_foreign` FOREIGN KEY (`proposer_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maintenance_requests_warranty_id_foreign` FOREIGN KEY (`warranty_id`) REFERENCES `warranties` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `maintenance_request_products`
--
ALTER TABLE `maintenance_request_products`
  ADD CONSTRAINT `maintenance_request_products_maintenance_request_id_foreign` FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintenance_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_request_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `maintenance_request_staff`
--
ALTER TABLE `maintenance_request_staff`
  ADD CONSTRAINT `maintenance_request_staff_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_request_staff_maintenance_request_id_foreign` FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintenance_requests` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `material_images`
--
ALTER TABLE `material_images`
  ADD CONSTRAINT `material_images_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `material_replacement_history`
--
ALTER TABLE `material_replacement_history`
  ADD CONSTRAINT `material_replacement_history_repair_id_foreign` FOREIGN KEY (`repair_id`) REFERENCES `repairs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_replacement_history_replaced_by_foreign` FOREIGN KEY (`replaced_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `material_replacement_history_source_warehouse_id_foreign` FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `material_replacement_history_target_warehouse_id_foreign` FOREIGN KEY (`target_warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Các ràng buộc cho bảng `material_supplier`
--
ALTER TABLE `material_supplier`
  ADD CONSTRAINT `material_supplier_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_supplier_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_materials`
--
ALTER TABLE `product_materials`
  ADD CONSTRAINT `product_materials_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_materials_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `project_requests`
--
ALTER TABLE `project_requests`
  ADD CONSTRAINT `project_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_requests_implementer_id_foreign` FOREIGN KEY (`implementer_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_requests_proposer_id_foreign` FOREIGN KEY (`proposer_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `project_request_items`
--
ALTER TABLE `project_request_items`
  ADD CONSTRAINT `project_request_items_project_request_id_foreign` FOREIGN KEY (`project_request_id`) REFERENCES `project_requests` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `project_role`
--
ALTER TABLE `project_role`
  ADD CONSTRAINT `project_role_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `rental_role`
--
ALTER TABLE `rental_role`
  ADD CONSTRAINT `rental_role_rental_id_foreign` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rental_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `repairs`
--
ALTER TABLE `repairs`
  ADD CONSTRAINT `repairs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `repairs_maintenance_request_id_foreign` FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintenance_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `repairs_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `repairs_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `repairs_warranty_id_foreign` FOREIGN KEY (`warranty_id`) REFERENCES `warranties` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `repair_items`
--
ALTER TABLE `repair_items`
  ADD CONSTRAINT `repair_items_rejected_warehouse_id_foreign` FOREIGN KEY (`rejected_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `repair_items_repair_id_foreign` FOREIGN KEY (`repair_id`) REFERENCES `repairs` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `testings`
--
ALTER TABLE `testings`
  ADD CONSTRAINT `testings_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `testings_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testings_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testings_fail_warehouse_id_foreign` FOREIGN KEY (`fail_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `testings_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `testings_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testings_success_warehouse_id_foreign` FOREIGN KEY (`success_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `testings_tester_id_foreign` FOREIGN KEY (`tester_id`) REFERENCES `employees` (`id`);

--
-- Các ràng buộc cho bảng `testing_details`
--
ALTER TABLE `testing_details`
  ADD CONSTRAINT `testing_details_testing_id_foreign` FOREIGN KEY (`testing_id`) REFERENCES `testings` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `testing_items`
--
ALTER TABLE `testing_items`
  ADD CONSTRAINT `testing_items_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`),
  ADD CONSTRAINT `testing_items_good_id_foreign` FOREIGN KEY (`good_id`) REFERENCES `goods` (`id`),
  ADD CONSTRAINT `testing_items_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `testing_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `testing_items_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `testing_items_testing_id_foreign` FOREIGN KEY (`testing_id`) REFERENCES `testings` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `warehouse_materials`
--
ALTER TABLE `warehouse_materials`
  ADD CONSTRAINT `warehouse_materials_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `warehouse_transfers`
--
ALTER TABLE `warehouse_transfers`
  ADD CONSTRAINT `warehouse_transfers_destination_warehouse_id_foreign` FOREIGN KEY (`destination_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `warehouse_transfers_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `warehouse_transfers_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `warehouse_transfers_source_warehouse_id_foreign` FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Các ràng buộc cho bảng `warehouse_transfer_materials`
--
ALTER TABLE `warehouse_transfer_materials`
  ADD CONSTRAINT `warehouse_transfer_materials_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `warehouse_transfer_materials_warehouse_transfer_id_foreign` FOREIGN KEY (`warehouse_transfer_id`) REFERENCES `warehouse_transfers` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `warranties`
--
ALTER TABLE `warranties`
  ADD CONSTRAINT `warranties_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `warranties_dispatch_id_foreign` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warranties_dispatch_item_id_foreign` FOREIGN KEY (`dispatch_item_id`) REFERENCES `dispatch_items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
