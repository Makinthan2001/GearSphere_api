-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2025 at 04:06 AM
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
-- Database: `gearsphere`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cpu`
--

CREATE TABLE `cpu` (
  `product_id` int(11) NOT NULL,
  `series` varchar(100) DEFAULT NULL,
  `socket` varchar(50) DEFAULT NULL,
  `core_count` int(11) DEFAULT NULL,
  `thread_count` int(11) DEFAULT NULL,
  `core_clock` decimal(4,2) DEFAULT NULL,
  `core_boost_clock` decimal(4,2) DEFAULT NULL,
  `tdp` varchar(20) DEFAULT NULL,
  `integrated_graphics` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cpu`
--

INSERT INTO `cpu` (`product_id`, `series`, `socket`, `core_count`, `thread_count`, `core_clock`, `core_boost_clock`, `tdp`, `integrated_graphics`) VALUES
(5, 'AMD Ryzen 7', 'AM5', 8, 16, 4.20, 5.00, '120W', 0),
(6, 'AMD Ryzen 7', 'AM5', 8, 16, 4.20, 5.00, '120W', 1),
(50, 'AMD Ryzen 5', 'AM4', 6, 12, 3.70, 4.60, '65W', 0),
(52, 'AMD Ryzen 7', 'AM5', 8, 16, 3.80, 5.50, '65W', 1),
(53, 'Intel Core i5-12400F', 'LGA1700', 6, 12, 2.50, 4.40, '65W', 0),
(102, 'AMD Ryzen 5', 'AM4', 6, 10, 3.20, 3.40, '65W', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cpu_cooler`
--

CREATE TABLE `cpu_cooler` (
  `product_id` int(11) NOT NULL,
  `fan_rpm` varchar(50) DEFAULT NULL,
  `noise_level` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `height` varchar(50) DEFAULT NULL,
  `water_cooled` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cpu_cooler`
--

INSERT INTO `cpu_cooler` (`product_id`, `fan_rpm`, `noise_level`, `color`, `height`, `water_cooled`) VALUES
(7, '1500 RPM', '25.6 dB', 'Black', '158 mm', 0),
(8, '1200 RPM', '24.6 dB', 'Black', '165 mm', 0),
(35, '600-2000 RPM', '26 dB', 'Black', '158.8 mm', 0),
(36, '900-1500 RPM', '25.6 dB', 'Black', '155 mm', 0),
(37, '200-2000 RPM', '22.5 dB', 'Black', '38 mm (radiator)', 1),
(95, '1550 RPM', '25.6db', 'Black/Silver', '148 mm', 0),
(100, '1550 RPM', '25.6db', 'White', '148 mm', 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `inventory_summary`
-- (See below for the actual view)
--
CREATE TABLE `inventory_summary` (
`total_products` bigint(21)
,`total_stock` decimal(32,0)
,`total_value` decimal(42,2)
,`out_of_stock` bigint(21)
,`low_stock` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `low_stock_products`
-- (See below for the actual view)
--
CREATE TABLE `low_stock_products` (
`product_id` int(11)
,`name` varchar(255)
,`category` varchar(100)
,`stock` int(11)
,`min_stock` int(1)
,`status` enum('In Stock','Low Stock','Out of Stock','Discontinued')
,`last_restock_date` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `memory`
--

CREATE TABLE `memory` (
  `product_id` int(11) NOT NULL,
  `memory_type` varchar(50) DEFAULT NULL,
  `speed` varchar(50) DEFAULT NULL,
  `modules` varchar(50) DEFAULT NULL,
  `cas_latency` varchar(20) DEFAULT NULL,
  `voltage` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memory`
--

INSERT INTO `memory` (`product_id`, `memory_type`, `speed`, `modules`, `cas_latency`, `voltage`) VALUES
(38, 'DDR4', '3200 MHz', '2 x 8GB', '16', '1.35V'),
(39, 'DDR5', '6000 MHz', '2 x 16GB', '36', '1.35V'),
(40, 'DDR5', '6000 MHz', '2 x 16GB', '36', '1.35V'),
(41, 'DDR5', '6000 MHz', '2 x 16GB', '30', '1.35V'),
(48, 'DDR4', '3200 MHz', '2 x 16GB', '16', '1.35V'),
(49, 'DDR4', '3200 MHz', '2 x 8GB', '16', '1.35V');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `name`, `email`, `message`, `date`) VALUES
(1, 'makinthan sathananthan', 'mahinthan2001a@gmail.com', 'Subject: Technical Support\nMessage: technician not responded', '2025-07-14 07:56:53'),
(3, 'makinthan sathananthan', 'madhan2001ana@gmail.com', 'Subject: Account Issues\nMessage: i could not change my profile picture', '2025-07-14 07:58:55');

-- --------------------------------------------------------

--
-- Table structure for table `monitor`
--

CREATE TABLE `monitor` (
  `product_id` int(11) NOT NULL,
  `screen_size` decimal(4,1) DEFAULT NULL,
  `resolution` varchar(50) DEFAULT NULL,
  `refresh_rate` varchar(50) DEFAULT NULL,
  `panel_type` varchar(50) DEFAULT NULL,
  `aspect_ratio` varchar(20) DEFAULT NULL,
  `brightness` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monitor`
--

INSERT INTO `monitor` (`product_id`, `screen_size`, `resolution`, `refresh_rate`, `panel_type`, `aspect_ratio`, `brightness`) VALUES
(26, 27.0, '2560 x 1440', '165 Hz', 'IPS', '16:9', '350 cd/m²'),
(27, 65.0, '3840 x 2160', '120 Hz', 'OLED', '16:9', '150 cd/m²'),
(28, 24.0, '1920 x 1080', '165 Hz', 'IPS', '16:9', '250 cd/m²'),
(29, 27.0, '2560 x 1440', '180 Hz', 'IPS', '16:9', '300 cd/m²'),
(30, 24.5, '1920 x 1080', '180 Hz', 'IPS', '16:9', '300 cd/m²'),
(31, 23.8, '1920 x 1080', '100 Hz', 'IPS', '16:9', '250 cd/m²'),
(103, 22.0, '1920 x 1080', '60 Hz', '', '16:9', '250 cd/m²'),
(104, 21.4, '1920 x 1080', '100', 'VA', '16:9', '250 cd/m²');

-- --------------------------------------------------------

--
-- Table structure for table `motherboard`
--

CREATE TABLE `motherboard` (
  `product_id` int(11) NOT NULL,
  `socket` varchar(50) DEFAULT NULL,
  `form_factor` varchar(50) DEFAULT NULL,
  `chipset` varchar(50) DEFAULT NULL,
  `memory_max` varchar(50) DEFAULT NULL,
  `memory_slots` int(11) DEFAULT NULL,
  `memory_type` varchar(50) DEFAULT NULL,
  `sata_ports` int(11) DEFAULT NULL,
  `wifi` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motherboard`
--

INSERT INTO `motherboard` (`product_id`, `socket`, `form_factor`, `chipset`, `memory_max`, `memory_slots`, `memory_type`, `sata_ports`, `wifi`) VALUES
(14, 'AM5', 'ATX', 'B650', '128GB', 4, 'DDR5', 6, 1),
(15, 'AM5', 'ATX', 'B650', '128GB', 4, 'DDR5', 6, 1),
(16, 'AM4', 'Micro ATX', 'B550', '128GB', 2, 'DDR4', 4, 0),
(17, 'LGA1700', 'ATX', 'B760', '128GB', 4, 'DDR5', 6, 1),
(18, 'AM5', 'Micro ATX', 'B650', '128GB', 4, 'DDR5', 4, 1),
(19, 'AM5', 'ATX', 'X870', '192GB', 4, 'DDR5', 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `date`) VALUES
(2, 30, 'You have been assigned to a new customer. Name: makinthan mdn, Email: mahinthan2001a@gmail.com. Please check your dashboard for details.', '2025-07-16 07:53:33'),
(3, 29, 'Your request was accepted by technician: madhan  mdn.', '2025-07-16 07:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `operating_system`
--

CREATE TABLE `operating_system` (
  `product_id` int(11) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `mode` enum('32-bit','64-bit','Both') NOT NULL,
  `version` varchar(100) DEFAULT NULL,
  `max_supported_memory` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operating_system`
--

INSERT INTO `operating_system` (`product_id`, `model`, `mode`, `version`, `max_supported_memory`) VALUES
(62, 'Windows 11 Pro', '64-bit', 'Pro', '2TB'),
(63, 'Windows 11 Home', '64-bit', 'Home', '2TB'),
(64, 'Windows 11 Home', '64-bit', 'Home', '2TB'),
(65, 'Windows 11 Pro', '64-bit', 'Pro', '2TB'),
(66, 'Windows 10 Pro', '64-bit', 'Pro', '2TB'),
(67, 'Windows 10 Pro', 'Both', 'Pro', '2TB'),
(96, 'Microsoft Windows 11 Home 64-bit - OEM (DVD)', '64-bit', 'Windows 11 Home', '128 GB'),
(97, 'Windows 11 Home 64-bit (USB)', '64-bit', 'Windows 11 Home', '128 GB'),
(98, 'Microsoft Windows 10 Home OEM (DVD)', '32-bit', 'Windows 10 Home', '4 GB'),
(99, 'Windows', '64-bit', 'Windows 8.1 Pro', '512 GB');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Card',
  `payment_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('success','failed','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pc_case`
--

CREATE TABLE `pc_case` (
  `product_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `side_panel` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `max_gpu_length` varchar(50) DEFAULT NULL,
  `volume` varchar(50) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pc_case`
--

INSERT INTO `pc_case` (`product_id`, `type`, `side_panel`, `color`, `max_gpu_length`, `volume`, `dimensions`) VALUES
(32, 'Mid Tower', 'Tempered Glass', 'Black', '400 mm', '50 L', '480 x 230 x 450 mm'),
(33, 'Mid Tower', 'Tempered Glass', 'Black/White', '380 mm', '45 L', '460 x 220 x 440 mm'),
(34, 'Mid Tower', 'Tempered Glass', 'Black', '375 mm', '42 L', '450 x 210 x 435 mm'),
(68, 'Mid Tower', 'Tempered Glass', 'Black', '360 mm', '41 L', '453 x 230 x 466 mm'),
(69, 'Mid Tower', 'Tempered Glass', 'White', '400 mm', '52 L', '480 x 235 x 460 mm'),
(70, 'Mid Tower', 'Tempered Glass', 'Black', '380 mm', '43 L', '445 x 220 x 440 mm'),
(94, 'ATX Mid Tower', 'Tempered Glass', 'Black', '330 mm', '37.582 L', '430 mm x 200 mm x 437 mm');

-- --------------------------------------------------------

--
-- Table structure for table `power_supply`
--

CREATE TABLE `power_supply` (
  `product_id` int(11) NOT NULL,
  `wattage` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `efficiency_rating` varchar(50) DEFAULT NULL,
  `length` varchar(50) DEFAULT NULL,
  `modular` enum('Full','Semi','Non-Modular') NOT NULL,
  `sata_connectors` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `power_supply`
--

INSERT INTO `power_supply` (`product_id`, `wattage`, `type`, `efficiency_rating`, `length`, `modular`, `sata_connectors`) VALUES
(20, 650, 'ATX', '80+ Bronze', '140 mm', 'Non-Modular', 5),
(21, 1000, 'ATX', '80+ Gold', '160 mm', 'Full', 12),
(22, 850, 'ATX', '80+ Gold', '160 mm', 'Full', 8),
(23, 750, 'ATX', '80+ Gold', '160 mm', 'Full', 6),
(24, 850, 'ATX', '80+ Gold', '160 mm', 'Full', 8),
(25, 650, 'ATX', '80+ Bronze', '140 mm', 'Non-Modular', 4);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `rating_count` int(11) DEFAULT 0,
  `rating_avg` decimal(3,2) DEFAULT 0.00,
  `manufacturer` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `status` enum('In Stock','Low Stock','Out of Stock','Discontinued') DEFAULT 'Out of Stock',
  `last_restock_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `category`, `price`, `image_url`, `description`, `rating_count`, `rating_avg`, `manufacturer`, `stock`, `status`, `last_restock_date`) VALUES
(5, 'AMD Ryzen 7 7800X3D 4.2 GHz 8-Core Processor', 'CPU', 95000.00, 'uploads/1751900410_AMD Ryzen 7 7800X3D 4.2 GHz 8-Core Processor.jpg', 'AMD Ryzen 7 9800X3D is a high-performance 8-core processor with a base clock of 4.7 GHz, designed for gaming and multitasking, featuring AMD\'s 3D V-Cache technology for enhanced speed and efficiency.', 0, 0.00, 'AMD', 10, 'In Stock', '2025-07-10 17:29:43'),
(6, 'AMD Ryzen 7 9800X3D 4.7 GHz 8-Core Processor', 'CPU', 130000.00, 'uploads/1751900624_AMD Ryzen 7 9800X3D 4.7 GHz 8-Core Processor.jpg', 'AMD Ryzen 7 7800X3D is a powerful 8-core processor with a 4.2 GHz base clock, optimized for gaming and productivity, and equipped with 3D V-Cache for improved performance in demanding tasks.', 0, 0.00, 'AMD', 12, 'In Stock', '2025-07-11 12:39:59'),
(7, 'Thermalright Phantom Spirit 120 SE ARGB 66.17 CFM', 'CPU Cooler', 11400.00, 'uploads/1751900981_Thermalright Phantom Spirit 120 SE ARGB 66.17 CFM CPU Cooler.jpg', '', 0, 0.00, 'Thermalright', 25, 'In Stock', '2025-07-08 02:44:53'),
(8, 'Noctua NH-D15 chromax.black 82.52 CFM', 'CPU Cooler', 43000.00, 'uploads/1751997698_Noctua NH-D15 chromax.black 82.52 CFM CPU Cooler.jpg', '', 0, 0.00, 'Noctua', 12, 'In Stock', '2025-07-08 12:31:38'),
(14, 'MSI MAG B650 TOMAHAWK WIFI ATX AM5 Motherboard', 'Motherboard', 45000.00, 'uploads/1752039312_dc9235e0d8052745493eb900bb9df6f6.1600.jpg', 'The **MSI MAG B650 TOMAHAWK WIFI** is an ATX motherboard for AMD AM5 CPUs, supporting DDR5 RAM, PCIe 4.0, Wi-Fi 6E, and fast networking. It offers strong cooling and good connectivity for gaming and productivity.\r\n', 0, 0.00, 'MSI', 9, 'In Stock', '2025-07-11 10:55:55'),
(15, 'MSI B650 GAMING PLUS WIFI ATX AM5 Motherboard', 'Motherboard', 40000.00, 'uploads/1752039603_ff33ebb87ed9f5fa5f9c54d6d316ae82.256p.jpg', 'The **MSI B650 GAMING PLUS WIFI** is an ATX AM5 motherboard with DDR5 support, PCIe 4.0 slots, Wi-Fi 6E, and 2.5G Ethernet. It offers strong power delivery and good cooling for gaming and productivity.\r\n', 0, 0.00, 'MSI', 7, 'In Stock', '2025-07-08 18:40:03'),
(16, 'Gigabyte B550M K Micro ATX AM4 Motherboard', 'Motherboard', 19000.00, 'uploads/1752055228_f52a9a0b2a28f096b5b1e5bf02707224.1600.jpg', 'The Gigabyte B550M K is a micro ATX motherboard for AMD AM4 CPUs, supporting PCIe 4.0, dual M.2 slots, up to 128GB DDR4 RAM, and features HDMI, DisplayPort, and Gigabit LAN. Ideal for compact Ryzen-based builds.', 0, 0.00, 'Gigabyte', 7, 'In Stock', '2025-07-11 10:46:19'),
(17, 'MSI B760 GAMING PLUS WIFI ATX LGA1700 Motherboard', 'Motherboard', 45000.00, 'uploads/1752055431_f22d681ccfd01238e756443a474f400b.1600.jpg', 'ATX B760 motherboard with Wi-Fi 6E, DDR5 support, PCIe 4.0, and dual M.2—ideal for Intel 12th–14th Gen CPUs.', 0, 0.00, 'MSI', 8, 'In Stock', '2025-07-08 23:03:51'),
(18, 'ASRock B650M Pro RS WiFi Micro ATX AM5 Motherboard', 'Motherboard', 35000.00, 'uploads/1752055628_d7893755609db1feb833c99ad9d243bf.1600.jpg', 'A compact AM5 motherboard built for Ryzen 7000 series CPUs, featuring DDR5 support, PCIe 5.0 M.2, Wi-Fi 6E, Bluetooth 5.2, and 2.5Gb LAN. Ideal for high-performance micro ATX builds', 0, 0.00, 'ASRock', 6, 'In Stock', '2025-07-08 23:07:08'),
(19, 'Gigabyte X870 EAGLE WIFI7 ATX AM5 Motherboard', 'Motherboard', 70000.00, 'uploads/1752055881_a06aef05389e93f5e4de5dc26d807abe.1600.jpg', 'High-performance AM5 ATX board with PCIe 5.0, DDR5, Wi-Fi 7, USB4, and triple M.2 slots—ideal for next-gen Ryzen builds.', 0, 0.00, 'Gigabyte', 9, 'In Stock', '2025-07-11 07:31:23'),
(20, 'MSI MAG A650BN 650 W 80+ Bronze Certified ATX Power Supply', 'Power Supply', 10000.00, 'uploads/1752056123_3148f884b77b2d7abee3a3e0ad72cf73.1600.jpg', 'MSI MAG A650BN is a 650W 80+ Bronze certified power supply with reliable protection, quiet 120mm fan, and essential connectors—great for mid-range PCs.', 0, 0.00, 'MSI', 6, 'In Stock', '2025-07-11 12:42:00'),
(21, 'Corsair RM1000e (2023) 1000 W 80+ Gold Certified Fully Modular ATX Power Supply', 'Power Supply', 40000.00, 'uploads/1752056277_ce27e414f67e9e8786401e4260bb85fa.1600.jpg', 'Corsair RM1000e (2023) is a 1000W 80+ Gold fully modular PSU with quiet fan, ATX 3.1 & PCIe 5.1 support, and a 7-year warranty—ideal for high-end builds.', 0, 0.00, 'Corsair', 9, 'In Stock', '2025-07-08 23:17:57'),
(22, 'MSI MAG A850GL PCIE5 850 W 80+ Gold Certified Fully Modular ATX Power Supply', 'Power Supply', 30000.00, 'uploads/1752056482_79be60ce3783b8918a1c0940d3e4ff19.1600.jpg', 'High-efficiency 850W PSU with 80+ Gold certification, PCIe 5.0 support (up to 600W), fully modular design, and quiet 120mm Fluid Dynamic Bearing fan. Ideal for high-performance builds', 0, 0.00, 'MSI', 7, 'In Stock', '2025-07-08 23:21:22'),
(23, 'MSI MAG A750GL PCIE5 750 W 80+ Gold Certified Fully Modular ATX Power Supply', 'Power Supply', 24000.00, 'uploads/1752056646_d8f4d13ba49891eb3929bdddc3d87b4b.1600.jpg', 'High-efficiency 750W PSU with 80+ Gold certification, PCIe 5.0 support (up to 450W), fully modular design, and quiet 120mm Fluid Dynamic Bearing fan. Ideal for high-performance builds.', 0, 0.00, 'MSI', 9, 'In Stock', '2025-07-08 23:24:06'),
(24, 'Corsair RM850 850 W 80+ Gold Certified Fully Modular ATX Power Supply', 'Power Supply', 35000.00, 'uploads/1752057164_1752baa3ddb162df098cf33b0b6eeae3.1600.jpg', 'Corsair RM850 is an 850W, 80+ Gold certified, fully modular ATX power supply designed for reliable and efficient performance in high-demand PC builds.', 0, 0.00, 'Corsair', 7, 'In Stock', '2025-07-08 23:32:44'),
(25, 'Corsair CX (2023) 650 W 80+ Bronze Certified ATX Power Supply', 'Power Supply', 21000.00, 'uploads/1752057370_c058ff0ffb542302d5b1c75cf8658ecd.1600.jpg', 'Corsair CX (2023) is a 650W, 80+ Bronze certified ATX power supply offering reliable power delivery with a non-modular design for budget-friendly PC builds.\r\n', 0, 0.00, 'Corsair', 7, 'In Stock', '2025-07-08 23:36:10'),
(26, 'Asus TUF Gaming VG27AQ 27.0\" 2560 x 1440 165 Hz Monitor', 'Monitor', 60000.00, 'uploads/1752058480_16988d4e994d281aa599d856ecd9f8bd.1600.jpg', 'The Asus TUF Gaming VG27AQ is a 27-inch 2560x1440 QHD gaming monitor with a 165Hz refresh rate, 1ms response time, HDR10 support, and G-SYNC compatibility for smooth, immersive gameplay.\r\n', 0, 0.00, 'Asus', 9, 'In Stock', '2025-07-08 23:54:40'),
(27, 'LG 65EP5G-B 65.0\" 3840 x 2160 120 Hz Monitor', 'Monitor', 90000.00, 'uploads/1752058733_b01d97e380b0840e5a7f789b31ec3a3f.1600.jpg', 'The LG 65EP5G-B is a 65-inch 4K UHD OLED monitor with a 120Hz refresh rate, 0.1ms response time, HDR10 support, and professional-grade color accuracy for content creation and broadcast use.\r\n', 0, 0.00, 'LG', 7, 'In Stock', '2025-07-08 23:58:53'),
(28, 'KOORUI 24E3 24.0\" 1920 x 1080 165 Hz Monitor', 'Monitor', 36000.00, 'uploads/1752059178_62a0a3d374d373fe78df925242bd9951.1600.jpg', 'The KOORUI 24E3 is a 24-inch Full HD IPS gaming monitor with a 165Hz refresh rate, 1ms response time, 99% sRGB color, and Adaptive Sync for smooth gameplay.\r\n', 0, 0.00, 'KOORUI', 9, 'In Stock', '2025-07-09 00:06:18'),
(29, 'MSI MAG 275QF 27.0\" 2560 x 1440 180 Hz Monitor', 'Monitor', 60000.00, 'uploads/1752059372_57207990ab73d40707fd5756f9680995.1600.jpg', 'MSI MAG 275QF is a 27-inch WQHD (2560×1440) gaming monitor with a rapid IPS panel, 180Hz refresh rate, and 0.5ms response time. It supports Adaptive-Sync for smooth gameplay, offers vibrant colors, HDR readiness, and a sleek frameless design—ideal for competitive gaming and immersive visuals.\r\n', 0, 0.00, 'MSI', 7, 'In Stock', '2025-07-09 00:09:32'),
(30, 'MSI G255F 24.5\" 1920 x 1080 180 Hz Monitor', 'Monitor', 33000.00, 'uploads/1752059580_42806ef6786626615ccf2f7bb7157d3b.1600.jpg', 'The MSI G255F is a 24.5\" Full HD (1920x1080) gaming monitor featuring a fast 180Hz refresh rate and 1ms response time. It uses an IPS panel for vibrant colors and wide viewing angles, supports AMD FreeSync for smooth gameplay, and offers multiple connectivity options including DisplayPort and HDMI. Ideal for competitive gaming with crisp visuals and minimal motion blur.\r\n', 0, 0.00, 'MSI', 8, 'In Stock', '2025-07-09 00:13:00'),
(31, 'LG 24MR400-B 23.8\" 1920 x 1080 100 Hz Monitor', 'Monitor', 17000.00, 'uploads/1752059775_401ded619278efffb3eb22a19380487d.1600.jpg', 'The LG 24MR400-B is a 23.8\" Full HD (1920x1080) IPS monitor with a 100Hz refresh rate and 5ms response time. It features vibrant colors, wide viewing angles, AMD FreeSync support, and a sleek 3-side borderless design, ideal for smooth gaming and everyday use.\r\n', 0, 0.00, 'LG', 7, 'In Stock', '2025-07-11 12:22:29'),
(32, 'Phanteks XT PRO ATX Mid Tower Case', 'PC Case', 20000.00, 'uploads/1752060149_4beb27272519c97f098ceab48df1e12c.1600.jpg', 'The Phanteks XT PRO is a sleek ATX mid-tower case featuring tempered glass, excellent airflow with support for up to 10 fans, extensive radiator compatibility, and integrated D-RGB lighting. It offers spacious interior layout, versatile cooling options, and clean cable management for high-performance PC builds.\r\n', 0, 0.00, 'Phanteks', 8, 'In Stock', '2025-07-09 00:22:29'),
(33, 'Montech XR ATX Mid Tower Case', 'PC Case', 30000.00, 'uploads/1752060353_41EQBUlNKML.jpg', 'The Montech XR is an ATX mid-tower case with dual tempered glass panels, wood-grain I/O, and three pre-installed ARGB fans. It supports high-end GPUs, up to 9 fans, and a 360mm radiator for great cooling.\r\n', 0, 0.00, 'Montech', 6, 'In Stock', '2025-07-09 00:25:53'),
(34, 'NZXT H5 Flow (2024) ATX Mid Tower Case', 'PC Case', 26000.00, 'uploads/1752060666_640e44d6b2b0264413b089545b628d0a.1600.jpg', 'The NZXT H5 Flow (2024) is a compact ATX mid-tower case featuring ultra-fine mesh panels for excellent airflow, pre-installed quiet fans, and support for up to 360mm radiators. It offers efficient cooling, easy cable management, and durable steel and tempered glass construction.\r\n', 0, 0.00, 'NZXT', 7, 'In Stock', '2025-07-09 00:31:06'),
(35, 'Cooler Master Hyper 212 Black Edition 42 CFM CPU Cooler', 'CPU Cooler', 6899.98, 'uploads/1752039496_716d4c601ae190184020710e098e7b36.256p.jpg', 'The Cooler Master Hyper 212 Black Edition is a stylish air cooler with a 120mm fan, 42 CFM airflow, and four heat pipes for efficient CPU cooling.', 0, 0.00, 'Cooler Master', 5, 'Low Stock', '2025-07-08 18:38:16'),
(36, 'Thermalright Peerless Assassin 120 SE 66.17 CFM CPU Cooler', 'CPU Cooler', 10200.00, 'uploads/1752040000_41hFTmi5aUL.jpg', 'The Thermalright Peerless Assassin 120 SE is a dual-tower air cooler with two 120mm fans, 66.17 CFM airflow, and six heat pipes for excellent cooling and low noise.\r\n', 0, 0.00, 'Thermalright', 7, 'In Stock', '2025-07-08 18:46:40'),
(37, 'ARCTIC Liquid Freezer III Pro 360 77 CFM Liquid CPU Cooler', 'CPU Cooler', 25800.00, 'uploads/1752040278_a0b2a92bb4ec2c95b5e492c9513b2c0b.256p.jpg', 'The ARCTIC Liquid Freezer III Pro 360 is a powerful 360mm AIO cooler with three 120mm fans (77 CFM), a thick radiator, and an integrated VRM fan for excellent cooling and quiet performance.\r\n', 0, 0.00, 'ARCTIC', 6, 'In Stock', '2025-07-08 18:51:18'),
(38, 'Corsair Vengeance LPX 16 GB (2 x 8 GB) DDR4-3200 CL16 Memory', 'Memory', 26700.00, 'uploads/1752046212_fee3ba4d684ea643cc72a1c38f0dbc2f.256p.jpg', 'Corsair Vengeance LPX 16GB (2×8GB) DDR4-3200 CL16 is a high-speed, low-profile RAM kit with reliable performance, aluminum heat spreaders, and XMP 2.0 support for easy overclocking.', 0, 0.00, 'Corsair', 7, 'In Stock', '2025-07-08 20:30:12'),
(39, 'G.Skill Flare X5 32 GB (2 x 16 GB) DDR5-6000 CL36 Memory', 'Memory', 26700.00, 'uploads/1752046845_fee3ba4d684ea643cc72a1c38f0dbc2f.256p.jpg', 'G.Skill Flare X5 32GB DDR5-6000 CL36 is a fast, low-profile RAM kit with EXPO support, ideal for AMD systems and high-performance builds.\r\n', 0, 0.00, 'G.Skill', 8, 'In Stock', '2025-07-08 20:40:45'),
(40, 'Corsair Vengeance RGB 32 GB (2 x 16 GB) DDR5-6000 CL36 Memory', 'Memory', 27000.00, 'uploads/1752047186_5cdd0dfcd25374317a12808fa7f63c19.1600.jpg', 'Corsair Vengeance RGB 32GB (2×16GB) DDR5-6000 CL36 is a high-speed memory kit with dynamic RGB lighting, XMP/EXPO support, and strong performance for gaming and multitasking.\r\n', 0, 0.00, 'Corsair', 6, 'In Stock', '2025-07-08 20:46:26'),
(41, 'Corsair Vengeance 32 GB (2 x 16 GB) DDR5-6000 CL30 Memory', 'Memory', 48700.00, 'uploads/1752047538_fe414d3559a9bbb2e092ba5374f6e1ed.256p.jpg', 'Corsair Vengeance 32GB (2×16GB) DDR5-6000 CL30 is a high-performance RAM kit with ultra-low latency, XMP/EXPO support, and a sleek heat spreader—perfect for gaming and productivity.\r\n', 0, 0.00, 'Corsair', 7, 'In Stock', '2025-07-11 07:33:58'),
(42, 'Samsung 870 Evo 1 TB 2.5\" Solid State Drive', 'Storage', 22500.00, 'uploads/1752048526_31ITAX-GoIL.jpg', 'Samsung 870 Evo 1TB is a reliable 2.5\" SATA SSD with fast speeds up to 560 MB/s, 5-year warranty, and broad compatibility for desktops and laptops.\r\n', 0, 0.00, 'Samsung', 7, 'In Stock', '2025-07-08 21:08:46'),
(43, 'Samsung 990 Pro 2 TB M.2-2280 PCIe 4.0 X4 NVME Solid State Drive', 'Storage', 55500.00, 'uploads/1752049016_4cdbd04a2d7c19789dd2bdc072b4a506.1600.jpg', 'Samsung 990 Pro 2TB is a blazing-fast M.2 NVMe SSD with PCIe 4.0, delivering up to 7,450 MB/s read and 6,900 MB/s write speeds, ideal for gaming and heavy workloads.\r\n', 0, 0.00, 'Samsung', 6, 'In Stock', '2025-07-11 07:34:37'),
(44, 'Sabrent Rocket 4 Plus 8 TB M.2-2280 PCIe 4.0 X4 NVME Solid State Drive', 'Storage', 275700.00, 'uploads/1752049839_a1f3e983bdceebc282929dee27df1db3.1600.jpg', 'Sabrent Rocket 4 Plus 8TB is a high-capacity M.2 PCIe 4.0 SSD with speeds up to 7,100 MB/s, ideal for heavy workloads, gaming, and large file storage.\r\n', 0, 0.00, 'Sabrent', 7, 'In Stock', '2025-07-08 21:30:39'),
(45, 'Western Digital WD_Black SN850X 2 TB M.2-2280 PCIe 4.0 X4 NVME Solid State Drive', 'Storage', 42600.00, 'uploads/1752050008_8e6dbae8a0f3c6572216c8758ce5a0b5.256p.jpg', 'WD Black SN850X 2TB is a fast PCIe 4.0 M.2 SSD with up to 7,300 MB/s read speed, great for gaming and heavy workloads, featuring optional heatsink and 5-year warranty.\r\n', 0, 0.00, 'Western Digital', 7, 'In Stock', '2025-07-08 21:33:28'),
(46, 'Crucial T500 2 TB M.2-2280 PCIe 4.0 X4 NVME Solid State Drive', 'Storage', 37200.00, 'uploads/1752050230_3fd5aee2fd8854d15ef3c81dcd9599f5.256p.jpg', 'Crucial T500 2TB is a fast PCIe 4.0 M.2 SSD with up to 7,400 MB/s read and 7,000 MB/s write speeds, great for gaming and heavy tasks.\r\n', 0, 0.00, 'Crucial', 6, 'In Stock', '2025-07-08 21:37:10'),
(47, 'Klevv CRAS C910 2 TB M.2-2280 PCIe 4.0 X4 NVME Solid State Drive', 'Storage', 26700.00, 'uploads/1752050424_31dtu8k-RkL.jpg', 'Klevv CRAS C910 2TB is a PCIe 4.0 M.2 SSD with up to 5,200 MB/s read and 4,800 MB/s write speeds, plus a 5-year warranty and optional heatsink.\r\n', 0, 0.00, 'Klevv', 9, 'In Stock', '2025-07-08 21:40:24'),
(48, 'Corsair Vengeance LPX 32 GB (2 x 16 GB) DDR4-3200 CL16 Memory', 'Memory', 22200.00, 'uploads/1752051377_ae9c14173c768f2fa9ad4d3c957e94a0.256p.jpg', 'Corsair Vengeance LPX 32GB (2x16GB) DDR4-3200 CL16 RAM offers reliable, fast performance with low-profile heat spreaders and easy overclocking support.\r\n', 0, 0.00, 'Corsair', 7, 'In Stock', '2025-07-08 21:56:17'),
(49, 'Silicon Power GAMING 16 GB (2 x 8 GB) DDR4-3200 CL16 Memory', 'Memory', 9300.00, 'uploads/1752051610_62a4ba196f5f165e68619a63ef5d0b70.256p.jpg', 'Silicon Power GAMING 16GB (2x8GB) DDR4-3200 CL16 RAM offers solid performance with heat spreaders and lifetime warranty.\r\n', 0, 0.00, 'Silicon Power', 8, 'In Stock', '2025-07-08 22:00:10'),
(50, 'AMD Ryzen 5 5600X 3.7 GHz 6-Core Processor', 'CPU', 35000.00, 'uploads/1752235186_1751996013_AMD Ryzen 7 7800X3D 4.2 GHz 8-Core Processor.jpg', 'The AMD Ryzen 5 5600X is a 6-core, 12-thread processor with a 3.7 GHz base clock, ideal for gaming and multitasking. It offers fast performance and includes a stock cooler.', 0, 0.00, 'AMD', 7, 'In Stock', '2025-07-11 11:59:46'),
(52, 'AMD Ryzen 7 9700X 3.8 GHz 8-Core Processor', 'CPU', 65000.00, 'uploads/1752038665_a0f5a161d8c7ff3c6b0827423537a029.256p.jpg', 'The AMD Ryzen 7 9700X is an 8-core, 16-thread desktop CPU with a 3.8 GHz base clock, offering strong multi-threaded and gaming performance. It\'s built for users who need high processing power for multitasking, content creation, and heavy workloads.', 0, 0.00, 'AMD', 8, 'In Stock', '2025-07-11 12:42:57'),
(53, 'Intel Core i5-12400F 2.5 GHz 6-Core Processor Intel Core i9-14900K 3.2 GHz 24-Core Processor Low', 'CPU', 84000.00, 'uploads/1752231652_5fe3c9cc8cbaaa4aa52aed7389d2cc10.1600.jpg', 'The **Intel i5-12400F** is a 6-core CPU for smooth gaming and daily tasks, requiring a GPU. The **Intel i9-14900K** is a 24-core chip built for high-end performance and heavy workloads.\r\n', 0, 0.00, 'Intel', 6, 'In Stock', '2025-07-11 11:00:52'),
(55, 'MSI B650 GAMING PLUS WIFI ATX AM5 Motherboard', 'Motherboard', 40000.00, 'uploads/1752039603_ff33ebb87ed9f5fa5f9c54d6d316ae82.256p.jpg', 'The **MSI B650 GAMING PLUS WIFI** is an ATX AM5 motherboard with DDR5 support, PCIe 4.0 slots, Wi-Fi 6E, and 2.5G Ethernet. It offers strong power delivery and good cooling for gaming and productivity.\r\n', 0, 0.00, 'MSI', 7, 'In Stock', '2025-07-08 13:10:03'),
(56, 'MSI GeForce RTX 3060 Ventus 2X 12G GeForce RTX 3060 12GB 12 GB Video Card', 'Video Card', 76000.00, 'uploads/1752055653_dbc81b89efc82ce66fb2e3ab7e0f0658.1600.jpg', 'MSI RTX 3060 Ventus 2X 12G is a compact dual-fan GPU with 12GB GDDR6, ideal for 1080p/1440p gaming with ray tracing and DLSS support.\r\n', 0, 0.00, 'MSI', 6, 'In Stock', '2025-07-11 12:43:45'),
(57, 'Gigabyte WINDFORCE OC SFF GeForce RTX 5070 12 GB Video Card', 'Video Card', 154700.00, 'uploads/1752055989_136567e8e098d881d15de177db1e9243.1600.jpg', 'Gigabyte RTX 5070 WINDFORCE OC SFF is a compact 12GB GDDR7 GPU built for smooth 1440p gaming with quiet triple-fan cooling.\r\n', 0, 0.00, 'Gigabyte', 7, 'In Stock', '2025-07-11 12:31:23'),
(58, 'MSI SHADOW 3X OC GeForce RTX 5070 Ti 16 GB Video Card', 'Video Card', 250000.00, 'uploads/1752056329_0e14bfa5ff48bf5a64a68bd5ccacfbd2.1600.jpg', 'MSI RTX 5070 Ti SHADOW 3X OC is a sleek, powerful 16GB GDDR7 GPU built for high-end 1440p/4K gaming with quiet triple-fan cooling.\r\n', 0, 0.00, 'MSI', 6, 'In Stock', '2025-07-11 07:33:10'),
(59, 'Sapphire PULSE Radeon RX 9070 XT 16 GB Video Card', 'Video Card', 215700.00, 'uploads/1752056742_476e6143a031380658f2fdb9d5a2126d.256p.jpg', 'Sapphire PULSE RX 9070 XT is a powerful 16GB GDDR6 GPU for smooth 1440p and 4K gaming with efficient triple-fan cooling.\r\n', 0, 0.00, 'Sapphire', 8, 'In Stock', '2025-07-08 23:25:42'),
(60, 'Asus ROG Astral OC GeForce RTX 5090 32 GB Video Card', 'Video Card', 1007700.00, 'uploads/1752057304_0161407080353d18971dcccd15dc3722.1600.jpg', 'Asus ROG Astral OC RTX 5090 is a flagship 32GB GDDR7 GPU with 21,760 CUDA cores, advanced quad-fan cooling, and top performance for 4K gaming and content creation.\r\n', 0, 0.00, 'Asus', 9, 'In Stock', '2025-07-08 23:35:04'),
(61, 'Gigabyte GAMING OC Radeon RX 9070 XT 16 GB Video Card', 'Video Card', 218700.00, 'uploads/1752057524_74a2c2cec2f57c4e790633d81b35cd4f.1600.jpg', 'Gigabyte GAMING OC RX 9070 XT is a 16GB GDDR6 GPU with high boost clocks, triple-fan WINDFORCE cooling, and strong 1440p/4K gaming performance.\r\n', 0, 0.00, 'Gigabyte', 7, 'In Stock', '2025-07-08 23:38:44'),
(62, 'Microsoft Windows 11 Pro OEM - DVD 64-bit', 'Operating System', 41700.00, 'uploads/1752058543_b06dd204711f15e712f892efcb1dd3df.1600.jpg', 'Windows 11 Pro OEM (DVD, 64-bit) is a one-time install OS for new PCs, offering advanced security, business tools, and modern productivity features.\r\n', 0, 0.00, 'Microsoft', 6, 'In Stock', '2025-07-08 23:55:43'),
(63, 'Microsoft Windows 11 Home OEM - DVD 64-bit', 'Operating System', 32700.00, 'uploads/1752058703_ab7d4d98513e78fab6b0ca42894bda65.1600.jpg', 'Windows 11 Home OEM (DVD, 64-bit) is a one-time install OS for new PCs, offering essential features, security, and a modern interface for everyday use.\r\n', 0, 0.00, 'Microsoft', 7, 'In Stock', '2025-07-08 23:58:23'),
(64, 'Microsoft Windows 11 Home Retail - USB 64-bit', 'Operating System', 38700.00, 'uploads/1752058934_ed2349ecd618439a2aa96364e9445138.1600.jpg', 'Windows 11 Home Retail (USB, 64-bit) is a user-friendly OS with modern features and a transferable license, ideal for personal use and easy installation via USB.\r\n', 0, 0.00, 'Microsoft', 5, 'Low Stock', '2025-07-09 00:02:14'),
(65, 'Microsoft Windows 11 Pro Retail - Download 64-bit', 'Operating System', 59700.00, 'uploads/1752059133_5975493ee399fce8bc8df92e7acfbbe2.1600.jpg', 'Windows 11 Pro Retail (Download, 64-bit) is a professional OS with advanced features and a transferable license—ideal for business use and easy digital installation.\r\n', 0, 0.00, 'Microsoft', 8, 'In Stock', '2025-07-09 00:05:33'),
(66, 'Microsoft Windows 10 Pro OEM - DVD 64-bit', 'Operating System', 300000.00, 'uploads/1752059658_e2020af0d40a275d81e0eec3386efa7e.1600.jpg', 'Windows 10 Pro OEM (DVD, 64-bit) is a one-time install operating system for new PCs, offering business features like BitLocker, Remote Desktop, and domain join support.\r\n', 0, 0.00, 'Microsoft', 7, 'In Stock', '2025-07-09 00:14:18'),
(67, 'Microsoft Windows 10 Pro Retail - USB 32/64-bit', 'Operating System', 300000.00, 'uploads/1752059861_e61db9f11b1551d54cc2635bc9bdbd32.1600.jpg', 'Windows 10 Pro Retail (USB, 32/64-bit) is a versatile OS for professionals, featuring advanced tools like BitLocker, Remote Desktop, and domain join, with a transferable license and easy USB installation.\r\n', 0, 0.00, 'Microsoft', 8, 'In Stock', '2025-07-09 00:17:41'),
(68, 'Corsair 4000D Airflow ATX Mid Tower Case', 'PC Case', 31200.00, 'uploads/1752060648_bc6e987da3fe22c616898d1d7fa3d227.1600.jpg', 'Corsair 4000D Airflow is a mid-tower PC case with high airflow, roomy interior, easy cable management, and support for large GPUs and radiators.\r\n', 0, 0.00, 'Corsair', 8, 'In Stock', '2025-07-09 00:30:48'),
(69, 'NZXT H9 Flow (2023) ATX Mid Tower Case', 'PC Case', 38700.00, 'uploads/1752060918_15fb785fb4d0995e7cc7e28a6f2271d9.1600.jpg', 'NZXT H9 Flow (2023) is a sleek ATX mid-tower case with excellent airflow, spacious interior, and strong cooling support.\r\n', 0, 0.00, 'NZXT', 7, 'In Stock', '2025-07-09 00:35:18'),
(70, 'Lian Li Lancool 207 ATX Mid Tower Case', 'PC Case', 24300.00, 'uploads/1752061160_393f3d73165045839334a1621040ee20.1600.jpg', 'Lian Li Lancool 207 is a compact ATX mid-tower case with great airflow, ARGB fans, and good cooling support.\r\n', 0, 0.00, 'Lian Li', 7, 'In Stock', '2025-07-09 00:39:20'),
(71, 'Redragon DEVARAIAS K556 RGB Wired Gaming Keyboard', 'Keyboard', 1500.00, 'uploads/1752071619_41vSWpjgMgL.jpg', 'Redragon DEVARAIAS K556 is a durable mechanical gaming keyboard featuring RGB backlighting, custom mechanical switches for tactile feedback, full metal construction, and anti-ghosting keys for reliable performance during intense gaming sessions.', 0, 0.00, 'Redragon', 13, 'In Stock', '2025-07-09 09:03:39'),
(72, 'Razer Huntsman Mini RGB Wired Mini Keyboard', 'Keyboard', 2300.00, 'uploads/1752071727_1411d2ffe69ca84f0c740ee5d0df4a0e.256p.jpg', 'Razer Huntsman Mini is a compact 60% mechanical gaming keyboard with ultra-fast optical switches, vibrant customizable RGB lighting, and a sleek, portable design—ideal for minimal setups and competitive gaming.', 0, 0.00, 'Razer', 10, 'In Stock', '2025-07-09 09:05:27'),
(73, 'SteelSeries Apex 3 TKL RGB Wired Gaming Keyboard', 'Keyboard', 1600.00, 'uploads/1752071822_41tJtjMmKCL.jpg', 'SteelSeries Apex 3 TKL is a compact, tenkeyless gaming keyboard with quiet, whisper-quiet switches, customizable RGB lighting, and water resistance—perfect for streamlined setups and long gaming sessions.', 0, 0.00, 'SteelSeries', 6, 'In Stock', '2025-07-09 09:07:02'),
(74, 'RK Royal Kludge RK61 Bluetooth/Wireless/Wired Mini Keyboard', 'Keyboard', 1800.00, 'uploads/1752071970_41lgkMJ5btL.jpg', 'RK Royal Kludge RK61 is a compact 60% mechanical keyboard with triple-mode connectivity—Bluetooth, 2.4GHz wireless, and USB-C wired. It features hot-swappable switches, vibrant RGB lighting, and a portable design, making it ideal for both gaming and on-the-go productivity.', 0, 0.00, 'RK Royal Kludge', 7, 'In Stock', '2025-07-09 09:09:30'),
(75, 'Logitech G305 LIGHTSPEED Wireless/Wired Optical Mouse', 'Mouse', 900.00, 'uploads/1752072181_e5ae54669b6dc857c4d03a26e70ed9d5.256p.jpg', 'Logitech G305 LIGHTSPEED is a wireless gaming mouse with ultra-fast 1ms response time, a HERO 12K sensor for precise tracking, and up to 250 hours of battery life offering top-tier performance in a lightweight, compact design.', 0, 0.00, 'Logitech', 10, 'In Stock', '2025-07-09 09:13:01'),
(76, 'SteelSeries Rival 3 Wired Optical Mouse', 'Mouse', 750.00, 'uploads/1752072282_24c3004e0bf166466ca2e8cf1ab55490.256p.jpg', 'A lightweight (~77 g), ambidextrous wired gaming mouse featuring the TrueMove Core sensor (100–8,500 CPI) for true 1:1 tracking, durable 60 million-click switches, customizable 3‑zone Prism RGB lighting, and onboard memory—offering excellent performance and comfort in a budget-friendly package .', 0, 0.00, 'SteelSeries', 9, 'In Stock', '2025-07-09 09:14:42'),
(77, 'Razer Basilisk V3 Wired Optical Mouse', 'Mouse', 1200.00, 'uploads/1752073722_d662cc7b90e3fd45502f7c77d9b54269.256p.jpg', 'It is an ergonomic wired gaming mouse with a high-precision 26K DPI sensor, customizable 11 buttons, a programmable HyperScroll wheel, and vibrant RGB lighting—designed for comfort and versatile gameplay.', 0, 0.00, 'Razer', 9, 'In Stock', '2025-07-09 09:38:42'),
(78, 'Razer DeathAdder Essential Wired Optical Mouse', 'Mouse', 649.98, 'uploads/1752073825_d6fd43322f07dedde7c5c3d9e53706d1.256p.jpg', 'It is a wired ergonomic gaming mouse with a 6,400 DPI optical sensor, 5 programmable buttons, and durable build—designed for comfortable and precise gameplay.', 0, 0.00, 'Razer', 10, 'In Stock', '2025-07-09 09:40:25'),
(79, 'Syba CM-502 Headset', 'Headset', 1499.98, 'uploads/1752074074_41Ak3kL+JJL.jpg', 'Affordable wired stereo headset with adjustable headband, omnidirectional mic, and in-line volume controls.', 0, 0.00, 'Syba', 10, 'In Stock', '2025-07-09 09:44:34'),
(80, 'HP HyperX Cloud II 7.1 Channel Headset', 'Headset', 2100.00, 'uploads/1752074194_72a586f112591cd7b43f4b0dc244ba29.256p.jpg', 'is a wired gaming headset with virtual 7.1 surround sound, comfortable memory foam cushions, a noise-cancelling mic, and multi-platform compatibility for immersive gameplay.', 0, 0.00, 'HP', 10, 'In Stock', '2025-07-09 09:46:34'),
(81, 'Razer Hammerhead Pro v2 In Ear With Microphone', 'Microphone', 700.00, 'uploads/1752074357_d9d896b309f2415a57c0ba8be1b91f51.256p.jpg', 'Razer Hammerhead Pro V2 are wired in-ear headphones with 10mm drivers, a built-in microphone with remote control, flat tangle-free cable, and multiple ear tip sizes for a comfortable fit.', 0, 0.00, 'Razer', 8, 'In Stock', '2025-07-09 09:49:17'),
(82, 'Logitech C270 Webcam', 'Webcam', 6000.00, 'uploads/1752074495_2ea46c5847b08c504b33b7d4c8ef39d7.256p.jpg', 'Logitech C270 Webcam is an affordable HD 720p webcam with built-in microphone, easy USB plug-and-play setup, and clear video quality for video calls and streaming.', 0, 0.00, 'Logitech', 10, 'In Stock', '2025-07-09 09:51:35'),
(83, 'Creative Labs Pebble 2.0 4.4 W Speakers', 'Speakers', 3000.00, 'uploads/1752074610_31jmgojlr-L.jpg', 'Creative Labs Pebble 2.0 4.4W Speakers are compact USB-powered desktop speakers with angled drivers for clear, balanced sound—ideal for small workspaces.', 0, 0.00, 'Creative Labs', 8, 'In Stock', '2025-07-09 09:53:30'),
(84, 'Asus Xonar SE 24-bit 192 kHz Sound Card', 'Sound Card', 1200.00, 'uploads/1752074769_44d27c13de46b5eb697cfd598740f3a2.256p.jpg', 'Asus Xonar SE is a PCIe sound card delivering high-resolution 5.1-channel audio with 24-bit/192kHz quality and a built-in headphone amplifier for enhanced sound clarity.', 0, 0.00, 'Asus', 8, 'In Stock', '2025-07-09 09:56:09'),
(85, 'Rosewill RNX-N150PCx 802.11a/b/g/n PCI Wi-Fi Adapter', 'Cables', 1000.00, 'uploads/1752074937_41Mb6y2O7ML.jpg', 'This adapter is compatible with Windows XP, Vista, 7, and 8, providing a reliable wireless connection for desktop PCs.', 0, 0.00, 'Rosewill', 10, 'In Stock', '2025-07-09 09:58:57'),
(86, 'Thermal Grizzly Duronaut 6 g', 'Thermal Paste', 600.00, 'uploads/1752075025_41e1hZ295PL.jpg', '', 0, 0.00, 'Thermal Grizzly', 9, 'In Stock', '2025-07-09 10:00:25'),
(87, 'Thermalright TL-C12C X3 66.17 CFM 120 mm Fans', 'Fans', 2000.00, 'uploads/1752075105_41KqlL2aSvL.jpg', '', 0, 0.00, 'Thermalright', 11, 'In Stock', '2025-07-09 10:01:45'),
(88, 'Syba SY-HUB24047 4 x Gigabit Ethernet ', 'USB Hub', 799.99, 'uploads/1752075180_f0a8b547f7d2f63c7d2d8b91adcbcb39.256p.jpg', '', 0, 0.00, 'Syba', 10, 'In Stock', '2025-07-09 10:03:00'),
(89, 'Rosewill RNX-N150PCx', 'Network Card', 700.00, 'uploads/1752075362_download.jpg', 'Rosewill RNX-N150PCx is a PCI wireless network card supporting 802.11b/g/n with speeds up to 150Mbps, featuring a detachable antenna for stable, basic Wi-Fi connectivity on desktop PCs.', 0, 0.00, 'Rosewill', 10, 'In Stock', '2025-07-09 10:06:02'),
(92, 'FanXiang S101 128 GB 2.5\" Solid State Drive', 'Storage', 21000.00, 'uploads/1752214766_41cZpkFrJ3L.jpg', 'FanXiang S101 128 GB 2.5″ SATA III SSD is an internal solid-state drive delivering up to 550 MB/s read and ~500 MB/s write speeds. Featuring 3D NAND TLC, it offers faster boot times, quieter operation, and improved system responsiveness compared to HDDs. Compatible with laptops and desktops via SATA III.', 0, 0.00, 'FanXiang', 10, 'In Stock', '2025-07-11 00:49:26'),
(93, 'MSI VENTUS 2X OC GeForce RTX 3050 6GB ', 'Video Card', 40000.00, 'uploads/1752215200_f86c8c5780481bd5cfd54e7251ce354e.256p.jpg', 'MSI VENTUS 2X OC GeForce RTX 3050 6GB is a dual‑fan graphics card based on NVIDIA’s Ampere architecture. It features 2,304 CUDA cores, 6 GB GDDR6 memory, and a 1492 MHz boost clock for smooth 1080p gaming, real‑time ray tracing, and AI-enhanced performance in a compact, efficient design.', 0, 0.00, 'MSI', 10, 'In Stock', '2025-07-11 12:47:11'),
(94, 'GameMax Nova N5 ATX Mid Tower Case', 'PC Case', 8000.00, 'uploads/1752215459_3fe68eb2222eba68bc559d39533233ae.256p.jpg', 'GameMax Nova N5 ATX Mid Tower Case is a stylish mid-tower PC enclosure that supports ATX, Micro‑ATX, and Mini‑ITX motherboards. It features a tempered glass side panel, convenient front I/O ports (USB 3.0 & audio), multiple fan mounts for optimized airflow, and ample space for graphics cards and cooling systems—combining aesthetic appeal with functional flexibility.', 0, 0.00, 'GameMax', 7, 'In Stock', '2025-07-11 12:35:07'),
(95, 'Thermalright Assassin X 120 Refined SE 66.17 CFM', 'CPU Cooler', 6000.00, 'uploads/1752215675_41aJwdGRuAL.jpg', 'ChatGPT said:\r\nThermalright Assassin X 120 Refined SE is a single-tower CPU air cooler with four 6 mm copper heat pipes and a 120 mm PWM fan delivering up to 66 CFM airflow. It offers quiet operation (≤25.6 dBA), supports modern Intel and AMD sockets, and balances effective cooling and low noise in a compact design.', 0, 0.00, 'Thermalright', 12, 'In Stock', '2025-07-11 01:04:35'),
(96, 'Microsoft Windows 11 Home OEM - DVD 64-bit', 'Operating System', 6500.00, 'uploads/1752215975_ab7d4d98513e78fab6b0ca42894bda65.256p.jpg', 'Microsoft Windows 11 Home OEM (64-bit DVD) is a licensed OS for new PC builds, offering a modern interface, enhanced security, and essential features—delivered via installation DVD.', 0, 0.00, 'Microsoft', 12, 'In Stock', '2025-07-11 12:38:01'),
(97, 'Microsoft Windows 11 Home Retail - USB 64-bit', 'Operating System', 13000.00, 'uploads/1752216124_ed2349ecd618439a2aa96364e9445138.256p.jpg', 'Microsoft Windows 11 Home Retail (USB, 64‑bit) is a full-license operating system on a USB installer, offering a modern user interface with features like Snap layouts and widgets, built-in security (TPM 2.0, Secure Boot), and transferable retail licensing—ideal for upgrading or clean-installing on multiple PCs.', 0, 0.00, 'Microsoft', 7, 'In Stock', '2025-07-11 01:12:04'),
(98, 'Microsoft Windows 10 Home OEM - DVD 32-bit', 'Operating System', 17000.00, 'uploads/1752216243_f5e3c78aadf16d536c31b0fa088c8306.256p.jpg', 'Microsoft Windows 10 Home OEM (DVD, 32‑bit) is a full-version operating system sold on DVD for new PC builds. It offers features like the familiar Start menu, fast startup, built-in security, and compatibility with existing hardware and software. The OEM license is tied to one machine and doesn\'t include Microsoft support.', 0, 0.00, 'Microsoft', 10, 'In Stock', '2025-07-11 01:14:03'),
(99, 'Microsoft Windows 8.1 Pro OEM 64-bit', 'Operating System', 21500.00, 'uploads/1752216362_41NkygesugL.jpg', 'ChatGPT said:\r\nMicrosoft Windows 8.1 Pro OEM (64‑bit) is a full‑version operating system — ideal for new PC builds — offering enhanced business features like BitLocker encryption, Remote Desktop and domain join, alongside the standard 8.1 interface with Start screen, touch support, and Windows Store access. The OEM version requires a clean install and is licensed per device, but includes all Pro features at a lower cost.', 0, 0.00, 'Microsoft', 8, 'In Stock', '2025-07-11 01:16:02'),
(100, 'Thermalright Assassin King SE ARGB 66.17 CFM', 'CPU Cooler', 9600.00, 'uploads/1752216506_41oLozsjh1L.jpg', 'Thermalright Assassin King SE ARGB is a single‑tower CPU air cooler featuring five 6 mm heat pipes with anti‑gravity tech, a 120 mm PWM ARGB fan (66 CFM, ≤25.6 dBA), and compatibility with modern Intel & AMD sockets—offering powerful, quiet cooling with vivid lighting.', 0, 0.00, 'Thermalright ', 10, 'In Stock', '2025-07-11 01:18:26'),
(102, 'AMD Ryzen 5 1600 (12nm) 3.2 GHz 6-Core Processor', 'CPU', 30000.00, 'uploads/1752236186_1752234643_2447958ccca245b5827bf05929f870e6.256p.jpg', 'AMD Ryzen 5 1600 (12nm) is a 6-core processor with a base clock of 3.2 GHz, designed for smooth multitasking and entry-level gaming, offering solid performance on a budget with improved efficiency from its 12nm process.', 0, 0.00, 'AMD', 6, 'In Stock', '2025-07-11 12:16:26'),
(103, 'Planar PLL2210MW 22.0\" 1920 x 1080 Monitor', 'Monitor', 6000.00, 'uploads/1752236829_91251942d23c415292a5b76b2abe8c41.256p.jpg', 'Planar PLL2210MW is a 22-inch Full HD (1920 x 1080) monitor that delivers clear visuals and a widescreen display, ideal for everyday computing, office tasks, and casual media viewing.', 0, 0.00, 'Planar', 8, 'In Stock', '2025-07-11 12:36:54'),
(104, 'MSI PRO MP223 E2 21.4\" 1920 x 1080 100 Hz Monitor', 'Monitor', 14000.00, 'uploads/1752236979_060e222c360e17b4ecad950111331052.256p.jpg', 'MSI PRO MP223 E2 is a 21.4-inch Full HD (1920 × 1080) monitor with a smooth 100 Hz refresh rate, offering crisp visuals and responsive performance—ideal for productivity and everyday computing.', 0, 0.00, 'MSI', 8, 'In Stock', '2025-07-11 12:29:39'),
(105, 'MSI GT 710 2GD3H LP GeForce GT 710 2 GB Video Card', 'Video Card', 20000.00, 'uploads/1752238339_a01ec3b624bf22abe8f1831194c4d226.256p.jpg', 'MSI GT 710 2GD3H LP is a low-profile 2GB graphics card ideal for basic tasks like HD video playback, office use, and light multimedia, featuring silent passive cooling and support for up to three displays.', 0, 0.00, 'MSI', 6, 'In Stock', '2025-07-11 12:52:19'),
(106, 'Zotac ZT-P10300A-10L GeForce GT 1030 2 GB Video Card', 'Video Card', 30000.00, 'uploads/1752238517_2bb607fb81a1937856469da540ad5c82.256p.jpg', 'Zotac ZT‑P10300A‑10L GeForce GT 1030 is a compact, energy-efficient 2 GB graphics card ideal for smooth HD video playback, light gaming, and multi-display setups, featuring passive cooling for silent operation.', 0, 0.00, 'Zotac', 7, 'In Stock', '2025-07-08 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `storage`
--

CREATE TABLE `storage` (
  `product_id` int(11) NOT NULL,
  `storage_type` enum('HDD','SSD','NVMe') NOT NULL,
  `capacity` varchar(50) DEFAULT NULL,
  `interface` varchar(50) DEFAULT NULL,
  `form_factor` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storage`
--

INSERT INTO `storage` (`product_id`, `storage_type`, `capacity`, `interface`, `form_factor`) VALUES
(42, 'SSD', '1 TB', 'SATA 6Gb/s', '2.5\"'),
(43, 'NVMe', '2 TB', 'PCIe 4.0 x4', 'M.2-2280'),
(44, 'NVMe', '8 TB', 'PCIe 4.0 x4', 'M.2-2280'),
(45, 'NVMe', '2 TB', 'PCIe 4.0 x4', 'M.2-2280'),
(46, 'NVMe', '2 TB', 'PCIe 4.0 x4', 'M.2-2280'),
(47, 'NVMe', '2 TB', 'PCIe 4.0 x4', 'M.2-2280'),
(92, 'SSD', '128 GB', 'SATA', '2.5\"');

-- --------------------------------------------------------

--
-- Table structure for table `technician`
--

CREATE TABLE `technician` (
  `technician_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `proof` varchar(255) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `experience` int(11) NOT NULL,
  `charge_per_day` decimal(10,2) DEFAULT NULL,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technician`
--

INSERT INTO `technician` (`technician_id`, `user_id`, `proof`, `specialization`, `experience`, `charge_per_day`, `status`) VALUES
(3, 30, '686804a510434_Guidelines to write a project progress report.pdf', 'Gaming PCs', 2, 2000.00, 'available'),
(4, 31, '6868af4b8f54d_a1377d3698eff001.pdf', 'Workstations', 10, 0.00, 'available'),
(5, 33, '6876665616021_ESD 111-1 Com Skill Assesment 2022.pdf', 'Custom Water Cooling', 3, 2000.00, 'available'),
(6, 34, '6876696f9be53_ESD 111-1 Com Skill Assesment 2022.pdf', 'Gaming PCs', 3, NULL, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `technician_assignments`
--

CREATE TABLE `technician_assignments` (
  `assignment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technician_assignments`
--

INSERT INTO `technician_assignments` (`assignment_id`, `customer_id`, `technician_id`, `assigned_at`, `status`, `instructions`) VALUES
(14, 29, 3, '2025-07-15 06:03:18', 'rejected', ''),
(15, 29, 3, '2025-07-15 06:24:27', 'accepted', 'hi'),
(16, 29, 3, '2025-07-15 07:57:30', 'accepted', 'hi madhan'),
(17, 29, 3, '2025-07-15 08:13:31', 'accepted', 'i need to your help for pc building'),
(18, 29, 3, '2025-07-15 08:15:53', 'rejected', 'hgftyf'),
(19, 29, 3, '2025-07-15 08:19:56', 'accepted', 'uhiuwea'),
(20, 29, 3, '2025-07-15 09:44:06', 'pending', ''),
(21, 29, 3, '2025-07-15 09:48:08', 'pending', ''),
(22, 29, 3, '2025-07-15 10:05:58', 'pending', 'gdchdg'),
(23, 29, 5, '2025-07-15 14:34:33', 'accepted', 'i need to quickly'),
(24, 29, 3, '2025-07-16 02:23:26', 'accepted', 'hjkl');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'customer',
  `profile_image` varchar(100) NOT NULL DEFAULT 'user_image.jpg',
  `disable_status` enum('active','disabled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `contact_number`, `address`, `user_type`, `profile_image`, `disable_status`, `created_at`) VALUES
(27, 'Seller Name', 'seller@gmail.com', '$2y$10$cynhWwNxXqV7Vicbe3KUoOVkdEsZpgUh6ogszFXAKE3JvUv2s3ECy', '0712345678', 'Colombo', 'seller', 'seller_27_1751986885.png', 'active', '2025-07-01 12:00:14'),
(28, 'admin', 'admin@gmail.com', '$2y$10$yIkI2M1cvRFelSCQ1/6hQe4tfYqIJwL7cMh.hL9tlQASet96hp/kO', '0704079541', 'Jaffna', 'admin', 'admin_28_1751989907.jpg', 'active', '2025-07-01 12:00:14'),
(29, 'makinthan mdn', 'mahinthan2001a@gmail.com', '$2y$10$ZLU/USlRx4ug9jwLpfp5J.U2UqIUNSnvHiTGjQESMwF0zL8xU3f5.', '0704079547', 'jaffna | Jaffna | 40000', 'customer', 'pp2.png', 'active', '2025-07-04 08:20:26'),
(30, 'madhan  mdn', 'madhan2001ana@gmail.com', '$2y$10$xJWZjjUaSlWshHLd.E2CqeLSZkugnGU4PkHo4Yb.e4EBv6LXrViSS', '0704079547', 'Velanai | Jaffna | 40000', 'Technician', 'img_6874eca24587b4.92343666.jpg', 'active', '2025-07-04 16:43:17'),
(31, 'Pukaliny Rajee', 'ypukaliny@gmail.com', '$2y$10$PYpIwRwiZQvXabqekQmwv.p74GGnXlMSm35Aw2qUkzYo9jBJnOQuW', '0774455666', 'pointpedro | Jaffna | 40000', 'Technician', 'img_686d3f1107a875.61902098.jpg', 'active', '2025-07-05 04:51:23'),
(32, 'Kowsika kantharuban', 'kantharubankowsika@gmail.com', '$2y$10$ic9jys8F9qbXPTmGEBFhHOv5wEeY1kTRmH49KappZAivvKqwRl4MK', '0775566777', 'udaiyarkaddu | Mullaitivu | 45000', 'customer', 'pp19.png', 'active', '2025-07-05 04:53:28'),
(33, 'suman raj', 'suthasuman20@gmail.com', '$2y$10$2u6DJnlHgoRlJ37YyExXF.w7Z0QA8I5RT68sz2kV.GSpMGQDnUBfG', '0704079588', 'velanai | Jaffna | 40000', 'Technician', 'img_6876669b200f99.38199932.jpg', 'active', '2025-07-15 14:31:50'),
(34, 'demario benet', 'demariobennet1@gmail.com', '$2y$10$4FIHbfJeQnQlKv/QPy9tNeQs0S7RMhkoCl3p8l8Qogsd0zjN0XSg2', '0704079445', 'velanai | Badulla | 40000', 'Technician', 'user_image.jpg', 'active', '2025-07-15 14:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `video_card`
--

CREATE TABLE `video_card` (
  `product_id` int(11) NOT NULL,
  `chipset` varchar(100) DEFAULT NULL,
  `memory` varchar(50) DEFAULT NULL,
  `memory_type` varchar(50) DEFAULT NULL,
  `core_clock` varchar(50) DEFAULT NULL,
  `boost_clock` varchar(50) DEFAULT NULL,
  `interface` varchar(50) DEFAULT NULL,
  `length` varchar(50) DEFAULT NULL,
  `tdp` varchar(50) DEFAULT NULL,
  `cooling` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `video_card`
--

INSERT INTO `video_card` (`product_id`, `chipset`, `memory`, `memory_type`, `core_clock`, `boost_clock`, `interface`, `length`, `tdp`, `cooling`) VALUES
(56, 'GeForce RTX 4090', '24 GB', 'GDDR6X', '2235 MHz', '2640 MHz', 'PCIe x16', '358 mm', '450 W', '3 Fans'),
(57, 'GeForce RTX 4090', '24 GB', 'GDDR6X', '2135 MHz', '2440 MHz', 'PCIe x16', '328 mm', '350 W', '3 Fans'),
(58, 'GeForce RTX 5070 Ti', '16 GB', 'GDDR7', '2310 MHz', '2610 MHz', 'PCIe 5.0', '320 mm', '285W', 'Triple-Fan'),
(59, 'Radeon RX 9070 XT', '16 GB', 'GDDR6', '2010 MHz', '2540 MHz', 'PCIe 5.0', '310 mm', '300W', 'Triple-Fan'),
(60, 'GeForce RTX 5090', '32 GB', 'GDDR7', '2235 MHz', '2520 MHz', 'PCIe 5.0', '356 mm', '450W', 'Quad-Fan'),
(61, 'Radeon RX 9070 XT', '16 GB', 'GDDR6', '2010 MHz', '2560 MHz', 'PCIe 5.0', '320 mm', '300W', 'Triple-Fan'),
(93, 'GeForce RTX 3050 6GB', '6 GB', 'GDDR6', '1040 MHz', '1492 MHz', 'PCIe x16', '189 mm', '70 W', '2 Fans'),
(105, 'GeForce GT 710', '2 GB', 'GDDR6', '954 MHz', '953 MHz', 'PCIe 3.0', '146 mm', '19W', '2 Fans'),
(106, 'GeForce GT 1030', '2 GB', 'GDDR6', '1227 MHz', '1466 MHz', 'Other', '172 mm', '30W', '2 Fans');

-- --------------------------------------------------------

--
-- Structure for view `inventory_summary`
--
DROP TABLE IF EXISTS `inventory_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `inventory_summary`  AS SELECT count(0) AS `total_products`, sum(`products`.`stock`) AS `total_stock`, sum(`products`.`stock` * `products`.`price`) AS `total_value`, count(case when `products`.`stock` = 0 then 1 end) AS `out_of_stock`, count(case when `products`.`stock` <= 5 and `products`.`stock` > 0 then 1 end) AS `low_stock` FROM `products` ;

-- --------------------------------------------------------

--
-- Structure for view `low_stock_products`
--
DROP TABLE IF EXISTS `low_stock_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `low_stock_products`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`name` AS `name`, `p`.`category` AS `category`, `p`.`stock` AS `stock`, 5 AS `min_stock`, `p`.`status` AS `status`, `p`.`last_restock_date` AS `last_restock_date` FROM `products` AS `p` WHERE `p`.`stock` <= 5 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `cpu`
--
ALTER TABLE `cpu`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `cpu_cooler`
--
ALTER TABLE `cpu_cooler`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `memory`
--
ALTER TABLE `memory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `monitor`
--
ALTER TABLE `monitor`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `motherboard`
--
ALTER TABLE `motherboard`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `operating_system`
--
ALTER TABLE `operating_system`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_orders_assignment` (`assignment_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pc_case`
--
ALTER TABLE `pc_case`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `power_supply`
--
ALTER TABLE `power_supply`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `storage`
--
ALTER TABLE `storage`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `technician`
--
ALTER TABLE `technician`
  ADD PRIMARY KEY (`technician_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `technician_assignments`
--
ALTER TABLE `technician_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `video_card`
--
ALTER TABLE `video_card`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `technician`
--
ALTER TABLE `technician`
  MODIFY `technician_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `technician_assignments`
--
ALTER TABLE `technician_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `cpu`
--
ALTER TABLE `cpu`
  ADD CONSTRAINT `cpu_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `cpu_cooler`
--
ALTER TABLE `cpu_cooler`
  ADD CONSTRAINT `cpu_cooler_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `memory`
--
ALTER TABLE `memory`
  ADD CONSTRAINT `memory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `monitor`
--
ALTER TABLE `monitor`
  ADD CONSTRAINT `monitor_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `motherboard`
--
ALTER TABLE `motherboard`
  ADD CONSTRAINT `motherboard_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `operating_system`
--
ALTER TABLE `operating_system`
  ADD CONSTRAINT `operating_system_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `technician_assignments` (`assignment_id`),
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `pc_case`
--
ALTER TABLE `pc_case`
  ADD CONSTRAINT `pc_case_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `power_supply`
--
ALTER TABLE `power_supply`
  ADD CONSTRAINT `power_supply_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `storage`
--
ALTER TABLE `storage`
  ADD CONSTRAINT `storage_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `technician`
--
ALTER TABLE `technician`
  ADD CONSTRAINT `technician_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `technician_assignments`
--
ALTER TABLE `technician_assignments`
  ADD CONSTRAINT `technician_assignments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `technician_assignments_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `technician` (`technician_id`) ON DELETE CASCADE;

--
-- Constraints for table `video_card`
--
ALTER TABLE `video_card`
  ADD CONSTRAINT `video_card_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
