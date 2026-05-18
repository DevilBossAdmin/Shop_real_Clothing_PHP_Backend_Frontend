-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 05, 2026 lúc 06:02 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `atino_real`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `entity`, `entity_id`, `meta_json`, `ip`, `user_agent`, `created_at`) VALUES
(1, 1, 'admin.logout', 'admin_users', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-05 18:38:48'),
(2, 1, 'admin.login', 'admin_users', 1, '{\"role\":\"superadmin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-05 18:39:11');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','sales','warehouse') NOT NULL DEFAULT 'sales',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$.a.rJXiL2qQOPqT.R90i8.9TZVSPq0VQIyjDERy.9ig5rKzDXGrvm', 'superadmin', 1, '2026-03-05 17:55:41', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `sort_order`, `created_at`) VALUES
(1, NULL, 'Nam', 'nam', 1, '2026-03-05 15:56:09'),
(2, NULL, 'Nữ', 'nu', 2, '2026-03-05 15:56:09'),
(3, NULL, 'Phụ kiện', 'phu-kien', 3, '2026-03-05 15:56:09'),
(4, 1, 'Áo nam', 'ao-nam', 1, '2026-03-05 15:56:09'),
(5, 1, 'Quần nam', 'quan-nam', 2, '2026-03-05 15:56:09'),
(6, 2, 'Áo nữ', 'ao-nu', 1, '2026-03-05 15:56:09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` int(11) NOT NULL,
  `min_order` int(11) DEFAULT 0,
  `max_discount` int(11) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 'SALE10', 'percent', 10, 0, NULL, NULL, 0, NULL, NULL, 1, '2026-03-05 18:54:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('in','out') NOT NULL,
  `qty` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `total` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL DEFAULT 0,
  `discount` int(11) NOT NULL DEFAULT 0,
  `coupon_code` varchar(50) DEFAULT NULL,
  `status` enum('pending','confirmed','shipping','completed','cancelled') DEFAULT 'pending',
  `payment_method` enum('cod','bank_transfer','qr','bank','card') DEFAULT NULL,
  `payment_status` enum('unpaid','paid','failed') DEFAULT 'unpaid',
  `tracking_code` varchar(50) DEFAULT NULL,
  `shipping_status` enum('picking','shipping','delivered','returned','cancelled') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `payment_ref` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_phone`, `customer_address`, `note`, `total`, `subtotal`, `discount`, `coupon_code`, `status`, `payment_method`, `payment_status`, `tracking_code`, `shipping_status`, `created_at`, `payment_ref`) VALUES
(1, NULL, 'Nguyễn Văn B', '01234656789', 'Hà Nội', NULL, 1406000, 1406000, 0, NULL, 'pending', 'cod', 'unpaid', NULL, NULL, '2026-03-05 18:52:49', NULL),
(2, 2, 'Nguyễn Văn B', '01234656789', 'Hà Nội', NULL, 349000, 349000, 0, NULL, 'pending', 'cod', 'unpaid', NULL, NULL, '2026-03-05 18:53:19', NULL),
(3, 2, 'Nguyễn Văn B', '01234656789', '123456', NULL, 359000, 359000, 0, NULL, 'pending', 'cod', 'unpaid', NULL, NULL, '2026-03-05 19:12:37', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `size` varchar(30) DEFAULT NULL,
  `color` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `price`, `qty`, `size`, `color`) VALUES
(1, 1, 41, NULL, 349000, 3, NULL, NULL),
(2, 1, 42, NULL, 359000, 1, NULL, NULL),
(3, 2, 41, NULL, 349000, 1, NULL, NULL),
(4, 3, 42, NULL, 359000, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `compare_at_price` int(11) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','draft') DEFAULT 'active',
  `is_new` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `price`, `compare_at_price`, `thumbnail`, `description`, `status`, `is_new`, `created_at`, `updated_at`) VALUES
(1, 4, 'Áo thun nam cotton basic', 'ao-thun-nam-cotton-basic', 'MN-TS-001', 199000, 249000, 'thumb_20260305_164500_447e74e8.png', 'Áo thun cotton 100%, thoáng mát, dễ phối đồ.', 'active', 1, '2026-03-05 16:03:40', '2026-03-05 22:45:00'),
(2, 4, 'Áo thun nam oversize street', 'ao-thun-nam-oversize-street', 'MN-TS-002', 219000, 269000, '/assets/img/products/p02.jpg', 'Form oversize trẻ trung, phù hợp phong cách streetwear.', 'active', 0, '2026-03-05 16:03:40', NULL),
(3, 4, 'Áo polo nam thun cá sấu', 'ao-polo-nam-thun-ca-sau', 'MN-PO-003', 279000, 329000, '/assets/img/products/p03.jpg', 'Polo cá sấu đứng form, thấm hút tốt.', 'active', 0, '2026-03-05 16:03:40', NULL),
(4, 4, 'Áo polo nam phối bo cổ', 'ao-polo-nam-phoi-bo-co', 'MN-PO-004', 299000, 359000, '/assets/img/products/p04.jpg', 'Thiết kế bo cổ phối màu, lịch sự năng động.', 'active', 1, '2026-03-05 16:03:40', NULL),
(5, 4, 'Áo sơ mi nam trắng công sở', 'ao-so-mi-nam-trang-cong-so', 'MN-SM-005', 349000, 399000, '/assets/img/products/p05.jpg', 'Sơ mi trắng form slimfit, hợp đi làm.', 'active', 0, '2026-03-05 16:03:40', NULL),
(6, 4, 'Áo sơ mi nam xanh nhạt', 'ao-so-mi-nam-xanh-nhat', 'MN-SM-006', 359000, 429000, '/assets/img/products/p06.jpg', 'Sơ mi màu xanh nhạt, vải ít nhăn.', 'active', 0, '2026-03-05 16:03:40', NULL),
(7, 4, 'Áo sơ mi nam caro trẻ', 'ao-so-mi-nam-caro-tre', 'MN-SM-007', 369000, 449000, '/assets/img/products/p07.jpg', 'Caro trẻ trung, hợp đi chơi/đi làm.', 'active', 1, '2026-03-05 16:03:40', NULL),
(8, 4, 'Áo khoác bomber nam', 'ao-khoac-bomber-nam', 'MN-JK-008', 599000, 699000, '/assets/img/products/p08.jpg', 'Bomber chống gió nhẹ, form gọn.', 'active', 0, '2026-03-05 16:03:40', NULL),
(9, 4, 'Áo khoác jean nam bụi bặm', 'ao-khoac-jean-nam-bui-bam', 'MN-JK-009', 689000, 799000, '/assets/img/products/p09.jpg', 'Jean dày dặn, phong cách cá tính.', 'active', 1, '2026-03-05 16:03:40', NULL),
(10, 4, 'Áo hoodie nam nỉ dày', 'ao-hoodie-nam-ni-day', 'MN-HD-010', 549000, 650000, '/assets/img/products/p10.jpg', 'Nỉ dày giữ ấm, mũ rộng, dễ mặc.', 'active', 1, '2026-03-05 16:03:40', NULL),
(11, 4, 'Áo sweater nam form rộng', 'ao-sweater-nam-form-rong', 'MN-SW-011', 479000, 560000, '/assets/img/products/p11.jpg', 'Sweater form rộng, chất nỉ mịn.', 'active', 0, '2026-03-05 16:03:40', NULL),
(12, 4, 'Áo len nam cổ lọ', 'ao-len-nam-co-lo', 'MN-KN-012', 459000, 520000, '/assets/img/products/p12.jpg', 'Len cổ lọ giữ ấm, mềm, không ngứa.', 'active', 1, '2026-03-05 16:03:40', NULL),
(13, 4, 'Áo khoác gió nam nhẹ', 'ao-khoac-gio-nam-nhe', 'MN-JK-013', 499000, 599000, '/assets/img/products/p13.jpg', 'Khoác gió nhẹ, chống nước lấm tấm.', 'active', 0, '2026-03-05 16:03:40', NULL),
(14, 4, 'Áo thun nam cổ tim', 'ao-thun-nam-co-tim', 'MN-TS-014', 189000, 239000, '/assets/img/products/p14.jpg', 'Cổ tim nhẹ, tôn dáng, dễ phối.', 'active', 0, '2026-03-05 16:03:40', NULL),
(15, 4, 'Áo tanktop nam thể thao', 'ao-tanktop-nam-the-thao', 'MN-TK-015', 169000, 219000, '/assets/img/products/p15.jpg', 'Thoáng mát, phù hợp tập luyện.', 'active', 0, '2026-03-05 16:03:40', NULL),
(16, 4, 'Áo vest nam lịch lãm', 'ao-vest-nam-lich-lam', 'MN-VS-016', 1299000, 1499000, '/assets/img/products/p16.jpg', 'Vest lịch lãm, hợp sự kiện/công sở.', 'active', 1, '2026-03-05 16:03:40', NULL),
(17, 5, 'Quần jean nam slimfit xanh đậm', 'quan-jean-nam-slimfit-xanh-dam', 'MN-JE-017', 499000, 599000, '/assets/img/products/p17.jpg', 'Jean co giãn nhẹ, form slimfit.', 'active', 1, '2026-03-05 16:03:40', NULL),
(18, 5, 'Quần jean nam đen trơn', 'quan-jean-nam-den-tron', 'MN-JE-018', 489000, 589000, '/assets/img/products/p18.jpg', 'Jean đen dễ phối, ít phai màu.', 'active', 0, '2026-03-05 16:03:40', NULL),
(19, 5, 'Quần tây nam công sở', 'quan-tay-nam-cong-so', 'MN-TR-019', 459000, 520000, '/assets/img/products/p19.jpg', 'Vải đứng form, chống nhăn nhẹ.', 'active', 0, '2026-03-05 16:03:40', NULL),
(20, 5, 'Quần kaki nam slim', 'quan-kaki-nam-slim', 'MN-KK-020', 429000, 499000, '/assets/img/products/p20.jpg', 'Kaki co giãn, mặc thoải mái cả ngày.', 'active', 1, '2026-03-05 16:03:40', NULL),
(21, 5, 'Quần jogger nam thể thao', 'quan-jogger-nam-the-thao', 'MN-JG-021', 339000, 399000, '/assets/img/products/p21.jpg', 'Jogger gọn gàng, bo gấu, năng động.', 'active', 0, '2026-03-05 16:03:40', NULL),
(22, 5, 'Quần short nam basic', 'quan-short-nam-basic', 'MN-SH-022', 229000, 279000, '/assets/img/products/p22.jpg', 'Short basic, chất liệu thoáng mát.', 'active', 0, '2026-03-05 16:03:40', NULL),
(23, 5, 'Quần short nam thể thao', 'quan-short-nam-the-thao', 'MN-SH-023', 239000, 299000, '/assets/img/products/p23.jpg', 'Co giãn tốt, phù hợp vận động.', 'active', 1, '2026-03-05 16:03:40', NULL),
(24, 5, 'Quần cargo nam túi hộp', 'quan-cargo-nam-tui-hop', 'MN-CG-024', 459000, 549000, '/assets/img/products/p24.jpg', 'Cargo nhiều túi, phong cách mạnh mẽ.', 'active', 1, '2026-03-05 16:03:40', NULL),
(25, 5, 'Quần chinos nam', 'quan-chinos-nam', 'MN-CH-025', 439000, 519000, '/assets/img/products/p25.jpg', 'Chinos lịch sự, hợp đi làm/đi chơi.', 'active', 0, '2026-03-05 16:03:40', NULL),
(26, 5, 'Quần nỉ nam ống suông', 'quan-ni-nam-ong-suong', 'MN-NI-026', 299000, 369000, '/assets/img/products/p26.jpg', 'Nỉ mềm, ống suông dễ mặc.', 'active', 0, '2026-03-05 16:03:40', NULL),
(27, 6, 'Áo thun nữ basic cổ tròn', 'ao-thun-nu-basic-co-tron', 'WM-TS-027', 189000, 239000, '/assets/img/products/p27.jpg', 'Áo thun nữ mềm mịn, dễ phối.', 'active', 1, '2026-03-05 16:03:40', NULL),
(28, 6, 'Áo thun nữ crop top', 'ao-thun-nu-crop-top', 'WM-TS-028', 179000, 229000, '/assets/img/products/p28.jpg', 'Crop top trẻ trung, tôn dáng.', 'active', 0, '2026-03-05 16:03:40', NULL),
(29, 6, 'Áo sơ mi nữ trắng công sở', 'ao-so-mi-nu-trang-cong-so', 'WM-SM-029', 339000, 399000, '/assets/img/products/p29.jpg', 'Sơ mi nữ thanh lịch, ít nhăn.', 'active', 0, '2026-03-05 16:03:40', NULL),
(30, 6, 'Áo blouse nữ tay bồng', 'ao-blouse-nu-tay-bong', 'WM-BL-030', 319000, 389000, '/assets/img/products/p30.jpg', 'Blouse tay bồng nhẹ, nữ tính.', 'active', 1, '2026-03-05 16:03:40', NULL),
(31, 6, 'Áo cardigan nữ mỏng', 'ao-cardigan-nu-mong', 'WM-CD-031', 359000, 429000, '/assets/img/products/p31.jpg', 'Cardigan mỏng, mặc khoác tiện lợi.', 'active', 0, '2026-03-05 16:03:40', NULL),
(32, 6, 'Áo hoodie nữ form rộng', 'ao-hoodie-nu-form-rong', 'WM-HD-032', 499000, 599000, '/assets/img/products/p32.jpg', 'Hoodie nữ form rộng, phong cách.', 'active', 1, '2026-03-05 16:03:40', NULL),
(33, 6, 'Áo len nữ cổ tim', 'ao-len-nu-co-tim', 'WM-KN-033', 399000, 469000, '/assets/img/products/p33.jpg', 'Len cổ tim mềm, giữ ấm tốt.', 'active', 0, '2026-03-05 16:03:40', NULL),
(34, 6, 'Áo polo nữ basic', 'ao-polo-nu-basic', 'WM-PO-034', 259000, 319000, '/assets/img/products/p34.jpg', 'Polo nữ basic, dễ mặc hàng ngày.', 'active', 0, '2026-03-05 16:03:40', NULL),
(35, 3, 'Thắt lưng da nam', 'that-lung-da-nam', 'AC-BL-035', 249000, 299000, '/assets/img/products/p35.jpg', 'Thắt lưng da PU cao cấp, khóa kim.', 'active', 0, '2026-03-05 16:03:40', NULL),
(36, 3, 'Ví da nam mini', 'vi-da-nam-mini', 'AC-WL-036', 219000, 269000, '/assets/img/products/p36.jpg', 'Ví mini gọn nhẹ, nhiều ngăn.', 'active', 1, '2026-03-05 16:03:40', NULL),
(37, 3, 'Mũ lưỡi trai basic', 'mu-luoi-trai-basic', 'AC-CP-037', 149000, 199000, '/assets/img/products/p37.jpg', 'Mũ lưỡi trai basic, dễ phối đồ.', 'active', 0, '2026-03-05 16:03:40', NULL),
(38, 3, 'Tất nam cổ ngắn (3 đôi)', 'tat-nam-co-ngan-3-doi', 'AC-SO-038', 99000, 129000, '/assets/img/products/p38.jpg', 'Chất cotton, thấm hút, 3 đôi/set.', 'active', 0, '2026-03-05 16:03:40', NULL),
(39, 3, 'Túi đeo chéo thời trang', 'tui-deo-cheo-thoi-trang', 'AC-BG-039', 299000, 359000, '/assets/img/products/p39.jpg', 'Túi đeo chéo nhỏ gọn, tiện dụng.', 'active', 1, '2026-03-05 16:03:40', NULL),
(40, 3, 'Kính mát thời trang', 'kinh-mat-thoi-trang', 'AC-SG-040', 199000, 249000, '/assets/img/products/p40.jpg', 'Kính mát chống chói, kiểu dáng basic.', 'active', 0, '2026-03-05 16:03:40', NULL),
(41, 4, 'Áo sơ mi nam trắng slimfit', 'ao-so-mi-nam-trang-slimfit', 'MSH-001', 349000, 399000, 'thumb_20260305_154511_77294008.png', 'Sơ mi trắng slimfit thanh lịch.', 'active', 1, '2026-03-05 16:06:57', '2026-03-05 21:45:11'),
(42, 4, 'Áo sơ mi nam xanh công sở', 'ao-so-mi-nam-xanh-cong-so', 'MSH-002', 359000, 419000, 'thumb_20260305_164706_e24f19c1.png', 'Sơ mi xanh nhạt phù hợp môi trường công sở.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:47:06'),
(43, 4, 'Áo sơ mi nam caro đỏ', 'ao-so-mi-nam-caro-do', 'MSH-003', 369000, 429000, 'thumb_20260305_164739_6e552956.png', 'Họa tiết caro trẻ trung.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:47:39'),
(44, 4, 'Áo sơ mi nam caro xanh', 'ao-so-mi-nam-caro-xanh', 'MSH-004', 369000, 429000, 'thumb_20260305_164810_0a33f4bd.png', 'Form slimfit hiện đại.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:48:10'),
(45, 4, 'Áo sơ mi nam denim', 'ao-so-mi-nam-denim', 'MSH-005', 399000, 459000, 'thumb_20260305_164843_dd6acfe2.png', 'Chất denim mềm, phong cách bụi bặm.', 'active', 1, '2026-03-05 16:06:57', '2026-03-05 22:48:43'),
(46, 4, 'Áo sơ mi nam tay ngắn', 'ao-so-mi-nam-tay-ngan', 'MSH-006', 329000, 379000, 'thumb_20260305_164915_3145f476.png', 'Sơ mi tay ngắn thoáng mát.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:49:15'),
(47, 4, 'Áo sơ mi nam họa tiết', 'ao-so-mi-nam-hoa-tiet', 'MSH-007', 389000, 449000, 'thumb_20260305_164940_01d854f1.png', 'Thiết kế họa tiết trẻ trung.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:49:40'),
(48, 4, 'Áo thun nam cotton basic', 'ao-thun-nam-cotton-basic-2', 'MTS-008', 199000, 249000, 'thumb_20260305_165007_4acff7a5.png', 'Áo thun cotton 100%.', 'active', 1, '2026-03-05 16:06:57', '2026-03-05 22:50:07'),
(49, 4, 'Áo thun nam oversize', 'ao-thun-nam-oversize-2', 'MTS-009', 219000, 269000, 'thumb_20260305_165054_e1142904.png', 'Form rộng phong cách Hàn.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:50:54'),
(50, 4, 'Áo thun nam cổ tim', 'ao-thun-nam-co-tim-2', 'MTS-010', 189000, 239000, 'thumb_20260305_165125_44cb10fe.png', 'Cổ tim nhẹ, tôn dáng.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:51:25'),
(51, 4, 'Áo thun nam graphic', 'ao-thun-nam-graphic', 'MTS-011', 229000, 289000, 'thumb_20260305_165202_fd570db3.png', 'Áo thun in hình cá tính.', 'active', 1, '2026-03-05 16:06:57', '2026-03-05 22:52:02'),
(52, 4, 'Áo thun nam thể thao', 'ao-thun-nam-the-thao', 'MTS-012', 239000, 299000, 'thumb_20260305_165233_90fa3a8e.png', 'Chất liệu co giãn tốt.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:52:33'),
(53, 4, 'Áo thun nam cổ trụ', 'ao-thun-nam-co-tru', 'MTS-013', 249000, 309000, '/assets/img/products/m13.jpg', 'Thiết kế cổ trụ lịch sự.', 'active', 0, '2026-03-05 16:06:57', NULL),
(54, 4, 'Áo thun nam streetwear', 'ao-thun-nam-streetwear', 'MTS-014', 259000, 319000, 'thumb_20260305_165259_064829ba.png', 'Phong cách streetwear trẻ trung.', 'active', 1, '2026-03-05 16:06:57', '2026-03-05 22:52:59'),
(55, 6, 'Quần jean nữ ống suông', 'quan-jean-nu-ong-suong', 'WFJ-015', 399000, 459000, 'thumb_20260305_165333_9b1eb517.png', 'Jean nữ ống suông thời trang.', 'active', 1, '2026-03-05 16:06:57', '2026-03-05 22:53:33'),
(56, 6, 'Quần jean nữ skinny', 'quan-jean-nu-skinny', 'WFJ-016', 389000, 449000, 'thumb_20260305_165412_6746a381.png', 'Jean ôm dáng tôn dáng.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:54:12'),
(57, 6, 'Quần tây nữ công sở', 'quan-tay-nu-cong-so', 'WFT-017', 359000, 419000, 'thumb_20260305_165433_5821d3ef.png', 'Quần tây nữ form chuẩn.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:54:33'),
(58, 6, 'Quần short nữ basic', 'quan-short-nu-basic', 'WFS-018', 229000, 279000, 'thumb_20260305_165458_e038a78b.png', 'Short nữ năng động.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:54:58'),
(59, 6, 'Quần jogger nữ thể thao', 'quan-jogger-nu-the-thao', 'WFJ-019', 299000, 359000, 'thumb_20260305_165521_64ec97ad.png', 'Jogger nữ thoải mái.', 'active', 1, '2026-03-05 16:06:57', '2026-03-05 22:55:21'),
(60, 6, 'Quần kaki nữ form rộng', 'quan-kaki-nu-form-rong', 'WFK-020', 339000, 399000, 'thumb_20260305_165540_8b485915.png', 'Kaki nữ form rộng hiện đại.', 'active', 0, '2026-03-05 16:06:57', '2026-03-05 22:55:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(30) DEFAULT NULL,
  `color` varchar(40) DEFAULT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `stock`) VALUES
(14, 2, 'S', 'Đen', 17),
(15, 2, 'M', 'Đen', 5),
(16, 2, 'L', 'Đen', 11),
(17, 2, 'S', 'Trắng', 12),
(18, 2, 'M', 'Trắng', 5),
(19, 2, 'L', 'Trắng', 25),
(20, 2, 'S', 'Xanh navy', 21),
(21, 2, 'M', 'Xanh navy', 6),
(22, 2, 'L', 'Xanh navy', 25),
(23, 3, 'S', 'Đen', 19),
(24, 3, 'M', 'Đen', 13),
(25, 3, 'L', 'Đen', 6),
(26, 3, 'S', 'Trắng', 7),
(27, 3, 'M', 'Trắng', 14),
(28, 3, 'L', 'Trắng', 21),
(29, 3, 'S', 'Xanh navy', 16),
(30, 3, 'M', 'Xanh navy', 15),
(31, 3, 'L', 'Xanh navy', 20),
(32, 4, 'S', 'Đen', 8),
(33, 4, 'M', 'Đen', 18),
(34, 4, 'L', 'Đen', 19),
(35, 4, 'S', 'Trắng', 15),
(36, 4, 'M', 'Trắng', 14),
(37, 4, 'L', 'Trắng', 19),
(38, 4, 'S', 'Xanh navy', 6),
(39, 4, 'M', 'Xanh navy', 11),
(40, 4, 'L', 'Xanh navy', 13),
(41, 5, 'S', 'Đen', 25),
(42, 5, 'M', 'Đen', 19),
(43, 5, 'L', 'Đen', 15),
(44, 5, 'S', 'Trắng', 14),
(45, 5, 'M', 'Trắng', 21),
(46, 5, 'L', 'Trắng', 14),
(47, 5, 'S', 'Xanh navy', 5),
(48, 5, 'M', 'Xanh navy', 19),
(49, 5, 'L', 'Xanh navy', 12),
(50, 6, 'S', 'Đen', 20),
(51, 6, 'M', 'Đen', 19),
(52, 6, 'L', 'Đen', 10),
(53, 6, 'S', 'Trắng', 8),
(54, 6, 'M', 'Trắng', 7),
(55, 6, 'L', 'Trắng', 6),
(56, 6, 'S', 'Xanh navy', 5),
(57, 6, 'M', 'Xanh navy', 24),
(58, 6, 'L', 'Xanh navy', 15),
(59, 7, 'S', 'Đen', 21),
(60, 7, 'M', 'Đen', 14),
(61, 7, 'L', 'Đen', 25),
(62, 7, 'S', 'Trắng', 12),
(63, 7, 'M', 'Trắng', 23),
(64, 7, 'L', 'Trắng', 12),
(65, 7, 'S', 'Xanh navy', 9),
(66, 7, 'M', 'Xanh navy', 24),
(67, 7, 'L', 'Xanh navy', 25),
(68, 8, 'S', 'Đen', 7),
(69, 8, 'M', 'Đen', 20),
(70, 8, 'L', 'Đen', 11),
(71, 8, 'S', 'Trắng', 9),
(72, 8, 'M', 'Trắng', 10),
(73, 8, 'L', 'Trắng', 16),
(74, 8, 'S', 'Xanh navy', 25),
(75, 8, 'M', 'Xanh navy', 9),
(76, 8, 'L', 'Xanh navy', 8),
(77, 9, 'S', 'Đen', 7),
(78, 9, 'M', 'Đen', 8),
(79, 9, 'L', 'Đen', 15),
(80, 9, 'S', 'Trắng', 5),
(81, 9, 'M', 'Trắng', 16),
(82, 9, 'L', 'Trắng', 21),
(83, 9, 'S', 'Xanh navy', 10),
(84, 9, 'M', 'Xanh navy', 22),
(85, 9, 'L', 'Xanh navy', 14),
(86, 10, 'S', 'Đen', 20),
(87, 10, 'M', 'Đen', 13),
(88, 10, 'L', 'Đen', 19),
(89, 10, 'S', 'Trắng', 11),
(90, 10, 'M', 'Trắng', 13),
(91, 10, 'L', 'Trắng', 8),
(92, 10, 'S', 'Xanh navy', 18),
(93, 10, 'M', 'Xanh navy', 19),
(94, 10, 'L', 'Xanh navy', 14),
(95, 11, 'S', 'Đen', 10),
(96, 11, 'M', 'Đen', 5),
(97, 11, 'L', 'Đen', 9),
(98, 11, 'S', 'Trắng', 5),
(99, 11, 'M', 'Trắng', 15),
(100, 11, 'L', 'Trắng', 14),
(101, 11, 'S', 'Xanh navy', 22),
(102, 11, 'M', 'Xanh navy', 19),
(103, 11, 'L', 'Xanh navy', 24),
(104, 12, 'S', 'Đen', 17),
(105, 12, 'M', 'Đen', 9),
(106, 12, 'L', 'Đen', 11),
(107, 12, 'S', 'Trắng', 25),
(108, 12, 'M', 'Trắng', 22),
(109, 12, 'L', 'Trắng', 9),
(110, 12, 'S', 'Xanh navy', 19),
(111, 12, 'M', 'Xanh navy', 21),
(112, 12, 'L', 'Xanh navy', 21),
(113, 13, 'S', 'Đen', 16),
(114, 13, 'M', 'Đen', 15),
(115, 13, 'L', 'Đen', 20),
(116, 13, 'S', 'Trắng', 9),
(117, 13, 'M', 'Trắng', 22),
(118, 13, 'L', 'Trắng', 17),
(119, 13, 'S', 'Xanh navy', 12),
(120, 13, 'M', 'Xanh navy', 6),
(121, 13, 'L', 'Xanh navy', 11),
(122, 14, 'S', 'Đen', 11),
(123, 14, 'M', 'Đen', 18),
(124, 14, 'L', 'Đen', 10),
(125, 14, 'S', 'Trắng', 15),
(126, 14, 'M', 'Trắng', 16),
(127, 14, 'L', 'Trắng', 12),
(128, 14, 'S', 'Xanh navy', 7),
(129, 14, 'M', 'Xanh navy', 16),
(130, 14, 'L', 'Xanh navy', 11),
(131, 15, 'S', 'Đen', 25),
(132, 15, 'M', 'Đen', 24),
(133, 15, 'L', 'Đen', 20),
(134, 15, 'S', 'Trắng', 23),
(135, 15, 'M', 'Trắng', 9),
(136, 15, 'L', 'Trắng', 14),
(137, 15, 'S', 'Xanh navy', 18),
(138, 15, 'M', 'Xanh navy', 21),
(139, 15, 'L', 'Xanh navy', 7),
(140, 16, 'S', 'Đen', 7),
(141, 16, 'M', 'Đen', 12),
(142, 16, 'L', 'Đen', 12),
(143, 16, 'S', 'Trắng', 19),
(144, 16, 'M', 'Trắng', 11),
(145, 16, 'L', 'Trắng', 16),
(146, 16, 'S', 'Xanh navy', 23),
(147, 16, 'M', 'Xanh navy', 18),
(148, 16, 'L', 'Xanh navy', 19),
(149, 17, 'S', 'Đen', 16),
(150, 17, 'M', 'Đen', 19),
(151, 17, 'L', 'Đen', 22),
(152, 17, 'S', 'Trắng', 5),
(153, 17, 'M', 'Trắng', 16),
(154, 17, 'L', 'Trắng', 21),
(155, 17, 'S', 'Xanh navy', 10),
(156, 17, 'M', 'Xanh navy', 24),
(157, 17, 'L', 'Xanh navy', 23),
(158, 18, 'S', 'Đen', 19),
(159, 18, 'M', 'Đen', 20),
(160, 18, 'L', 'Đen', 16),
(161, 18, 'S', 'Trắng', 16),
(162, 18, 'M', 'Trắng', 9),
(163, 18, 'L', 'Trắng', 11),
(164, 18, 'S', 'Xanh navy', 25),
(165, 18, 'M', 'Xanh navy', 5),
(166, 18, 'L', 'Xanh navy', 7),
(167, 19, 'S', 'Đen', 14),
(168, 19, 'M', 'Đen', 5),
(169, 19, 'L', 'Đen', 17),
(170, 19, 'S', 'Trắng', 6),
(171, 19, 'M', 'Trắng', 17),
(172, 19, 'L', 'Trắng', 18),
(173, 19, 'S', 'Xanh navy', 13),
(174, 19, 'M', 'Xanh navy', 8),
(175, 19, 'L', 'Xanh navy', 19),
(176, 20, 'S', 'Đen', 23),
(177, 20, 'M', 'Đen', 12),
(178, 20, 'L', 'Đen', 9),
(179, 20, 'S', 'Trắng', 25),
(180, 20, 'M', 'Trắng', 11),
(181, 20, 'L', 'Trắng', 16),
(182, 20, 'S', 'Xanh navy', 21),
(183, 20, 'M', 'Xanh navy', 10),
(184, 20, 'L', 'Xanh navy', 25),
(185, 21, 'S', 'Đen', 7),
(186, 21, 'M', 'Đen', 18),
(187, 21, 'L', 'Đen', 23),
(188, 21, 'S', 'Trắng', 15),
(189, 21, 'M', 'Trắng', 20),
(190, 21, 'L', 'Trắng', 11),
(191, 21, 'S', 'Xanh navy', 12),
(192, 21, 'M', 'Xanh navy', 22),
(193, 21, 'L', 'Xanh navy', 6),
(194, 22, 'S', 'Đen', 22),
(195, 22, 'M', 'Đen', 6),
(196, 22, 'L', 'Đen', 21),
(197, 22, 'S', 'Trắng', 21),
(198, 22, 'M', 'Trắng', 18),
(199, 22, 'L', 'Trắng', 19),
(200, 22, 'S', 'Xanh navy', 15),
(201, 22, 'M', 'Xanh navy', 16),
(202, 22, 'L', 'Xanh navy', 10),
(203, 23, 'S', 'Đen', 17),
(204, 23, 'M', 'Đen', 11),
(205, 23, 'L', 'Đen', 19),
(206, 23, 'S', 'Trắng', 15),
(207, 23, 'M', 'Trắng', 13),
(208, 23, 'L', 'Trắng', 18),
(209, 23, 'S', 'Xanh navy', 23),
(210, 23, 'M', 'Xanh navy', 14),
(211, 23, 'L', 'Xanh navy', 19),
(212, 24, 'S', 'Đen', 25),
(213, 24, 'M', 'Đen', 24),
(214, 24, 'L', 'Đen', 17),
(215, 24, 'S', 'Trắng', 9),
(216, 24, 'M', 'Trắng', 12),
(217, 24, 'L', 'Trắng', 8),
(218, 24, 'S', 'Xanh navy', 21),
(219, 24, 'M', 'Xanh navy', 13),
(220, 24, 'L', 'Xanh navy', 18),
(221, 25, 'S', 'Đen', 25),
(222, 25, 'M', 'Đen', 23),
(223, 25, 'L', 'Đen', 17),
(224, 25, 'S', 'Trắng', 10),
(225, 25, 'M', 'Trắng', 14),
(226, 25, 'L', 'Trắng', 18),
(227, 25, 'S', 'Xanh navy', 20),
(228, 25, 'M', 'Xanh navy', 22),
(229, 25, 'L', 'Xanh navy', 25),
(230, 26, 'S', 'Đen', 13),
(231, 26, 'M', 'Đen', 25),
(232, 26, 'L', 'Đen', 18),
(233, 26, 'S', 'Trắng', 11),
(234, 26, 'M', 'Trắng', 19),
(235, 26, 'L', 'Trắng', 13),
(236, 26, 'S', 'Xanh navy', 5),
(237, 26, 'M', 'Xanh navy', 24),
(238, 26, 'L', 'Xanh navy', 15),
(239, 27, 'S', 'Đen', 20),
(240, 27, 'M', 'Đen', 9),
(241, 27, 'L', 'Đen', 23),
(242, 27, 'S', 'Trắng', 20),
(243, 27, 'M', 'Trắng', 6),
(244, 27, 'L', 'Trắng', 6),
(245, 27, 'S', 'Xanh navy', 7),
(246, 27, 'M', 'Xanh navy', 15),
(247, 27, 'L', 'Xanh navy', 5),
(248, 28, 'S', 'Đen', 17),
(249, 28, 'M', 'Đen', 25),
(250, 28, 'L', 'Đen', 6),
(251, 28, 'S', 'Trắng', 13),
(252, 28, 'M', 'Trắng', 20),
(253, 28, 'L', 'Trắng', 17),
(254, 28, 'S', 'Xanh navy', 21),
(255, 28, 'M', 'Xanh navy', 5),
(256, 28, 'L', 'Xanh navy', 19),
(257, 29, 'S', 'Đen', 16),
(258, 29, 'M', 'Đen', 19),
(259, 29, 'L', 'Đen', 22),
(260, 29, 'S', 'Trắng', 24),
(261, 29, 'M', 'Trắng', 10),
(262, 29, 'L', 'Trắng', 15),
(263, 29, 'S', 'Xanh navy', 19),
(264, 29, 'M', 'Xanh navy', 25),
(265, 29, 'L', 'Xanh navy', 23),
(266, 30, 'S', 'Đen', 13),
(267, 30, 'M', 'Đen', 11),
(268, 30, 'L', 'Đen', 15),
(269, 30, 'S', 'Trắng', 14),
(270, 30, 'M', 'Trắng', 22),
(271, 30, 'L', 'Trắng', 21),
(272, 30, 'S', 'Xanh navy', 13),
(273, 30, 'M', 'Xanh navy', 17),
(274, 30, 'L', 'Xanh navy', 21),
(275, 31, 'S', 'Đen', 6),
(276, 31, 'M', 'Đen', 8),
(277, 31, 'L', 'Đen', 16),
(278, 31, 'S', 'Trắng', 10),
(279, 31, 'M', 'Trắng', 18),
(280, 31, 'L', 'Trắng', 14),
(281, 31, 'S', 'Xanh navy', 13),
(282, 31, 'M', 'Xanh navy', 17),
(283, 31, 'L', 'Xanh navy', 22),
(284, 32, 'S', 'Đen', 11),
(285, 32, 'M', 'Đen', 6),
(286, 32, 'L', 'Đen', 14),
(287, 32, 'S', 'Trắng', 5),
(288, 32, 'M', 'Trắng', 21),
(289, 32, 'L', 'Trắng', 23),
(290, 32, 'S', 'Xanh navy', 25),
(291, 32, 'M', 'Xanh navy', 8),
(292, 32, 'L', 'Xanh navy', 25),
(293, 33, 'S', 'Đen', 14),
(294, 33, 'M', 'Đen', 12),
(295, 33, 'L', 'Đen', 11),
(296, 33, 'S', 'Trắng', 17),
(297, 33, 'M', 'Trắng', 5),
(298, 33, 'L', 'Trắng', 9),
(299, 33, 'S', 'Xanh navy', 6),
(300, 33, 'M', 'Xanh navy', 20),
(301, 33, 'L', 'Xanh navy', 16),
(302, 34, 'S', 'Đen', 13),
(303, 34, 'M', 'Đen', 14),
(304, 34, 'L', 'Đen', 6),
(305, 34, 'S', 'Trắng', 5),
(306, 34, 'M', 'Trắng', 24),
(307, 34, 'L', 'Trắng', 15),
(308, 34, 'S', 'Xanh navy', 21),
(309, 34, 'M', 'Xanh navy', 14),
(310, 34, 'L', 'Xanh navy', 22),
(311, 35, 'S', 'Đen', 21),
(312, 35, 'M', 'Đen', 14),
(313, 35, 'L', 'Đen', 24),
(314, 35, 'S', 'Trắng', 11),
(315, 35, 'M', 'Trắng', 20),
(316, 35, 'L', 'Trắng', 21),
(317, 35, 'S', 'Xanh navy', 18),
(318, 35, 'M', 'Xanh navy', 22),
(319, 35, 'L', 'Xanh navy', 10),
(320, 36, 'S', 'Đen', 22),
(321, 36, 'M', 'Đen', 11),
(322, 36, 'L', 'Đen', 7),
(323, 36, 'S', 'Trắng', 19),
(324, 36, 'M', 'Trắng', 25),
(325, 36, 'L', 'Trắng', 23),
(326, 36, 'S', 'Xanh navy', 12),
(327, 36, 'M', 'Xanh navy', 11),
(328, 36, 'L', 'Xanh navy', 13),
(329, 37, 'S', 'Đen', 7),
(330, 37, 'M', 'Đen', 12),
(331, 37, 'L', 'Đen', 13),
(332, 37, 'S', 'Trắng', 23),
(333, 37, 'M', 'Trắng', 10),
(334, 37, 'L', 'Trắng', 18),
(335, 37, 'S', 'Xanh navy', 14),
(336, 37, 'M', 'Xanh navy', 9),
(337, 37, 'L', 'Xanh navy', 23),
(338, 38, 'S', 'Đen', 19),
(339, 38, 'M', 'Đen', 23),
(340, 38, 'L', 'Đen', 11),
(341, 38, 'S', 'Trắng', 25),
(342, 38, 'M', 'Trắng', 22),
(343, 38, 'L', 'Trắng', 12),
(344, 38, 'S', 'Xanh navy', 7),
(345, 38, 'M', 'Xanh navy', 18),
(346, 38, 'L', 'Xanh navy', 23),
(347, 39, 'S', 'Đen', 15),
(348, 39, 'M', 'Đen', 21),
(349, 39, 'L', 'Đen', 14),
(350, 39, 'S', 'Trắng', 23),
(351, 39, 'M', 'Trắng', 25),
(352, 39, 'L', 'Trắng', 11),
(353, 39, 'S', 'Xanh navy', 17),
(354, 39, 'M', 'Xanh navy', 5),
(355, 39, 'L', 'Xanh navy', 10),
(356, 40, 'S', 'Đen', 13),
(357, 40, 'M', 'Đen', 6),
(358, 40, 'L', 'Đen', 11),
(359, 40, 'S', 'Trắng', 9),
(360, 40, 'M', 'Trắng', 10),
(361, 40, 'L', 'Trắng', 17),
(362, 40, 'S', 'Xanh navy', 7),
(363, 40, 'M', 'Xanh navy', 24),
(364, 40, 'L', 'Xanh navy', 8),
(473, 53, 'S', 'Đen', 11),
(474, 53, 'M', 'Đen', 9),
(475, 53, 'L', 'Đen', 8),
(476, 53, 'S', 'Trắng', 7),
(477, 53, 'M', 'Trắng', 6),
(478, 53, 'L', 'Trắng', 5),
(479, 53, 'S', 'Xanh navy', 24),
(480, 53, 'M', 'Xanh navy', 15),
(481, 53, 'L', 'Xanh navy', 22),
(1109, 41, 'L', 'Đen', 9),
(1110, 41, 'L', 'Trắng', 11),
(1111, 41, 'L', 'Xanh navy', 24),
(1112, 41, 'M', 'Đen', 18),
(1113, 41, 'M', 'Trắng', 19),
(1114, 41, 'M', 'Xanh navy', 19),
(1115, 41, 'S', 'Đen', 5),
(1116, 41, 'S', 'Trắng', 6),
(1117, 41, 'S', 'Xanh navy', 16),
(1118, 1, 'L', 'Đen', 24),
(1119, 1, 'L', 'Trắng', 20),
(1120, 1, 'L', 'Xanh navy', 5),
(1121, 1, 'M', 'Đen', 23),
(1122, 1, 'M', 'Trắng', 24),
(1123, 1, 'M', 'Xanh navy', 21),
(1124, 1, 'S', 'Đen', 15),
(1125, 1, 'S', 'Trắng', 24),
(1126, 1, 'S', 'Xanh navy', 5),
(1145, 42, 'L', 'Đen', 10),
(1146, 42, 'L', 'Trắng', 13),
(1147, 42, 'L', 'Xanh navy', 6),
(1148, 42, 'M', 'Đen', 16),
(1149, 42, 'M', 'Trắng', 24),
(1150, 42, 'M', 'Xanh navy', 13),
(1151, 42, 'S', 'Đen', 14),
(1152, 42, 'S', 'Trắng', 20),
(1153, 42, 'S', 'Xanh navy', 12),
(1154, 43, 'L', 'Đen', 25),
(1155, 43, 'L', 'Trắng', 21),
(1156, 43, 'L', 'Xanh navy', 18),
(1157, 43, 'M', 'Đen', 5),
(1158, 43, 'M', 'Trắng', 21),
(1159, 43, 'M', 'Xanh navy', 5),
(1160, 43, 'S', 'Đen', 5),
(1161, 43, 'S', 'Trắng', 20),
(1162, 43, 'S', 'Xanh navy', 14),
(1163, 44, 'L', 'Đen', 15),
(1164, 44, 'L', 'Trắng', 13),
(1165, 44, 'L', 'Xanh navy', 18),
(1166, 44, 'M', 'Đen', 10),
(1167, 44, 'M', 'Trắng', 12),
(1168, 44, 'M', 'Xanh navy', 15),
(1169, 44, 'S', 'Đen', 10),
(1170, 44, 'S', 'Trắng', 21),
(1171, 44, 'S', 'Xanh navy', 24),
(1172, 45, 'L', 'Đen', 23),
(1173, 45, 'L', 'Trắng', 19),
(1174, 45, 'L', 'Xanh navy', 5),
(1175, 45, 'M', 'Đen', 22),
(1176, 45, 'M', 'Trắng', 23),
(1177, 45, 'M', 'Xanh navy', 23),
(1178, 45, 'S', 'Đen', 20),
(1179, 45, 'S', 'Trắng', 23),
(1180, 45, 'S', 'Xanh navy', 21),
(1181, 46, 'L', 'Đen', 24),
(1182, 46, 'L', 'Trắng', 12),
(1183, 46, 'L', 'Xanh navy', 13),
(1184, 46, 'M', 'Đen', 15),
(1185, 46, 'M', 'Trắng', 13),
(1186, 46, 'M', 'Xanh navy', 12),
(1187, 46, 'S', 'Đen', 15),
(1188, 46, 'S', 'Trắng', 10),
(1189, 46, 'S', 'Xanh navy', 15),
(1190, 47, 'L', 'Đen', 5),
(1191, 47, 'L', 'Trắng', 6),
(1192, 47, 'L', 'Xanh navy', 16),
(1193, 47, 'M', 'Đen', 23),
(1194, 47, 'M', 'Trắng', 22),
(1195, 47, 'M', 'Xanh navy', 21),
(1196, 47, 'S', 'Đen', 21),
(1197, 47, 'S', 'Trắng', 12),
(1198, 47, 'S', 'Xanh navy', 21),
(1199, 48, 'L', 'Đen', 22),
(1200, 48, 'L', 'Trắng', 16),
(1201, 48, 'L', 'Xanh navy', 13),
(1202, 48, 'M', 'Đen', 18),
(1203, 48, 'M', 'Trắng', 23),
(1204, 48, 'M', 'Xanh navy', 20),
(1205, 48, 'S', 'Đen', 14),
(1206, 48, 'S', 'Trắng', 10),
(1207, 48, 'S', 'Xanh navy', 6),
(1208, 49, 'L', 'Đen', 15),
(1209, 49, 'L', 'Trắng', 23),
(1210, 49, 'L', 'Xanh navy', 16),
(1211, 49, 'M', 'Đen', 5),
(1212, 49, 'M', 'Trắng', 11),
(1213, 49, 'M', 'Xanh navy', 7),
(1214, 49, 'S', 'Đen', 22),
(1215, 49, 'S', 'Trắng', 17),
(1216, 49, 'S', 'Xanh navy', 12),
(1217, 50, 'L', 'Đen', 14),
(1218, 50, 'L', 'Trắng', 12),
(1219, 50, 'L', 'Xanh navy', 19),
(1220, 50, 'M', 'Đen', 21),
(1221, 50, 'M', 'Trắng', 14),
(1222, 50, 'M', 'Xanh navy', 9),
(1223, 50, 'S', 'Đen', 15),
(1224, 50, 'S', 'Trắng', 25),
(1225, 50, 'S', 'Xanh navy', 14),
(1226, 51, 'L', 'Đen', 7),
(1227, 51, 'L', 'Trắng', 22),
(1228, 51, 'L', 'Xanh navy', 11),
(1229, 51, 'M', 'Đen', 23),
(1230, 51, 'M', 'Trắng', 12),
(1231, 51, 'M', 'Xanh navy', 12),
(1232, 51, 'S', 'Đen', 21),
(1233, 51, 'S', 'Trắng', 24),
(1234, 51, 'S', 'Xanh navy', 8),
(1235, 52, 'L', 'Đen', 15),
(1236, 52, 'L', 'Trắng', 5),
(1237, 52, 'L', 'Xanh navy', 7),
(1238, 52, 'M', 'Đen', 17),
(1239, 52, 'M', 'Trắng', 16),
(1240, 52, 'M', 'Xanh navy', 6),
(1241, 52, 'S', 'Đen', 15),
(1242, 52, 'S', 'Trắng', 17),
(1243, 52, 'S', 'Xanh navy', 14),
(1244, 54, 'L', 'Đen', 5),
(1245, 54, 'L', 'Trắng', 13),
(1246, 54, 'L', 'Xanh navy', 19),
(1247, 54, 'M', 'Đen', 16),
(1248, 54, 'M', 'Trắng', 19),
(1249, 54, 'M', 'Xanh navy', 15),
(1250, 54, 'S', 'Đen', 17),
(1251, 54, 'S', 'Trắng', 12),
(1252, 54, 'S', 'Xanh navy', 24),
(1253, 55, 'L', 'Đen', 11),
(1254, 55, 'L', 'Trắng', 14),
(1255, 55, 'L', 'Xanh navy', 7),
(1256, 55, 'M', 'Đen', 18),
(1257, 55, 'M', 'Trắng', 20),
(1258, 55, 'M', 'Xanh navy', 17),
(1259, 55, 'S', 'Đen', 24),
(1260, 55, 'S', 'Trắng', 20),
(1261, 55, 'S', 'Xanh navy', 25),
(1262, 56, 'L', 'Đen', 19),
(1263, 56, 'L', 'Trắng', 8),
(1264, 56, 'L', 'Xanh navy', 16),
(1265, 56, 'M', 'Đen', 13),
(1266, 56, 'M', 'Trắng', 18),
(1267, 56, 'M', 'Xanh navy', 11),
(1268, 56, 'S', 'Đen', 20),
(1269, 56, 'S', 'Trắng', 12),
(1270, 56, 'S', 'Xanh navy', 25),
(1271, 57, 'L', 'Đen', 21),
(1272, 57, 'L', 'Trắng', 5),
(1273, 57, 'L', 'Xanh navy', 10),
(1274, 57, 'M', 'Đen', 19),
(1275, 57, 'M', 'Trắng', 17),
(1276, 57, 'M', 'Xanh navy', 25),
(1277, 57, 'S', 'Đen', 23),
(1278, 57, 'S', 'Trắng', 5),
(1279, 57, 'S', 'Xanh navy', 9),
(1280, 58, 'L', 'Đen', 18),
(1281, 58, 'L', 'Trắng', 15),
(1282, 58, 'L', 'Xanh navy', 12),
(1283, 58, 'M', 'Đen', 13),
(1284, 58, 'M', 'Trắng', 23),
(1285, 58, 'M', 'Xanh navy', 16),
(1286, 58, 'S', 'Đen', 14),
(1287, 58, 'S', 'Trắng', 24),
(1288, 58, 'S', 'Xanh navy', 21),
(1289, 59, 'L', 'Đen', 5),
(1290, 59, 'L', 'Trắng', 12),
(1291, 59, 'L', 'Xanh navy', 19),
(1292, 59, 'M', 'Đen', 24),
(1293, 59, 'M', 'Trắng', 19),
(1294, 59, 'M', 'Xanh navy', 16),
(1295, 59, 'S', 'Đen', 9),
(1296, 59, 'S', 'Trắng', 12),
(1297, 59, 'S', 'Xanh navy', 20),
(1298, 60, 'L', 'Đen', 23),
(1299, 60, 'L', 'Trắng', 17),
(1300, 60, 'L', 'Xanh navy', 6),
(1301, 60, 'M', 'Đen', 8),
(1302, 60, 'M', 'Trắng', 22),
(1303, 60, 'M', 'Xanh navy', 5),
(1304, 60, 'S', 'Đen', 18),
(1305, 60, 'S', 'Trắng', 23),
(1306, 60, 'S', 'Xanh navy', 12);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `key` varchar(80) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`key`, `value`, `updated_at`) VALUES
('BANK_ACCOUNT', '', '2026-03-05 15:56:09'),
('BANK_NAME', '', '2026-03-05 15:56:09'),
('BANK_OWNER', '', '2026-03-05 15:56:09'),
('QR_IMAGE', '', '2026-03-05 15:56:09'),
('SHIP_ENABLED', '1', '2026-03-05 15:56:09'),
('SHIP_FEE', '0', '2026-03-05 15:56:09'),
('SHIP_FREE_FROM', '0', '2026-03-05 15:56:09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `fee` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `role`, `created_at`) VALUES
(2, 'Nguyễn Văn B', '20233885@eaut.edu.vn', '$2y$10$QG25fOcC1vg6InD9g.NjEeLijEBMUUrNRMuiDWIHIPL.ni2phy03K', NULL, 'customer', '2026-03-05 15:58:52');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_variant` (`product_id`,`size`,`color`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1307;

--
-- AUTO_INCREMENT cho bảng `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;


-- ========================================================
-- Bổ sung OTP mail, quên mật khẩu, email thông báo đơn hàng
-- ========================================================

ALTER TABLE `users`
  ADD COLUMN `email_verified_at` DATETIME NULL DEFAULT NULL AFTER `password_hash`;

ALTER TABLE `orders`
  ADD COLUMN `customer_email` VARCHAR(190) DEFAULT NULL AFTER `customer_name`,
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `orders`
  MODIFY `status` enum('pending','confirmed','picking','shipping','completed','delivered','cancelled') DEFAULT 'pending',
  MODIFY `payment_method` enum('cod','bank_transfer','qr','bank','card') DEFAULT NULL,
  MODIFY `payment_status` enum('unpaid','paid','failed') DEFAULT 'unpaid',
  MODIFY `shipping_status` enum('picking','shipping','delivered','returned','cancelled') DEFAULT NULL;

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_password_resets_token` (`token`),
  KEY `idx_password_resets_user_id` (`user_id`),
  KEY `idx_password_resets_expires_at` (`expires_at`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purpose` varchar(50) NOT NULL,
  `email` varchar(190) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `otp_code` varchar(12) NOT NULL,
  `payload_json` text DEFAULT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_otps_lookup` (`purpose`,`email`),
  KEY `idx_email_otps_expires_at` (`expires_at`),
  KEY `idx_email_otps_user_id` (`user_id`),
  CONSTRAINT `fk_email_otps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- ADMIN SHIPPING TIGHT UPGRADE
-- ========================================================
-- Nâng cấp admin đơn hàng + vận chuyển + zone ship
-- ========================================================

ALTER TABLE `orders`
  ADD COLUMN `shipping_fee` INT NOT NULL DEFAULT 0 AFTER `discount`,
  ADD COLUMN `shipping_carrier` VARCHAR(50) DEFAULT 'manual' AFTER `tracking_code`,
  ADD COLUMN `shipping_zone_id` INT NULL AFTER `shipping_carrier`;

ALTER TABLE `orders`
  ADD KEY `idx_orders_shipping_zone_id` (`shipping_zone_id`),
  ADD KEY `idx_orders_tracking_code` (`tracking_code`),
  ADD KEY `idx_orders_shipping_status` (`shipping_status`);

ALTER TABLE `shipping_zones`
  ADD COLUMN `provinces_text` TEXT NULL AFTER `name`,
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `fee`,
  ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0 AFTER `is_active`,
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;

CREATE TABLE IF NOT EXISTS `shipping_settings` (
  `id` INT NOT NULL,
  `default_fee` INT NOT NULL DEFAULT 0,
  `free_ship_min` INT NOT NULL DEFAULT 0,
  `ghn_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `ghn_base_url` VARCHAR(255) NULL,
  `ghn_token` VARCHAR(255) NULL,
  `ghn_shop_id` VARCHAR(100) NULL,
  `ghtk_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `ghtk_base_url` VARCHAR(255) NULL,
  `ghtk_token` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `shipping_settings` (`id`, `default_fee`, `free_ship_min`, `ghn_enabled`, `ghtk_enabled`)
VALUES (1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE `id` = `id`;

CREATE TABLE IF NOT EXISTS `shipments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `shipping_zone_id` INT NULL,
  `carrier` VARCHAR(50) NOT NULL DEFAULT 'manual',
  `tracking_code` VARCHAR(100) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'created',
  `last_tracking_json` LONGTEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_shipments_order_id` (`order_id`),
  KEY `idx_shipments_tracking_code` (`tracking_code`),
  KEY `idx_shipments_status` (`status`),
  KEY `idx_shipments_zone` (`shipping_zone_id`),
  CONSTRAINT `fk_shipments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shipments_zone` FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_status_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `old_status` VARCHAR(50) NULL,
  `new_status` VARCHAR(50) NULL,
  `old_shipping_status` VARCHAR(50) NULL,
  `new_shipping_status` VARCHAR(50) NULL,
  `note` VARCHAR(255) NULL,
  `changed_by_admin_id` INT NULL,
  `changed_by_type` VARCHAR(30) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_status_logs_order` (`order_id`),
  KEY `idx_order_status_logs_admin` (`changed_by_admin_id`),
  CONSTRAINT `fk_order_status_logs_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_status_logs_admin` FOREIGN KEY (`changed_by_admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_shipping_zone` FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE SET NULL;


-- ========================================================
-- Chuẩn hóa enum đơn hàng + seed zone ship mẫu
-- ========================================================
ALTER TABLE `orders`
  MODIFY `status` ENUM('pending','confirmed','shipping','completed','cancelled') DEFAULT 'pending',
  MODIFY `payment_method` ENUM('cod','bank_transfer','qr','bank','card') DEFAULT NULL,
  MODIFY `payment_status` ENUM('unpaid','paid','failed') DEFAULT 'unpaid',
  MODIFY `shipping_status` ENUM('picking','shipping','delivered','returned','cancelled') DEFAULT NULL;

INSERT INTO `shipping_zones` (`id`,`name`,`provinces_text`,`fee`,`is_active`,`sort_order`,`created_at`,`updated_at`) VALUES
(1,'Nội thành Hà Nội','Hà Nội
Ha Noi',20000,1,1,NOW(),NOW()),
(2,'Miền Bắc khác','Bắc Ninh
Hưng Yên
Hải Dương
Hải Phòng
Quảng Ninh
Nam Định
Thái Bình
Ninh Bình
Hà Nam
Vĩnh Phúc
Phú Thọ
Bắc Giang
Bắc Kạn
Cao Bằng
Lạng Sơn
Thái Nguyên
Tuyên Quang
Yên Bái
Lào Cai
Sơn La
Điện Biên
Lai Châu
Hòa Bình',30000,1,2,NOW(),NOW()),
(3,'Miền Trung','Thanh Hóa
Nghệ An
Hà Tĩnh
Quảng Bình
Quảng Trị
Thừa Thiên Huế
Đà Nẵng
Quảng Nam
Quảng Ngãi
Bình Định
Phú Yên
Khánh Hòa
Ninh Thuận
Bình Thuận',35000,1,3,NOW(),NOW()),
(4,'Miền Nam','Hồ Chí Minh
TP HCM
TP. HCM
Ho Chi Minh
Bình Dương
Đồng Nai
Bà Rịa - Vũng Tàu
Long An
Tiền Giang
Bến Tre
Trà Vinh
Vĩnh Long
Cần Thơ
Hậu Giang
Sóc Trăng
Bạc Liêu
Cà Mau
An Giang
Kiên Giang
Đồng Tháp
Tây Ninh
Bình Phước',40000,1,4,NOW(),NOW())
ON DUPLICATE KEY UPDATE
`name`=VALUES(`name`),`provinces_text`=VALUES(`provinces_text`),`fee`=VALUES(`fee`),`is_active`=VALUES(`is_active`),`sort_order`=VALUES(`sort_order`),`updated_at`=VALUES(`updated_at`);

UPDATE `shipping_settings`
SET `default_fee` = 30000,
    `free_ship_min` = 500000,
    `updated_at` = NOW()
WHERE `id` = 1;
