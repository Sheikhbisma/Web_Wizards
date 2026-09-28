-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2026 at 12:44 PM
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
-- Database: `techwiz7`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `entity` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `is_active`) VALUES
(1, 'Vegetables', 'Fresh vegetables', 1),
(2, 'Fruits', 'Fresh fruits', 1),
(3, 'Dairy', 'Milk and dairy', 1),
(4, 'Bakery', 'Bread and baked goods', 1),
(5, 'Herbs', 'Fresh herbs and spices', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `preferred_market_id` int(11) DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `full_name`, `preferred_market_id`, `loyalty_points`, `address`, `city`, `profile_image`) VALUES
(2, 33, 'Daniyal Ahmed', 7, 124, 'House 4, Street 9, DHA Phase 5, Karachi', 'Karachi', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `farmer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stall_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `approval_status` enum('pending','approved','suspended') DEFAULT 'pending',
  `avg_rating` decimal(3,2) DEFAULT 0.00,
  `total_orders` int(11) DEFAULT 0,
  `contact_person` varchar(100) DEFAULT NULL,
  `pickup_start` time DEFAULT NULL,
  `pickup_end` time DEFAULT NULL,
  `cutoff_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`farmer_id`, `user_id`, `stall_name`, `description`, `profile_image`, `address`, `latitude`, `longitude`, `approval_status`, `avg_rating`, `total_orders`, `contact_person`, `pickup_start`, `pickup_end`, `cutoff_time`) VALUES
