-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql206.infinityfree.com
-- Generation Time: Jun 28, 2025 at 01:42 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_38952671_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cartID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `datacreated` datetime DEFAULT current_timestamp(),
  `lastupdated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `discountamount` decimal(10,2) DEFAULT 0.00,
  `taxamount` decimal(10,2) DEFAULT 0.00,
  `shippingestimate` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cartID`, `CustomerID`, `productID`, `quantity`, `datacreated`, `lastupdated`, `subtotal`, `discountamount`, `taxamount`, `shippingestimate`) VALUES
(116, 4, 13, 1, '2025-06-24 11:12:21', '2025-06-24 11:12:21', '0.00', '0.00', '0.00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `cartitem`
--

CREATE TABLE `cartitem` (
  `cartitemID` int(11) NOT NULL,
  `cartID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `dateadded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `categoryID` int(11) NOT NULL,
  `Description` text DEFAULT NULL,
  `displayOrder` int(11) DEFAULT NULL,
  `categoryname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`categoryID`, `Description`, `displayOrder`, `categoryname`) VALUES
(6, 'Fabrics and textiles used for stitching custom outfits, available in various prints, weaves, and textures.', 0, 'Dress material'),
(7, 'Traditional South Asian attire consisting of a tunic (kameez), loose trousers (shalwar), and a dupatta (scarf)', 0, 'Shalwar Suit'),
(8, 'Elegant draped garments for women, featuring a long unstitched fabric paired with a blouse and petticoat.', 0, 'Sarees'),
(9, 'Flared skirts paired with a blouse and dupatta, often worn for weddings and festive occasions.', 0, 'Lehengas'),
(10, 'Complementary items like belts, stoles, jewelry, and embellishments to enhance outfits.', 0, 'Garment Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  `shippingaddress` text DEFAULT NULL,
  `accountCreatedDate` datetime DEFAULT current_timestamp(),
  `Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `username`, `password`, `firstname`, `lastname`, `phonenumber`, `shippingaddress`, `accountCreatedDate`, `Email`) VALUES
(1, 'Theekshana', '$2y$10$W0BPzSYifZuepgXYDlAZSOJgWeUZq6w4ajs8VyazsNBHUjj.KF9L.', 'Theekshana', 'Thennakoon', '0789651168', NULL, '2025-06-16 12:05:59', 'theekshanathennakoon79@gmail.com'),
(2, 'thisula', '$2y$10$YaWFrn0qEFwqGOB6700yeePCruhFydbKHuJPBnMn3vcq.Gy05UuVi', 'Thisula', 'Prabashwara', '0705040775', 'Medaoda Rd Dampelessa, Narammala', '2025-06-22 12:36:20', 'thisuladevelopment@gmail.com'),
(3, 'Achira', '$2y$10$IQOmcKmkTRIOt99aA8tIpOzM7CCjg.1b6Ax7aliK/og4VJcPvL2L.', 'Achira', 'Pamuditha', NULL, NULL, '2025-06-22 22:05:21', 'pamudithaachira@gmail.com'),
(4, 'danushayan', '$2y$10$aURckIGJCeLd0xhAHz4hHu1npXvQ/iLB7ZjRG1VPe73ksHiIOka2C', 'danush', 'elangovan', NULL, NULL, '2025-06-22 22:05:35', 'danushyanelangovan@gmail.com'),
(5, 'testuser', '$2y$10$vrTv5.jn88oOcS4KvumplOnStrkvMi9eL2mmHfu7SkQkmOeD9JdiO', 'Test', 'User', NULL, NULL, '2025-06-23 05:52:54', 'itt2021106@tec.rjt.ac.lk'),
(7, 'testadmin', '$2y$10$ekB5ANteUj.OPA0BqgDUZ.fLmfg0.aCzmlGTXig2IrtfbuWtzdN7C', 'Test', 'CEO', NULL, NULL, '2025-06-23 10:42:37', 'itt2021107@tec.rjt.ac.lk');

-- --------------------------------------------------------

--
-- Table structure for table `customersupport`
--

CREATE TABLE `customersupport` (
  `supportID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `supportType` enum('general','order','return','technical','payment') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `resolution` text DEFAULT NULL,
  `customerSatisfactionRating` int(11) DEFAULT NULL,
  PRIMARY KEY (`supportID`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `deliveryitem`
--

CREATE TABLE `deliveryitem` (
  `deliveryItemID` int(11) NOT NULL,
  `deliveryID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL,
  `subTotal` decimal(10,2) NOT NULL,
  `recivedQuantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','received','partial','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financialtransaction`
--

CREATE TABLE `financialtransaction` (
  `transactionID` int(11) NOT NULL,
  `orderID` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transactionDate` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `paymentMethod` enum('credit_card','paypal','bank_transfer','cod','refund') NOT NULL,
  `transactionType` enum('sale','refund','expense','income') NOT NULL,
  `taxamount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventoryID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantityINstock` int(11) NOT NULL DEFAULT 0,
  `lastupdated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location` varchar(100) DEFAULT NULL,
  `batchnumber` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','recalled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventoryID`, `productID`, `quantityINstock`, `lastupdated`, `location`, `batchnumber`, `status`) VALUES
(12, 12, 15, '2025-06-22 21:55:44', 'wattala', NULL, 'active'),
(13, 13, 5, '2025-06-23 12:39:03', 'wattala', '', 'active'),
(17, 17, 20, '2025-06-24 02:24:35', 'Sri Lanka', NULL, 'active'),
(19, 19, 10, '2025-06-24 04:30:33', 'wattala', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `inventorytransaction`
--

CREATE TABLE `inventorytransaction` (
  `transactionID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `transactiontype` enum('purchase','sale','return','adjustment','transfer') NOT NULL,
  `quantity` int(11) NOT NULL,
  `transactiondate` datetime DEFAULT current_timestamp(),
  `previousquantity` int(11) NOT NULL,
  `newquantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `userID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventorytransaction`
--

INSERT INTO `inventorytransaction` (`transactionID`, `productID`, `transactiontype`, `quantity`, `transactiondate`, `previousquantity`, `newquantity`, `notes`, `userID`) VALUES
(12, 12, 'purchase', 15, '2025-06-22 21:55:44', 0, 15, NULL, 1),
(13, 13, 'purchase', 5, '2025-06-22 21:57:26', 0, 5, NULL, 1),
(14, 13, 'adjustment', 2, '2025-06-23 12:39:03', 3, 5, '', 8),
(18, 17, 'purchase', 20, '2025-06-24 02:24:35', 0, 20, NULL, 1),
(20, 19, 'purchase', 10, '2025-06-24 04:30:33', 0, 10, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `orderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `orderdate` datetime DEFAULT current_timestamp(),
  `shippingaddress` text NOT NULL,
  `paymentmethod` enum('credit_card','paypal','bank_transfer','cod') NOT NULL,
  `totalamount` decimal(10,2) NOT NULL,
  `Status` enum('pending','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  `statusLastUpdateDate` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trackingNumber` varchar(50) DEFAULT NULL,
  `deliveryDate` datetime DEFAULT NULL,
  `promoCodeApplied` varchar(20) DEFAULT NULL,
  `Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderitem`
--

CREATE TABLE `orderitem` (
  `orderitemID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unitprice` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `orderDate` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `totalAmount` decimal(10,2) NOT NULL,
  `taxAmount` decimal(10,2) NOT NULL,
  `shippingAmount` decimal(10,2) NOT NULL,
  `discountAmount` decimal(10,2) DEFAULT 0.00,
  `paymentMethod` varchar(50) NOT NULL,
  `shippingAddress` text NOT NULL,
  `billingAddress` text NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `CustomerID`, `orderDate`, `status`, `totalAmount`, `taxAmount`, `shippingAmount`, `discountAmount`, `paymentMethod`, `shippingAddress`, `billingAddress`, `notes`) VALUES
(1, 1, '2025-06-23 08:52:11', 'shipped', '46000.00', '0.00', '0.00', '0.00', 'Credit Card', 'Kasiwaththa ,Kiwulgalla , Metiyagane', '', ''),
(2, 1, '2025-06-23 14:42:45', 'shipped', '15000.00', '0.00', '0.00', '0.00', 'Credit Card', 'Kasiwaththa ,Kiwulgalla , Metiyagane', '', ''),
(3, 1, '2025-06-24 02:29:05', 'Processing', '46900.00', '0.00', '0.00', '0.00', 'Credit Card', 'Metiyagane, Kurunegala, Sri Lanka', '', ''),
(4, 4, '2025-06-24 11:08:16', 'Processing', '21000.00', '0.00', '0.00', '0.00', 'Cash on Delivery', '192/A shanthi road, wattala', '', ''),
(5, 1, '2025-06-25 11:43:25', 'Processing', '21000.00', '0.00', '0.00', '0.00', 'Credit Card', 'Thisula Development,\r\nNegombo Road Narammala', '', ''),
(6, 1, '2025-06-25 11:43:59', 'Processing', '21000.00', '0.00', '0.00', '0.00', 'Credit Card', 'Thisula Development,\r\nNegombo Road Narammala', '', ''),
(7, 1, '2025-06-25 12:03:15', 'Processing', '30000.00', '0.00', '0.00', '0.00', 'Credit Card', 'Kasiwaththa ,Kiwulgalla , Metiyagane', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `orderItemID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`orderItemID`, `orderID`, `productID`, `quantity`, `unitPrice`) VALUES
(4, 0, 13, 2, '23000.00'),
(3, 0, 12, 2, '15000.00'),
(5, 0, 12, 2, '15000.00'),
(6, 0, 13, 1, '23000.00'),
(7, 2, 12, 1, '15000.00'),
(8, 3, 17, 3, '300.00'),
(9, 3, 13, 2, '23000.00'),
(10, 4, 19, 1, '21000.00'),
(11, 5, 19, 1, '21000.00'),
(12, 6, 19, 1, '21000.00'),
(13, 7, 12, 2, '15000.00');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `paymentID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `paymentDate` datetime DEFAULT current_timestamp(),
  `paymentmethod` enum('credit_card','paypal','bank_transfer','cod') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `refundAmount` decimal(10,2) DEFAULT 0.00,
  `refundDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `productID` int(11) NOT NULL,
  `Description` text DEFAULT NULL,
  `unitprice` decimal(10,2) NOT NULL,
  `quantityINstock` int(11) NOT NULL DEFAULT 0,
  `supplierid` int(11) DEFAULT NULL,
  `dataAdded` datetime DEFAULT current_timestamp(),
  `productname` varchar(100) NOT NULL,
  `addedBY` int(11) DEFAULT NULL,
  `imageURl` varchar(255) DEFAULT NULL,
  `imageURl2` varchar(255) NOT NULL,
  `imageURl3` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `categoryID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`productID`, `Description`, `unitprice`, `quantityINstock`, `supplierid`, `dataAdded`, `productname`, `addedBY`, `imageURl`, `imageURl2`, `imageURl3`, `location`, `categoryID`) VALUES
(12, 'Rani Pink Saree in Georgette fabric The Saree is elevated with Mirrorwork embroidery It comes with an Unstitched blouse Comes with the Koskii promise of premium quality.', '15000.00', 8, NULL, '2025-06-22 21:55:44', 'Rani Pink Mirrorwork Georgette Saree', 1, 'SAUS0039105_RANI_PINK_2.JPEG', '', '', 'wattala', 8),
(13, 'Dark Maroon Saree in Tissue fabric. The Saree is elevated with Prints . It comes with an Unstitched blouse. Comes with the Koskii promise of premium quality.', '23000.00', 2, NULL, '2025-06-22 21:57:26', 'Dark Maroon Zariwork Tissue Saree', 1, 'koskii-darkmaroon-zariwork-tissue-designer-saree-saus0041302_darkmaroon_1_5.JPEG', '', '', 'wattala', 8),
(17, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '300.00', 17, NULL, '2025-06-24 02:24:35', 'Bobbin', 1, 'green.jpg', 'blue.jpg', 'green.jpg', 'Sri Lanka', 6),
(19, 'Drape yourself in the luxurious folds of Koskii\'s Olive Green Organza Saree, where traditional craftsmanship meets contemporary flair! This exquisite saree sparkles with vibrant mirrorwork embroidery that dances playfully across the sheer, whisper-light organza, creating an allure that\'s impossible to resist. It\'s a celebration of grace and a testament to the spirited elegance that Koskii brings to every piece. Perfect for those who love to express their style with confidence and vivacity!.', '21000.00', 7, NULL, '2025-06-24 04:30:33', 'Mehendi Mirrorwork Tissue Saree', 1, 'koskii-mehendi-mirrorwork-georgette-designer-saree-saus0039105_mehendi_1_4.JPEG', 'koskii-mehendi-mirrorwork-georgette-designer-saree-saus0039105_mehendi_1_6.jpeg', 'koskii-mehendi-mirrorwork-georgette-designer-saree-saus0039105_mehendi_1_9.jpeg', 'wattala', 8);

-- --------------------------------------------------------

--
-- Table structure for table `productreview`
--

CREATE TABLE `productreview` (
  `reviewID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `approvedby` int(11) DEFAULT NULL,
  `reviewDate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reviewID`)
) ;

--
-- Dumping data for table `productreview`
--

INSERT INTO `productreview` (`reviewID`, `productID`, `CustomerID`, `rating`, `comment`, `status`, `approvedby`, `reviewDate`) VALUES
(6, 12, 2, 5, 'Good product', 'approved', NULL, '2025-06-24 02:10:33');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `reportID` int(11) NOT NULL,
  `reportType` enum('sales','inventory','customer','financial') NOT NULL,
  `generatedDate` datetime DEFAULT current_timestamp(),
  `format` enum('pdf','csv','excel','html') DEFAULT 'pdf',
  `status` enum('pending','generated','failed') DEFAULT 'pending',
  `schedulingFrequency` enum('daily','weekly','monthly','quarterly','yearly','once') DEFAULT NULL,
  `parameters` text DEFAULT NULL,
  `generatedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return`
--

CREATE TABLE `return` (
  `returnID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `returnDate` datetime DEFAULT current_timestamp(),
  `reasonFORreturn` text NOT NULL,
  `status` enum('requested','approved','rejected','processed','refunded') DEFAULT 'requested',
  `refundAmount` decimal(10,2) DEFAULT 0.00,
  `approvalDate` datetime DEFAULT NULL,
  `returnShippingMethod` varchar(50) DEFAULT NULL,
  `returnTrackingNumber` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `qualityCheckStatus` enum('pending','passed','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returnitem`
--

CREATE TABLE `returnitem` (
  `returnitemID` int(11) NOT NULL,
  `returnID` int(11) NOT NULL,
  `orderitemID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `condition` enum('new','opened','damaged','used') NOT NULL,
  `refundAmount` decimal(10,2) NOT NULL,
  `restockStatus` enum('pending','restocked','discarded') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `staff_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('support','admin','manager') DEFAULT 'support',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `supplierid` int(11) NOT NULL,
  `companyname` varchar(100) NOT NULL,
  `Firstname` varchar(50) NOT NULL,
  `Lastname` varchar(50) NOT NULL,
  `businessRegistrationNumber` varchar(50) NOT NULL,
  `Phonenumber` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contractStartDate` date NOT NULL,
  `contractEndDate` date DEFAULT NULL,
  `paymentTerms` varchar(100) DEFAULT NULL,
  `serviceCategory` varchar(50) DEFAULT NULL,
  `accountStatusField` enum('active','inactive','suspended') DEFAULT 'active',
  `accountCreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplierdelivery`
--

CREATE TABLE `supplierdelivery` (
  `deliveryID` int(11) NOT NULL,
  `supplierid` int(11) NOT NULL,
  `deliveryDate` datetime DEFAULT NULL,
  `expectedDeliveryDate` datetime NOT NULL,
  `status` enum('pending','delivered','cancelled','partial') DEFAULT 'pending',
  `purchaseOrderNumber` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `qualityCheckPassed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `ticket_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `ticket_type` enum('general','return','warranty','account','technical') NOT NULL DEFAULT 'general',
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`ticket_id`, `customer_id`, `order_id`, `ticket_type`, `subject`, `description`, `status`, `priority`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'return', 'message delivered but it not received to my number', 'gretsyiduyrf', 'open', 'medium', '2025-06-24 01:05:14', NULL),
(2, 1, NULL, 'warranty', 'message delivered but it not received to my number', 'gretsyiduyrf', 'in_progress', 'medium', '2025-06-24 01:09:20', '2025-06-24 01:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `attachment_id` int(11) NOT NULL,
  `response_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_responses`
--

CREATE TABLE `ticket_responses` (
  `response_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('customer','staff') NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ticket_responses`
--

INSERT INTO `ticket_responses` (`response_id`, `ticket_id`, `user_id`, `user_type`, `message`, `created_at`) VALUES
(1, 1, 1, 'customer', 'gretsyiduyrf', '2025-06-24 01:05:14'),
(2, 2, 1, 'customer', 'gretsyiduyrf', '2025-06-24 01:09:20'),
(3, 2, 2, 'customer', 'fdsbs', '2025-06-24 01:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Firstname` varchar(50) NOT NULL,
  `Lastname` varchar(50) NOT NULL,
  `Role` enum('System_admin','sales_manager','inventory_manager','warehouse_staff','customer_care','CEO','customer') NOT NULL,
  `Phonenumber` varchar(20) DEFAULT NULL,
  `Lastlogin` datetime DEFAULT NULL,
  `Status` enum('active','inactive','suspended') DEFAULT 'active',
  `JobTiitle` varchar(50) DEFAULT NULL,
  `accountCreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `username`, `password`, `Email`, `Firstname`, `Lastname`, `Role`, `Phonenumber`, `Lastlogin`, `Status`, `JobTiitle`, `accountCreationDate`) VALUES
(1, 'admin', '$2a$12$qeMZ4Rg9nyeDtLqWlZ89SeFEYdFsY3wabHqj0TIkNqoERRy5ZQ0uW', 'admin@mail.com', 'Admin', 'Admin', 'System_admin', '0712345678', '2025-06-25 12:03:34', 'active', NULL, '2025-06-16 11:47:41'),
(2, 'Theekshana', '$2y$10$W0BPzSYifZuepgXYDlAZSOJgWeUZq6w4ajs8VyazsNBHUjj.KF9L.', 'theekshanathennakoon79@gmail.com', 'Theekshana', 'Thennakoon', 'customer', '0789651168', '2025-06-25 12:02:50', 'active', NULL, '2025-06-16 12:05:59'),
(4, 'Achira', '$2y$10$IQOmcKmkTRIOt99aA8tIpOzM7CCjg.1b6Ax7aliK/og4VJcPvL2L.', 'pamudithaachira@gmail.com', 'Achira', 'Pamuditha', 'customer', NULL, NULL, 'active', NULL, '2025-06-22 22:05:21'),
(5, 'danushayan', '$2y$10$aURckIGJCeLd0xhAHz4hHu1npXvQ/iLB7ZjRG1VPe73ksHiIOka2C', 'danushyanelangovan@gmail.com', 'danush', 'elangovan', 'customer', NULL, '2025-06-24 11:11:58', 'active', NULL, '2025-06-22 22:05:35'),
(8, 'testadmin', '$2y$10$ekB5ANteUj.OPA0BqgDUZ.fLmfg0.aCzmlGTXig2IrtfbuWtzdN7C', 'itt2021107@tec.rjt.ac.lk', 'Test', 'CEO', 'inventory_manager', NULL, '2025-06-23 23:47:19', 'active', NULL, '2025-06-23 10:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `userpermission`
--

CREATE TABLE `userpermission` (
  `permissionID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `ModuleAccess` varchar(50) NOT NULL,
  `accesslevel` enum('read','write','admin') NOT NULL,
  `GrantedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlistID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `datacreated` datetime DEFAULT current_timestamp(),
  `productID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlistitem`
--

CREATE TABLE `wishlistitem` (
  `wishlistitemID` int(11) NOT NULL,
  `wishlistID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `dateadded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cartID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `cartitem`
--
ALTER TABLE `cartitem`
  ADD PRIMARY KEY (`cartitemID`),
  ADD KEY `cartID` (`cartID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `deliveryitem`
--
ALTER TABLE `deliveryitem`
  ADD PRIMARY KEY (`deliveryItemID`),
  ADD KEY `deliveryID` (`deliveryID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `financialtransaction`
--
ALTER TABLE `financialtransaction`
  ADD PRIMARY KEY (`transactionID`),
  ADD KEY `orderID` (`orderID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventoryID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `inventorytransaction`
--
ALTER TABLE `inventorytransaction`
  ADD PRIMARY KEY (`transactionID`),
  ADD KEY `productID` (`productID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`orderID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`orderitemID`),
  ADD KEY `orderID` (`orderID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`orderItemID`),
  ADD KEY `orderID` (`orderID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `orderID` (`orderID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`productID`),
  ADD KEY `categoryID` (`categoryID`),
  ADD KEY `supplierid` (`supplierid`),
  ADD KEY `addedBY` (`addedBY`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `generatedBy` (`generatedBy`);

--
-- Indexes for table `return`
--
ALTER TABLE `return`
  ADD PRIMARY KEY (`returnID`),
  ADD KEY `orderID` (`orderID`);

--
-- Indexes for table `returnitem`
--
ALTER TABLE `returnitem`
  ADD PRIMARY KEY (`returnitemID`),
  ADD KEY `returnID` (`returnID`),
  ADD KEY `orderitemID` (`orderitemID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`supplierid`),
  ADD UNIQUE KEY `businessRegistrationNumber` (`businessRegistrationNumber`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `supplierdelivery`
--
ALTER TABLE `supplierdelivery`
  ADD PRIMARY KEY (`deliveryID`),
  ADD KEY `supplierid` (`supplierid`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `response_id` (`response_id`);

--
-- Indexes for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `fk_response_staff` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `userpermission`
--
ALTER TABLE `userpermission`
  ADD PRIMARY KEY (`permissionID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlistID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `wishlistitem`
--
ALTER TABLE `wishlistitem`
  ADD PRIMARY KEY (`wishlistitemID`),
  ADD KEY `wishlistID` (`wishlistID`),
  ADD KEY `productID` (`productID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `cartitem`
--
ALTER TABLE `cartitem`
  MODIFY `cartitemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customersupport`
--
ALTER TABLE `customersupport`
  MODIFY `supportID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliveryitem`
--
ALTER TABLE `deliveryitem`
  MODIFY `deliveryItemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financialtransaction`
--
ALTER TABLE `financialtransaction`
  MODIFY `transactionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `inventorytransaction`
--
ALTER TABLE `inventorytransaction`
  MODIFY `transactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderitem`
--
ALTER TABLE `orderitem`
  MODIFY `orderitemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `orderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `productreview`
--
ALTER TABLE `productreview`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `reportID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return`
--
ALTER TABLE `return`
  MODIFY `returnID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returnitem`
--
ALTER TABLE `returnitem`
  MODIFY `returnitemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplierid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplierdelivery`
--
ALTER TABLE `supplierdelivery`
  MODIFY `deliveryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `userpermission`
--
ALTER TABLE `userpermission`
  MODIFY `permissionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlistID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `wishlistitem`
--
ALTER TABLE `wishlistitem`
  MODIFY `wishlistitemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cartitem`
--
ALTER TABLE `cartitem`
  ADD CONSTRAINT `cartitem_ibfk_1` FOREIGN KEY (`cartID`) REFERENCES `cart` (`cartID`) ON DELETE CASCADE,
  ADD CONSTRAINT `cartitem_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE;

--
-- Constraints for table `deliveryitem`
--
ALTER TABLE `deliveryitem`
  ADD CONSTRAINT `deliveryitem_ibfk_1` FOREIGN KEY (`deliveryID`) REFERENCES `supplierdelivery` (`deliveryID`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveryitem_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE;

--
-- Constraints for table `financialtransaction`
--
ALTER TABLE `financialtransaction`
  ADD CONSTRAINT `financialtransaction_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `order` (`orderID`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE;

--
-- Constraints for table `inventorytransaction`
--
ALTER TABLE `inventorytransaction`
  ADD CONSTRAINT `inventorytransaction_ibfk_1` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventorytransaction_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE;

--
-- Constraints for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `orderitem_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `order` (`orderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `orderitem_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `order` (`orderID`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `category` (`categoryID`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_ibfk_3` FOREIGN KEY (`addedBY`) REFERENCES `user` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`generatedBy`) REFERENCES `user` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `return`
--
ALTER TABLE `return`
  ADD CONSTRAINT `return_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `order` (`orderID`) ON DELETE CASCADE;

--
-- Constraints for table `returnitem`
--
ALTER TABLE `returnitem`
  ADD CONSTRAINT `returnitem_ibfk_1` FOREIGN KEY (`returnID`) REFERENCES `return` (`returnID`) ON DELETE CASCADE,
  ADD CONSTRAINT `returnitem_ibfk_2` FOREIGN KEY (`orderitemID`) REFERENCES `orderitem` (`orderitemID`) ON DELETE CASCADE;

--
-- Constraints for table `supplierdelivery`
--
ALTER TABLE `supplierdelivery`
  ADD CONSTRAINT `supplierdelivery_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`) ON DELETE CASCADE;

--
-- Constraints for table `userpermission`
--
ALTER TABLE `userpermission`
  ADD CONSTRAINT `userpermission_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `wishlistitem`
--
ALTER TABLE `wishlistitem`
  ADD CONSTRAINT `wishlistitem_ibfk_1` FOREIGN KEY (`wishlistID`) REFERENCES `wishlist` (`wishlistID`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlistitem_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