(8, 27, 'Ayesha Farms', 'Ayesha Farms produce organic vegetables grown in Tando Adam, harvested before sunrise and at your gate by noon.', 'farmers-profile1.jpg', 'Plot 8, Farm Colony Tando Adam', 25.76310000, 68.66130000, 'approved', 5.00, 1, 'Ali Raza', NULL, NULL, NULL),
(9, 28, 'Nida Orchard', 'Family-run mango and citrus orchard. Seasonal fruits only, picked ripe and packed the same morning.', 'farmers-profile2.jpg', 'Orchard Road, Mirpurkhas', 25.52600000, 69.01150000, 'approved', 0.00, 1, 'Nida Bano', NULL, NULL, NULL),
(10, 29, 'GreenGate Dairy', 'Pure desi dairy — raw milk, fresh butter and clotted cream collected every single morning.', 'farmers-profile3.jpg', 'Green Gate Lane, Latifabad, Hyderabad', 25.39200000, 68.37200000, 'approved', 0.00, 1, 'Usman Khan', NULL, NULL, NULL),
(11, 30, 'Sunrise Bakery', 'Wood-fired whole-wheat bread, sourdough and flatbread baked daily at dawn.', 'farmers-profile4.jpg', 'Shop 21, Shahrah-e-Quaideen, Saddar', 24.86110000, 67.00990000, 'approved', 4.00, 1, 'Farhan Sheikh', NULL, NULL, NULL),
(12, 31, 'Herb Haven', 'Hydroponic herbs and microgreens grown in Karachi — basil, mint, coriander and greens year round.', 'farmers-profile5.jpg', 'Zone C, Scheme 33, Karachi', 24.98330000, 67.08200000, 'approved', 0.00, 1, 'Sana Abbasi', NULL, NULL, NULL),
(13, 32, 'Farm2Basket', 'A collective of small holder farmers delivering mixed seasonal baskets of fruit and vegetables.', 'farmers-profile6.jpg', 'Main PAF Road, Malir, Karachi', 24.91010000, 67.20300000, 'approved', 0.00, 1, 'Imran Shah', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `market_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `customer_id`, `farmer_id`, `product_id`, `market_id`, `created_at`) VALUES
(6, 2, 8, NULL, NULL, '2026-09-28 15:41:54'),
(7, 2, 10, NULL, NULL, '2026-09-28 15:41:54'),
(8, 2, NULL, 26, NULL, '2026-09-28 15:41:54'),
(9, 2, NULL, 22, NULL, '2026-09-28 15:41:54'),
(10, 2, NULL, 38, NULL, '2026-09-28 15:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `markets`
--

CREATE TABLE `markets` (
  `market_id` int(11) NOT NULL,
  `market_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `operating_days` varchar(100) DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `map_provider` varchar(30) DEFAULT 'Google Maps',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `markets`
--

INSERT INTO `markets` (`market_id`, `market_name`, `address`, `latitude`, `longitude`, `operating_days`, `opening_time`, `closing_time`, `map_provider`, `is_active`) VALUES
(7, 'Clifton Fresh Market', 'Plot 12, MCB Road, Clifton, Karachi', 24.81000000, 67.03000000, 'Tuesday,Thursday,Saturday', '07:00:00', '14:00:00', 'OpenStreetMap', 1),
(8, 'Gulshan Community Market', 'University Road, Gulshan-e-Iqbal, Karachi', 24.93300000, 67.07500000, 'Monday,Wednesday,Friday', '06:30:00', '13:30:00', 'OpenStreetMap', 1),
(9, 'Saddar Juma Bazaar', 'Zaibunnisa Street, Saddar, Karachi', 24.85400000, 67.03500000, 'Friday', '05:30:00', '12:00:00', 'OpenStreetMap', 1),
(10, 'North Nazimabad Green Market', 'Block H, North Nazimabad, Karachi', 24.95600000, 67.03300000, 'Tuesday,Friday,Sunday', '07:00:00', '15:00:00', 'OpenStreetMap', 1),
(11, 'Malir City Farm Market', 'Main Malir Halt Road, Karachi', 24.89700000, 67.19200000, 'Wednesday,Saturday', '06:00:00', '13:00:00', 'OpenStreetMap', 1);

-- --------------------------------------------------------

--
-- Table structure for table `market_farmer`
--

CREATE TABLE `market_farmer` (
  `mf_id` int(11) NOT NULL,
  `market_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `stall_number` varchar(20) DEFAULT NULL,
  `pickup_start` time DEFAULT NULL,
  `pickup_end` time DEFAULT NULL,
  `day_of_week` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `market_farmer`
--

INSERT INTO `market_farmer` (`mf_id`, `market_id`, `farmer_id`, `stall_number`, `pickup_start`, `pickup_end`, `day_of_week`) VALUES
(21, 7, 8, 'A-01', '07:00:00', '13:30:00', 'Tuesday'),
(22, 7, 9, 'A-02', '07:00:00', '13:00:00', 'Tuesday'),
(23, 7, 12, 'C-05', '07:00:00', '13:30:00', 'Thursday'),
(24, 7, 13, 'B-12', '07:00:00', '13:00:00', 'Saturday'),
(25, 8, 9, 'D-03', '06:30:00', '12:30:00', 'Wednesday'),
(26, 8, 10, 'D-04', '06:30:00', '12:30:00', 'Wednesday'),
(27, 8, 12, 'E-09', '06:30:00', '12:30:00', 'Monday'),
(28, 9, 11, 'F-01', '05:30:00', '11:00:00', 'Friday'),
(29, 9, 8, 'F-02', '05:30:00', '11:00:00', 'Friday'),
(30, 10, 10, 'G-07', '07:00:00', '14:00:00', 'Sunday'),
(31, 10, 11, 'G-08', '07:00:00', '14:00:00', 'Sunday'),
(32, 10, 13, 'H-02', '07:00:00', '13:30:00', 'Tuesday'),
(33, 11, 8, 'J-01', '06:00:00', '12:00:00', 'Wednesday'),
(34, 11, 9, 'J-02', '06:00:00', '12:00:00', 'Wednesday'),
(35, 11, 12, 'K-05', '06:00:00', '12:00:00', 'Saturday');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('order','restock','announcement','review') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(6, 33, 'Order Ready', 'Your milk order #9 is ready for pickup today at Gulshan Community Market.', 'order', 0, '2026-09-28 10:41:54'),
(7, 33, 'Order Accepted', 'Farmer Nida Orchard accepted your order #10.', 'order', 0, '2026-09-28 10:41:54'),
(8, 33, 'Order Placed', 'Pre-order #11 is confirmed. Payment will be settled at pickup.', 'order', 0, '2026-09-28 06:41:54'),
(9, 33, 'Order Cancelled', 'Pre-order #12 has been cancelled. See you soon!', 'order', 0, '2026-09-28 08:41:54'),
(10, 33, 'Welcome to MarketLink', 'Pre-order fresh produce from farmers and pick up at the market.', 'announcement', 0, '2026-09-28 06:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `market_id` int(11) DEFAULT NULL,
  `pickup_date` date NOT NULL,
  `pickup_slot` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('placed','accepted','ready','completed','cancelled','declined') DEFAULT 'placed',
  `cutoff_time` datetime DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `farmer_id`, `market_id`, `pickup_date`, `pickup_slot`, `total_amount`, `order_status`, `cutoff_time`, `special_instructions`, `order_date`, `updated_at`) VALUES
(7, 2, 8, 7, '2026-10-04', '07:00 - 08:00', 390.00, 'completed', '2026-10-03 08:00:00', 'Firm green tomatoes please', '2026-09-21 12:41:54', '2026-09-28 15:41:54'),
(8, 2, 11, 9, '2026-10-01', '05:30 - 06:30', 420.00, 'completed', '2026-09-30 06:30:00', 'Please pack bread separately', '2026-09-24 12:41:54', '2026-09-28 15:41:54'),
(9, 2, 10, 8, '2026-09-28', '06:30 - 07:30', 860.00, 'ready', '2026-09-27 07:30:00', 'Need milk before 7am please', '2026-09-28 12:41:54', '2026-09-28 15:41:54'),
(10, 2, 9, 7, '2026-10-01', '07:00 - 08:30', 1180.00, 'accepted', '2026-09-30 08:30:00', 'Choose ripe mangoes', '2026-09-27 12:41:54', '2026-09-28 15:41:54'),
(11, 2, 13, 10, '2026-10-03', '07:00 - 08:00', 830.00, 'placed', '2026-10-02 08:00:00', 'Amla basket this time', '2026-09-28 12:41:54', '2026-09-28 15:41:54'),
(12, 2, 12, 7, '2026-09-30', '07:00 - 08:00', 160.00, 'cancelled', '2026-09-29 08:00:00', '', '2026-09-25 12:41:54', '2026-09-28 15:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(12, 7, 22, 2, 120.00, 240.00),
(13, 7, 24, 3, 50.00, 150.00),
(14, 8, 34, 2, 150.00, 300.00),
(15, 8, 36, 1, 120.00, 120.00),
(16, 9, 30, 3, 180.00, 540.00),
(17, 9, 32, 2, 160.00, 320.00),
(18, 10, 26, 2, 350.00, 700.00),
(19, 10, 27, 3, 160.00, 480.00),
(20, 11, 40, 1, 550.00, 550.00),
(21, 11, 41, 2, 140.00, 280.00),
(22, 12, 38, 4, 40.00, 160.00);

-- --------------------------------------------------------

--
-- Table structure for table `pickup_slots`
--

CREATE TABLE `pickup_slots` (
  `slot_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `day_of_week` varchar(10) DEFAULT NULL,
  `slot_start` time DEFAULT NULL,
  `slot_end` time DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_sold_out` tinyint(1) DEFAULT 0,
  `avg_rating` decimal(3,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `farmer_id`, `category_id`, `name`, `description`, `price`, `unit`, `stock_quantity`, `image_url`, `is_available`, `is_sold_out`, `avg_rating`, `created_at`, `updated_at`) VALUES
(22, 8, 1, 'Tomatoes', 'Sun ripened desi tomatoes, tangy and juicy.', 120.00, '1kg', 80, '../public/Uploads/img/Tomatoes.png', 1, 0, 5.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(23, 8, 1, 'Potatoes', 'Firm sandy-grown potatoes, perfect for sabzi and fries.', 90.00, '1kg', 60, '../public/Uploads/img/Potatoes.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(24, 8, 1, 'Onions', 'Pungent red onions, stored crisp.', 150.00, '1kg', 50, '../public/Uploads/img/Onions.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(25, 8, 1, 'Cucumbers', 'Cool greenhouse cucumbers, crunchy and hydrating.', 45.00, '1kg', 90, '../public/Uploads/img/Cucumbers.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(26, 9, 2, 'Mangoes Sindhri', 'Sweet aromatic Sindhri mangoes, orchard fresh.', 40.00, '1kg', 350, '../public/Uploads/img/Mangoes Sindhri.png', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(27, 9, 2, 'Oranges', 'Juicy kinnow oranges, seeded and sweet.', 75.00, '1kg', 160, '../public/Uploads/img/Oranges.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(28, 9, 2, 'Bananas', 'Ripe but firm bananas, bunch fresh.', 120.00, '1kg', 100, '../public/Uploads/img/Bananas.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(29, 9, 2, 'Strawberries', 'Plump red strawberries, farm selected.', 30.00, '250g', 320, '../public/Uploads/img/Strawberries.png', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(30, 10, 3, 'Fresh Milk', 'Raw desi milk, boiled and bottled every morning.', 60.00, '1L', 180, '../public/Uploads/img/Fresh Milk.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(31, 10, 3, 'Desi Butter', 'Hand churned desi butter from pure cream.', 25.00, '500g', 700, '../public/Uploads/img/Desi Butter.png', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(32, 10, 3, 'Desi Yogurt', 'Thick, tangy desi dahi made from whole milk.', 40.00, '500g', 160, '../public/Uploads/img/Desi Yogurt.webp', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(33, 10, 3, 'Cream Malai', 'Clotted cream collected from fresh milk.', 20.00, '250g', 240, '../public/Uploads/img/Cream Malai.png', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(34, 11, 4, 'Whole Wheat Bread', 'Wood fired whole wheat loaf, baked at dawn.', 35.00, '1 loaf', 150, '../public/Uploads/img/Whole Wheat Bread.png', 1, 0, 4.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(35, 11, 4, 'Sourdough Bread', 'Naturally leavened sourdough, crusty and light.', 25.00, '1 loaf', 260, '../public/Uploads/img/Sourdough Bread.png', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(36, 11, 4, 'Roti (12 pc)', 'Hand rolled chapati, soft and ready to warm.', 30.00, 'pack', 120, '../public/Uploads/img/Roti (12 pc).jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(37, 12, 5, 'Fresh Mint', 'Fragrant mint sprigs grown hydroponically.', 30.00, '100g', 60, '../public/Uploads/img/Fresh Mint.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(38, 12, 5, 'Coriander', 'Tender coriander with deep green leaves.', 30.00, '100g', 40, '../public/Uploads/img/Coriander.png', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(39, 12, 5, 'Basil', 'Sweet basil ideal for sauces and salads.', 20.00, '50g', 110, '../public/Uploads/img/Basil.jpg', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(40, 13, 1, 'Mixed Veg Basket', 'Seasonal box of 8+ vegetables, market selection.', 35.00, 'basket', 550, '../public/Uploads/img/Mixed Veg Basket.png', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54'),
(41, 13, 1, 'Leafy Greens Pack', 'Spinach, mustard greens and lettuce mix.', 40.00, '500g', 140, '../public/Uploads/img/Leafy Greens Pack.webp', 1, 0, 0.00, '2026-09-28 15:41:54', '2026-09-28 15:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `parameters` text DEFAULT NULL,
  `generated_at` datetime DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `farmer_response` text DEFAULT NULL,
  `review_date` datetime DEFAULT current_timestamp(),
  `is_flagged` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `order_id`, `product_id`, `farmer_id`, `customer_id`, `rating`, `comment`, `farmer_response`, `review_date`, `is_flagged`) VALUES
(3, 7, 22, 8, 2, 5, 'Best tomatoes I have found in Karachi. Super fresh.', 'Shukriya Daniyal! See you next week.', '2026-09-23 12:41:54', 0),
(4, 8, 34, 11, 2, 4, 'Bread is excellent, still warm at pickup.', NULL, '2026-09-26 12:41:54', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(128) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `is_verified` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('customer','farmer','admin') NOT NULL,
  `status` enum('active','deactivated') DEFAULT 'active',
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `contact`, `password`, `otp_code`, `otp_expiry`, `reset_token`, `reset_token_expiry`, `is_verified`, `created_at`, `role`, `status`, `remember_token`) VALUES
(16, 'Bisma Sheikh', 'bismasheikh2006@gmail.com', '03212038938', '$2y$10$OIvaRsa69Ps3JF.1/TZPi.iLVMy9EsqX1CPlfnBISok4r3LlpJI1W', '830912', NULL, NULL, NULL, 1, '2026-09-23 03:54:29', 'customer', 'deactivated', NULL),
(17, 'admin', 'admin@gmail.com', NULL, '$2y$10$x41.dYoDFQiOzk3.9TpA.OXa1D1Yf2Vw1juaPYVumPi.sGag9GXfq', '123456', NULL, NULL, NULL, 1, '2026-09-23 13:29:08', 'admin', 'active', NULL),
(26, 'Bilal', 'bilal@gmail.com', NULL, '$2y$10$V.Vf6OyxHtqIPbRLzlzGUe0ayjOYjE60AE.YjqeVWujt4RJt8Jh.G', '987654', NULL, NULL, NULL, 1, '2026-09-28 10:41:53', 'farmer', 'active', NULL),
(27, 'AliRaza', 'ali.raza@gmail.com', '03331234567', '$2y$10$X2ovODTI8mJej90g7kpG4uPtJjraJlWD94xCxW40N5b1qlvm.xwFi', '111111', NULL, NULL, NULL, 1, '2026-09-28 10:41:53', 'farmer', 'active', NULL),
(28, 'Nida', 'nida.orchard@gmail.com', '03451234567', '$2y$10$ru0HLCqGCDxgGOZWsJnzquH13bcrSwWxMf4aNhQkAR.0GVZXluQwq', '111111', NULL, NULL, NULL, 1, '2026-09-28 10:41:53', 'farmer', 'active', NULL),
(29, 'GreenGate', 'greengate.dairy@gmail.com', '03211234567', '$2y$10$AE4fJiK5y3bK2jJ/zmz1MeM8CiMIDbZoyjjyE8k1UC8MnMMgGp03q', '111111', NULL, NULL, NULL, 1, '2026-09-28 10:41:53', 'farmer', 'active', NULL),
(30, 'SunriseBakery', 'sunrise.bake@gmail.com', '03001234567', '$2y$10$u2Q9aTBQ2G4fD3U/l43Fn.wltkuY9DViHg/bULOhGLedKsLGabwxq', '111111', NULL, NULL, NULL, 1, '2026-09-28 10:41:54', 'farmer', 'active', NULL),
(31, 'HerbHaven', 'herbhaven.pk@gmail.com', '03151234567', '$2y$10$GKkHl1rsv2yJp0L1DwopDuRxRUeWr8RK8OGeqy6DjmmJM5oLpgyNa', '111111', NULL, NULL, NULL, 1, '2026-09-28 10:41:54', 'farmer', 'active', NULL),
(32, 'Farm2Basket', 'farm2basket@gmail.com', '03161234567', '$2y$10$gqvtiGYMZsWM1Na/O0ggGO4OEeagxCYUQmaQRyuhe2IDOoRzzO4fu', '111111', NULL, NULL, NULL, 1, '2026-09-28 10:41:54', 'farmer', 'active', NULL),
(33, 'Daniyal', 'daniyal@gmail.com', '03331231234', '$2y$10$xnJShOFUtsgEx8gLPC8kreqMHb4u7HYBGvj5CMhGYm9hwso4oLbUe', '555555', NULL, NULL, NULL, 1, '2026-09-28 10:41:54', 'customer', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `weekly_stock_template`
--

CREATE TABLE `weekly_stock_template` (
  `template_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `default_quantity` int(11) DEFAULT NULL,
  `day_of_week` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `fk_ann_admin` (`admin_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_audit_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `fk_customer_user` (`user_id`),
  ADD KEY `fk_customer_market` (`preferred_market_id`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`farmer_id`),
  ADD KEY `fk_farmer_user` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `fk_fav_customer` (`customer_id`),
  ADD KEY `fk_fav_farmer` (`farmer_id`),
  ADD KEY `fk_fav_product` (`product_id`);

--
-- Indexes for table `markets`
--
ALTER TABLE `markets`
  ADD PRIMARY KEY (`market_id`);

--
-- Indexes for table `market_farmer`
--
ALTER TABLE `market_farmer`
  ADD PRIMARY KEY (`mf_id`),
  ADD UNIQUE KEY `unique_market_farmer_day` (`market_id`,`farmer_id`,`day_of_week`),
  ADD KEY `fk_mf_farmer` (`farmer_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notif_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_customer` (`customer_id`),
  ADD KEY `fk_order_farmer` (`farmer_id`),
  ADD KEY `fk_order_market` (`market_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_item_order` (`order_id`),
  ADD KEY `fk_item_product` (`product_id`);

--
-- Indexes for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  ADD PRIMARY KEY (`slot_id`),
  ADD KEY `fk_slot_farmer` (`farmer_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_product_farmer` (`farmer_id`),
  ADD KEY `fk_product_category` (`category_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_report_user` (`generated_by`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_review_order` (`order_id`),
  ADD KEY `fk_review_product` (`product_id`),
  ADD KEY `fk_review_farmer` (`farmer_id`),
  ADD KEY `fk_review_customer` (`customer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `weekly_stock_template`
--
ALTER TABLE `weekly_stock_template`
  ADD PRIMARY KEY (`template_id`),
  ADD KEY `fk_wst_farmer` (`farmer_id`),
  ADD KEY `fk_wst_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `farmer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `markets`
--
ALTER TABLE `markets`
  MODIFY `market_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `market_farmer`
--
ALTER TABLE `market_farmer`
  MODIFY `mf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `weekly_stock_template`
--
ALTER TABLE `weekly_stock_template`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_ann_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customer_market` FOREIGN KEY (`preferred_market_id`) REFERENCES `markets` (`market_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `fk_farmer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_fav_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `market_farmer`
--
ALTER TABLE `market_farmer`
  ADD CONSTRAINT `fk_mf_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mf_market` FOREIGN KEY (`market_id`) REFERENCES `markets` (`market_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_market` FOREIGN KEY (`market_id`) REFERENCES `markets` (`market_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `pickup_slots`
--
ALTER TABLE `pickup_slots`
  ADD CONSTRAINT `fk_slot_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_report_user` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `weekly_stock_template`
--
ALTER TABLE `weekly_stock_template`
  ADD CONSTRAINT `fk_wst_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wst_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
