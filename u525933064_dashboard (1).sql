-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2022 at 11:59 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u525933064_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` text NOT NULL,
  `leader` text NOT NULL,
  `team` text NOT NULL,
  `accesslevel` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `action` text NOT NULL,
  `status` text NOT NULL,
  `action_by` text NOT NULL,
  `actionStart_date_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `actionEnd_date_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bol_mappingsku`
--

CREATE TABLE `bol_mappingsku` (
  `id` int(11) NOT NULL,
  `bol_sku` text CHARACTER SET utf8mb4 NOT NULL,
  `system_sku` text CHARACTER SET utf8mb4 NOT NULL,
  `history` mediumtext CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `canadaorders`
--

CREATE TABLE `canadaorders` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` date NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `canadasales`
--

CREATE TABLE `canadasales` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `date` date NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `canadasupplyorder`
--

CREATE TABLE `canadasupplyorder` (
  `id` int(11) NOT NULL,
  `supplyorderid` text NOT NULL,
  `date` date NOT NULL,
  `estimatedate` date NOT NULL,
  `status` text NOT NULL,
  `containerid` text NOT NULL,
  `supplier` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `canadasupplyorderlist`
--

CREATE TABLE `canadasupplyorderlist` (
  `id` int(11) NOT NULL,
  `supplyorderid` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `canadatemporders`
--

CREATE TABLE `canadatemporders` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` datetime NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `unit` text NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `code` text NOT NULL,
  `category` text NOT NULL,
  `maincategory` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cbm`
--

CREATE TABLE `cbm` (
  `id` int(100) NOT NULL,
  `sku` text NOT NULL,
  `englishdesc` varchar(100) NOT NULL,
  `chinesedesc` varchar(100) NOT NULL,
  `remarks` text NOT NULL,
  `pcsctn` int(11) NOT NULL,
  `length` decimal(3,2) NOT NULL,
  `width` decimal(3,2) NOT NULL,
  `height` decimal(3,2) NOT NULL,
  `ctnweight` decimal(5,3) NOT NULL,
  `supplier` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `id` int(11) NOT NULL,
  `source` text NOT NULL,
  `channel` text NOT NULL,
  `account` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` int(11) NOT NULL,
  `color` text NOT NULL,
  `code` text NOT NULL,
  `chinese` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `comboproducts`
--

CREATE TABLE `comboproducts` (
  `id` int(255) NOT NULL,
  `sku` text NOT NULL,
  `originalsku` text NOT NULL,
  `image` text NOT NULL,
  `instruction` text NOT NULL,
  `uploadedon` date NOT NULL,
  `addedby` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `csvtemplate`
--

CREATE TABLE `csvtemplate` (
  `temp_id` int(11) NOT NULL,
  `tempname` text NOT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `csvtemplatelist`
--

CREATE TABLE `csvtemplatelist` (
  `id` int(11) NOT NULL,
  `temp_id` int(11) DEFAULT NULL,
  `columntitle` text NOT NULL,
  `columnname` text NOT NULL,
  `sortorder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ebaylistings`
--

CREATE TABLE `ebaylistings` (
  `id` int(10) UNSIGNED NOT NULL,
  `Action` varchar(255) DEFAULT NULL,
  `Itemnumber` varchar(255) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Listingsite` varchar(255) DEFAULT NULL,
  `Currency` varchar(255) DEFAULT NULL,
  `Startprice` varchar(255) DEFAULT NULL,
  `BuyItNowprice` varchar(255) DEFAULT NULL,
  `Availablequantity` varchar(255) DEFAULT NULL,
  `Relationship` varchar(255) DEFAULT NULL,
  `Relationshipdetails` varchar(255) DEFAULT NULL,
  `Customlabel` varchar(255) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fbasales`
--

CREATE TABLE `fbasales` (
  `id` int(11) NOT NULL,
  `asin` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line_item_total` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `deduct` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fbastock`
--

CREATE TABLE `fbastock` (
  `id` int(11) NOT NULL,
  `channel_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `listing_sku` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `system_sku` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asin` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` smallint(6) NOT NULL,
  `history` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `germanlogs`
--

CREATE TABLE `germanlogs` (
  `id` int(100) NOT NULL,
  `date` date NOT NULL,
  `type` text NOT NULL,
  `sku` text NOT NULL,
  `changelog` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `germanorders`
--

CREATE TABLE `germanorders` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` date NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `notes` text NOT NULL,
  `cancellationreason` text NOT NULL,
  `TrackingNumber` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `germansales`
--

CREATE TABLE `germansales` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `date` date NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `germansupplyorder`
--

CREATE TABLE `germansupplyorder` (
  `id` int(11) NOT NULL,
  `supplyorderid` text NOT NULL,
  `date` date NOT NULL,
  `estimatedate` date NOT NULL,
  `status` text NOT NULL,
  `containerid` text NOT NULL,
  `supplier` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `germansupplyorderlist`
--

CREATE TABLE `germansupplyorderlist` (
  `id` int(11) NOT NULL,
  `supplyorderid` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `germantemporders`
--

CREATE TABLE `germantemporders` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` datetime NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `unit` text NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `zenstoresOrderTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `invupdatelogs`
--

CREATE TABLE `invupdatelogs` (
  `id` int(11) NOT NULL,
  `sku` text NOT NULL,
  `mappingsku` text NOT NULL,
  `orderqty` text NOT NULL,
  `orderid` int(11) NOT NULL,
  `orderdate` date NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(100) NOT NULL,
  `date` date NOT NULL,
  `type` text NOT NULL,
  `sku` text NOT NULL,
  `changelog` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lowinv`
--

CREATE TABLE `lowinv` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `details` text NOT NULL,
  `total` int(11) NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lowinvdetails`
--

CREATE TABLE `lowinvdetails` (
  `id` int(11) NOT NULL,
  `lowinvid` int(11) NOT NULL,
  `prsku` text NOT NULL,
  `location` text NOT NULL,
  `sales` int(11) NOT NULL,
  `changes` text NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lowstockdetails`
--

CREATE TABLE `lowstockdetails` (
  `id` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `lowStock` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mappingsku`
--

CREATE TABLE `mappingsku` (
  `id` int(11) NOT NULL,
  `sku` text NOT NULL,
  `map_sku` text NOT NULL,
  `source` text NOT NULL,
  `sub_source` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `netharlandorders`
--

CREATE TABLE `netharlandorders` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` date NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `notes` text NOT NULL,
  `cancellationreason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `netharlandsales`
--

CREATE TABLE `netharlandsales` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `date` date NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `netharlandtemporders`
--

CREATE TABLE `netharlandtemporders` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` datetime NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `orgSku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `unit` text NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `zenstoresOrderTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `netherlandlogs`
--

CREATE TABLE `netherlandlogs` (
  `id` int(100) NOT NULL,
  `date` date NOT NULL,
  `type` text NOT NULL,
  `sku` text NOT NULL,
  `changelog` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `status` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `FBA_merchantSKU` text NOT NULL,
  `FBA_ASIN` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` varchar(200) NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `unit` text NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `notes` text NOT NULL,
  `cancellationreason` text NOT NULL,
  `ProcessedDate` datetime NOT NULL,
  `PaymentMethod` text NOT NULL,
  `PostalService` text NOT NULL,
  `TrackingNumber` text NOT NULL,
  `TrackingStatus` int(11) NOT NULL,
  `replacement` text NOT NULL,
  `shipment_id` text NOT NULL,
  `royalmail_order_id` text NOT NULL,
  `p2go_id` text NOT NULL,
  `p2go_hash` text NOT NULL,
  `label_B64` text NOT NULL,
  `refundstatus` text NOT NULL,
  `refundAmount` decimal(10,2) NOT NULL,
  `refundby` text NOT NULL,
  `refundreason` text NOT NULL,
  `refunddate` date NOT NULL,
  `refundCusFeedback` text NOT NULL,
  `returntracking` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `orderID`, `status`, `date`, `channel`, `firstname`, `lastname`, `telephone`, `email`, `currency`, `ordertotal`, `name`, `sku`, `FBA_merchantSKU`, `FBA_ASIN`, `quantity`, `lineitemtotal`, `flags`, `shippingservice`, `shippingaddresscompany`, `shippingaddressline1`, `shippingaddressline2`, `shippingaddressline3`, `shippingaddressregion`, `shippingaddresscity`, `shippingaddresspostcode`, `shippingaddresscountry`, `shippingaddresscountrycode`, `booking`, `csvdate`, `unit`, `addedby`, `merge`, `notes`, `cancellationreason`, `ProcessedDate`, `PaymentMethod`, `PostalService`, `TrackingNumber`, `TrackingStatus`, `replacement`, `shipment_id`, `royalmail_order_id`, `p2go_id`, `p2go_hash`, `label_B64`, `refundstatus`, `refundAmount`, `refundby`, `refundreason`, `refunddate`, `refundCusFeedback`, `returntracking`) VALUES
(529579, '37489:13-08998-99975', 'Pending', '2022-08-19', 'EBAY-led_sone', 'thinushan', '', '', '0152e27a2c274f74de20@members.ebay.com', 'GBP', '45.00', 'Vintage Retro Industrielle Semi Flush Mount Ceiling Light Metall Lampenschirm[Orange,Nein]', 'CRSF100OR+PHRB1PWRPW+LSWE315OR', '', '', 1, '0.00', 'Lampshade, Merged', 'international', '', 'Wolfhagweg 1', '', '', '', 'Degersheim', '9113', 'Switzerland', 'CH', '1st Booking', '2022-08-22', 'unit2', '', '', '', '', '0000-00-00 00:00:00', '', 'UPS', '', 0, '', '', '', '', '', '', '', '0.00', '', '', '0000-00-00', '', ''),
(529580, '37489:13-08998-99975', 'Pending', '2022-08-19', 'EBAY-led_sone', 'thinushan', '', '', '0152e27a2c274f74de20@members.ebay.com', 'GBP', '28.40', 'Vintage Retro Industrielle Semi Flush Mount Ceiling Light Metall Lampenschirm[Grau,Nein]', 'CRSF100GY+PHRB1PWRPW+LSWE315GY', '', '', 1, '0.00', 'Lampshade', 'international', '', 'Wolfhagweg 1', '', '', '', 'Degersheim', '9113', 'Switzerland', 'CH', '1st Booking', '2022-08-22', 'unit2', '', '', '', '', '0000-00-00 00:00:00', '', 'UPS', '', 0, '', '', '', '', '', '', '', '0.00', '', '', '0000-00-00', '', ''),
(529581, '37446:05-09001-48097', 'Pending', '2022-08-19', 'EBAY-led_sone', 'Simone Stanghetti', '', '', '152dd22dfcc825d88a78@members.ebay.com', 'GBP', '123.91', '3 Testa Vintage Retro Industriale Metallo Soffitto Paralume Appeso Ciondolo Luci[Rustico Rosso,No]', 'ENC820', '', '', 3, '0.00', 'Lampshade', 'international', '', 'Via Giuseppe Arimondi n.20', 'IOSS: IM2760000742', '', 'RM', 'roma', '00159', 'Italy', 'IT', '1st Booking', '2022-08-22', 'unit2', '', '', '', '', '0000-00-00 00:00:00', '', 'Etrak - Delivery Group', '', 0, '', '', '', '', '', '', '', '0.00', '', '', '0000-00-00', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `picklist`
--

CREATE TABLE `picklist` (
  `id` int(11) NOT NULL,
  `booking_info` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `jsonData` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `SKU` text NOT NULL,
  `mappingsku` text NOT NULL,
  `deleteSKU` text DEFAULT NULL,
  `ListingType` text NOT NULL,
  `ParentSKU` text NOT NULL,
  `IsVariationGroup` text NOT NULL,
  `VariationGroupName` text NOT NULL,
  `Type` text NOT NULL,
  `ProductName` text NOT NULL,
  `ProductType` text NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `ProductDescription` mediumtext NOT NULL,
  `ProductDescriptionLink` varchar(100) NOT NULL,
  `Tags` mediumtext DEFAULT NULL,
  `metadata` mediumtext NOT NULL,
  `Price` decimal(11,2) NOT NULL,
  `location` text NOT NULL,
  `germanInventory` int(10) DEFAULT NULL,
  `france` int(11) DEFAULT NULL,
  `netherland` int(11) DEFAULT NULL,
  `canada` int(11) DEFAULT NULL,
  `canadalocation` text NOT NULL,
  `Quantity` int(10) DEFAULT NULL,
  `outofstock` text NOT NULL,
  `unit2` int(11) NOT NULL,
  `unit1` int(11) DEFAULT NULL,
  `Mainimage` text DEFAULT NULL,
  `Subimages` text DEFAULT NULL,
  `Image1` text NOT NULL,
  `Image2` text NOT NULL,
  `Image3` text NOT NULL,
  `Image4` text NOT NULL,
  `Image5` text NOT NULL,
  `Image6` text NOT NULL,
  `Image7` text NOT NULL,
  `Image8` text NOT NULL,
  `Image9` text NOT NULL,
  `Image10` text NOT NULL,
  `SupplierImage` text NOT NULL,
  `ImageURL` varchar(255) NOT NULL,
  `ProductTypeAttributee` text NOT NULL,
  `Brand` text NOT NULL,
  `LightDirection` text NOT NULL,
  `Colour` text NOT NULL,
  `Features` text NOT NULL,
  `BulletPoint1` text NOT NULL,
  `BulletPoint2` text NOT NULL,
  `BulletPoint3` text NOT NULL,
  `BulletPoint4` text NOT NULL,
  `Searchterms` mediumtext NOT NULL,
  `Size` text NOT NULL,
  `Length` text NOT NULL,
  `SensorType` text NOT NULL,
  `IPRating` text NOT NULL,
  `NumberOfLights` text NOT NULL,
  `Voltage` text NOT NULL,
  `Finish` text NOT NULL,
  `AssemblyRequired` text NOT NULL,
  `Occassion` text NOT NULL,
  `Material` text NOT NULL,
  `PowerSource` text NOT NULL,
  `ItemWeight` text NOT NULL,
  `Theme` text NOT NULL,
  `Style` text NOT NULL,
  `InstallationArea` text NOT NULL,
  `LabelsAndCertifications` text NOT NULL,
  `Room` text NOT NULL,
  `EnergyEfficiencyRating` text NOT NULL,
  `LightingTechnology` text NOT NULL,
  `ShadeShape` text NOT NULL,
  `RequiredTools` text NOT NULL,
  `Manufacturer` text NOT NULL,
  `LightColor` text NOT NULL,
  `Department` text NOT NULL,
  `ControlStyle` text NOT NULL,
  `Mounting` text NOT NULL,
  `TopToBottom_Height` text NOT NULL,
  `overAllHeight` text NOT NULL,
  `SideToSide_Width` text NOT NULL,
  `FrontToBack_Depth` text NOT NULL,
  `BulbBase` text NOT NULL,
  `CableLength` text NOT NULL,
  `PowerConsumption` text NOT NULL,
  `ManufacturerWarranty` text NOT NULL,
  `Era` text NOT NULL,
  `ECRange` text NOT NULL,
  `Amperage` text NOT NULL,
  `Wattage` text NOT NULL,
  `CurrentType` text NOT NULL,
  `Lumens` text NOT NULL,
  `BulbWattage` text NOT NULL,
  `BulbLifeHours` text NOT NULL,
  `ColourTemperature` text NOT NULL,
  `Connectors` text NOT NULL,
  `Insulation` text NOT NULL,
  `Maxloadweight` text NOT NULL,
  `NumberOfOutput` text NOT NULL,
  `InputAmperage` text NOT NULL,
  `OutputAmperage` text NOT NULL,
  `CableCore` text NOT NULL,
  `Outlettype` text NOT NULL,
  `InputVoltage` text NOT NULL,
  `OutputVoltage` text NOT NULL,
  `RequiredBulb` text NOT NULL,
  `NumberInPack` text NOT NULL,
  `HolderType` text NOT NULL,
  `CeilingRoseHeight` text NOT NULL,
  `CeilingRoseWidth` text NOT NULL,
  `CeilingRoseDepth` text NOT NULL,
  `ShadeHeight` text NOT NULL,
  `ShadeWidth` text NOT NULL,
  `ShadeDepth` text NOT NULL,
  `AdjustableCableHangingLength` text NOT NULL,
  `CableMaxHeight` text NOT NULL,
  `CableMinHeight` text NOT NULL,
  `MaxExtensionLength` text NOT NULL,
  `AdjustableExtension` text NOT NULL,
  `OpenableChainHeight` text NOT NULL,
  `SpecialFeatures` text NOT NULL,
  `ShadeColour` text NOT NULL,
  `ShadeMaterial` text NOT NULL,
  `CableWiringMaterial` text NOT NULL,
  `ChainFullLength` text NOT NULL,
  `OpenableChainWidth` text NOT NULL,
  `PipeLength` text NOT NULL,
  `linnwork` text NOT NULL,
  `type_id` text NOT NULL,
  `uploadedon` datetime DEFAULT NULL,
  `addedby` text DEFAULT NULL,
  `history` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `team` text NOT NULL,
  `project` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `orderID` text NOT NULL,
  `date` date NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sales_selro`
--

CREATE TABLE `sales_selro` (
  `id` int(11) NOT NULL,
  `details` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `selro_file`
--

CREATE TABLE `selro_file` (
  `Id` int(11) NOT NULL,
  `selro_product_id` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `selro_sku` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `selro_image` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `system_sku` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `system_image` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_type` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `wrong_mapped` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `auto_corrected` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `history` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shippinginfo`
--

CREATE TABLE `shippinginfo` (
  `id` int(11) NOT NULL,
  `channel` text NOT NULL,
  `sku` text NOT NULL,
  `shipping service` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `skuchangelog`
--

CREATE TABLE `skuchangelog` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sku` text NOT NULL,
  `correctsku` text NOT NULL,
  `times` int(11) NOT NULL,
  `logs` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `staffleave`
--

CREATE TABLE `staffleave` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `leavedate` date NOT NULL,
  `leavetype` text NOT NULL,
  `leavedays` decimal(11,1) NOT NULL,
  `reason` text NOT NULL,
  `applydate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stocktransfer`
--

CREATE TABLE `stocktransfer` (
  `id` int(50) NOT NULL,
  `stockfrom` text NOT NULL,
  `stockto` text NOT NULL,
  `status` tinytext NOT NULL DEFAULT 'pending',
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stocktransferlist`
--

CREATE TABLE `stocktransferlist` (
  `id` int(50) NOT NULL,
  `stocktransferid` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(100) NOT NULL,
  `supplier` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `supplyorder`
--

CREATE TABLE `supplyorder` (
  `id` int(11) NOT NULL,
  `supplyorderid` text NOT NULL,
  `date` date NOT NULL,
  `estimatedate` date NOT NULL,
  `status` text NOT NULL,
  `containerid` text NOT NULL,
  `supplier` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `supplyorderlist`
--

CREATE TABLE `supplyorderlist` (
  `id` int(11) NOT NULL,
  `supplyorderid` text NOT NULL,
  `sku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `originalquantity` int(11) DEFAULT NULL,
  `remarks` text NOT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_employee`
--

CREATE TABLE `tbl_employee` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `gender` varchar(10) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `team`) VALUES
(1, 'Amazon'),
(2, 'Ebay');

-- --------------------------------------------------------

--
-- Table structure for table `temporders`
--

CREATE TABLE `temporders` (
  `id` int(11) NOT NULL,
  `image_from_ship` text NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` datetime NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `orgSku` text NOT NULL,
  `FBA_merchantSKU` text NOT NULL,
  `FBA_ASIN` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` varchar(200) NOT NULL,
  `subflags` varchar(200) NOT NULL,
  `box_sizes` text NOT NULL,
  `shippingservice` varchar(200) NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `postal_service` varchar(300) NOT NULL,
  `shipment_id` text NOT NULL,
  `tracking_No` text NOT NULL,
  `label_B64` mediumtext NOT NULL,
  `invoice_B64` mediumtext NOT NULL,
  `weight_In_Grams` int(11) NOT NULL,
  `item_height` varchar(50) NOT NULL,
  `item_length` varchar(50) NOT NULL,
  `item_width` varchar(50) NOT NULL,
  `royalmail_order_id` text NOT NULL,
  `p2go_id` text NOT NULL,
  `p2go_hash` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `unit` text NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `zenstoresOrderTotal` decimal(10,2) NOT NULL,
  `remarks` text NOT NULL,
  `shippedStatus` text NOT NULL,
  `trackingStatus` text NOT NULL,
  `notes` text NOT NULL,
  `orderItemId` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `temporders`
--

INSERT INTO `temporders` (`id`, `image_from_ship`, `orderID`, `status`, `date`, `channel`, `firstname`, `lastname`, `telephone`, `email`, `currency`, `ordertotal`, `name`, `sku`, `orgSku`, `FBA_merchantSKU`, `FBA_ASIN`, `quantity`, `lineitemtotal`, `flags`, `subflags`, `box_sizes`, `shippingservice`, `shippingaddresscompany`, `shippingaddressline1`, `shippingaddressline2`, `shippingaddressline3`, `shippingaddressregion`, `shippingaddresscity`, `shippingaddresspostcode`, `shippingaddresscountry`, `shippingaddresscountrycode`, `shipping_cost`, `postal_service`, `shipment_id`, `tracking_No`, `label_B64`, `invoice_B64`, `weight_In_Grams`, `item_height`, `item_length`, `item_width`, `royalmail_order_id`, `p2go_id`, `p2go_hash`, `booking`, `csvdate`, `unit`, `addedby`, `merge`, `total`, `zenstoresOrderTotal`, `remarks`, `shippedStatus`, `trackingStatus`, `notes`, `orderItemId`) VALUES
(62602, 'https://s3-us-west-2.amazonaws.com/selroclientsprod1/8357/15328_1ddf956a-6847-488a-9d07-29ef29f153460.jpg', '37446:05-09001-48097', 'Pending', '2022-08-19 07:39:32', 'EBAY-led_sone', 'Simone Stanghetti', '', '3287449501', '152dd22dfcc825d88a78@members.ebay.com', 'GBP', '123.91', '3 Testa Vintage Retro Industriale Metallo Soffitto Paralume Appeso Ciondolo Luci[Rustico Rosso,No]', 'ENC820', '', '', '', 3, '0.00', 'Lampshade', '', '', 'international', '', 'Via Giuseppe Arimondi n.20', 'IOSS: IM2760000742', '', 'RM', 'roma', '00159', 'Italy', 'IT', '0.00', 'Etrak - Delivery Group', '', '', '', '', 3000, '410', '540', '440', '', '', '', '1st Booking', '2022-08-22', 'unit2', '', '', '123.91', '0.00', '', '', '', '', '165470327247'),
(62605, 'https://s3-us-west-2.amazonaws.com/selroclientsprod1/8357/54149_5913ce22-4828-4133-98cd-6ca23188d6bf0.jpg', '37489:13-08998-99975', 'Pending', '2022-08-19 10:43:51', 'EBAY-led_sone', 'thinushan', '', '0041712201474', '0152e27a2c274f74de20@members.ebay.com', 'GBP', '45.00', 'Vintage Retro Industrielle Semi Flush Mount Ceiling Light Metall Lampenschirm[Orange,Nein]', 'CRSF100OR+PHRB1PWRPW+LSWE315OR', '', '', '', 1, '0.00', 'Lampshade', 'Beat Style Colour', '', 'international', '', 'Wolfhagweg 1', '', '', '', 'Degersheim', '9113', 'Switzerland', 'CH', '20.89', 'UPS', '', '', '', '', 0, '', '', '', '', '', '', '1st Booking', '2022-08-22', 'unit2', '', 'Merged', '73.40', '0.00', '', '', '', '', '165502302344'),
(62606, 'https://s3-us-west-2.amazonaws.com/selroclientsprod1/8357/32484_1c159c27-f777-4ff8-b4e5-c9daa5de40bd0.jpg', '37489:13-08998-99975', 'Pending', '2022-08-19 10:43:51', 'EBAY-led_sone', 'thinushan', '', '0041712201474', '0152e27a2c274f74de20@members.ebay.com', 'GBP', '28.40', 'Vintage Retro Industrielle Semi Flush Mount Ceiling Light Metall Lampenschirm[Grau,Nein]', 'CRSF100GY+PHRB1PWRPW+LSWE315GY', '', '', '', 1, '0.00', 'Lampshade', 'Beat Style Colour', '', 'international', '', 'Wolfhagweg 1', '', '', '', 'Degersheim', '9113', 'Switzerland', 'CH', '0.00', 'UPS', '', '', '', '', 0, '', '', '', '', '', '', '1st Booking', '2022-08-22', 'unit2', '', '2022-08-19 10:43:51-37489:13-08998-99975', '73.40', '0.00', '', '', '', '', '165502302344');

-- --------------------------------------------------------

--
-- Table structure for table `temporders1`
--

CREATE TABLE `temporders1` (
  `id` int(11) NOT NULL,
  `image_from_ship` text NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` datetime NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `currency` text NOT NULL,
  `ordertotal` decimal(10,2) NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `orgSku` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineitemtotal` decimal(10,2) NOT NULL,
  `flags` text NOT NULL,
  `subflags` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `shipping_cost` text NOT NULL,
  `postal_service` text NOT NULL,
  `shipment_id` text NOT NULL,
  `tracking_No` text NOT NULL,
  `label_B64` mediumtext NOT NULL,
  `invoice_B64` mediumtext NOT NULL,
  `weight_In_Grams` int(11) NOT NULL,
  `item_height` varchar(50) NOT NULL,
  `item_length` varchar(50) NOT NULL,
  `item_width` varchar(50) NOT NULL,
  `royalmail_order_id` text NOT NULL,
  `p2go_id` text NOT NULL,
  `p2go_hash` text NOT NULL,
  `booking` text NOT NULL,
  `csvdate` date NOT NULL,
  `unit` text NOT NULL,
  `addedby` text NOT NULL,
  `merge` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `zenstoresOrderTotal` decimal(10,2) NOT NULL,
  `remarks` text NOT NULL,
  `shippedStatus` text NOT NULL,
  `trackingStatus` text NOT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `testusers`
--

CREATE TABLE `testusers` (
  `id` int(3) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `testusers`
--

INSERT INTO `testusers` (`id`, `username`, `password`) VALUES
(1, 'test', 'digitwebtest');

-- --------------------------------------------------------

--
-- Table structure for table `timesheet`
--

CREATE TABLE `timesheet` (
  `ID` bigint(255) NOT NULL,
  `Name` text NOT NULL,
  `Date` date NOT NULL,
  `StartTime` text NOT NULL,
  `EndTime` text NOT NULL,
  `TotalTime` text NOT NULL,
  `project` text NOT NULL,
  `Tasks` mediumtext NOT NULL,
  `SubmittedDate` date NOT NULL,
  `status` text NOT NULL,
  `authorizedby` text NOT NULL,
  `authorizeddate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `typelist`
--

CREATE TABLE `typelist` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `columnname` text NOT NULL,
  `defaultvalue` text NOT NULL,
  `sortorder` int(11) NOT NULL,
  `required` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `type_id` int(11) NOT NULL,
  `typename` text NOT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ukreplacementorders`
--

CREATE TABLE `ukreplacementorders` (
  `id` int(11) NOT NULL,
  `originalorderid` int(11) NOT NULL,
  `linnworkid` text NOT NULL,
  `orderID` text NOT NULL,
  `status` text NOT NULL,
  `date` date NOT NULL,
  `channel` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  `name` text NOT NULL,
  `sku` text NOT NULL,
  `flags` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `customermessageimage` text NOT NULL,
  `replacementimage` text NOT NULL,
  `reasonimages` text NOT NULL,
  `shippingservice` text NOT NULL,
  `shippingaddresscompany` text NOT NULL,
  `shippingaddressline1` text NOT NULL,
  `shippingaddressline2` text NOT NULL,
  `shippingaddressline3` text NOT NULL,
  `shippingaddressregion` text NOT NULL,
  `shippingaddresscity` text NOT NULL,
  `shippingaddresspostcode` text NOT NULL,
  `shippingaddresscountry` text NOT NULL,
  `shippingaddresscountrycode` text NOT NULL,
  `replacementdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reason` text NOT NULL,
  `responsibleperson` text NOT NULL,
  `notes` text NOT NULL,
  `ProcessedDate` datetime NOT NULL,
  `PostalService` text NOT NULL,
  `TrackingNumber` text NOT NULL,
  `TrackingStatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `variations`
--

CREATE TABLE `variations` (
  `id` int(11) NOT NULL,
  `SKU` text NOT NULL,
  `childs` varchar(100) NOT NULL,
  `ListingType` text NOT NULL,
  `ParentSKU` text NOT NULL,
  `IsVariationGroup` text NOT NULL,
  `VariationGroupName` text NOT NULL,
  `Type` text NOT NULL,
  `ProductName` text NOT NULL,
  `ProductType` text NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `ProductDescription` mediumtext NOT NULL,
  `ProductDescriptionLink` varchar(100) NOT NULL,
  `Tags` mediumtext DEFAULT NULL,
  `metadata` mediumtext NOT NULL,
  `Price` decimal(11,2) NOT NULL,
  `location` text NOT NULL,
  `germanInventory` int(10) DEFAULT NULL,
  `Quantity` int(10) DEFAULT NULL,
  `outofstock` text NOT NULL,
  `unit2` int(11) NOT NULL,
  `Mainimage` text DEFAULT NULL,
  `Subimages` text DEFAULT NULL,
  `Image1` text NOT NULL,
  `Image2` text NOT NULL,
  `Image3` text NOT NULL,
  `Image4` text NOT NULL,
  `Image5` text NOT NULL,
  `Image6` text NOT NULL,
  `Image7` text NOT NULL,
  `Image8` text NOT NULL,
  `Image9` text NOT NULL,
  `Image10` text NOT NULL,
  `SupplierImage` text NOT NULL,
  `ImageURL` varchar(255) NOT NULL,
  `ProductTypeAttributee` text NOT NULL,
  `Brand` text NOT NULL,
  `LightDirection` text NOT NULL,
  `Colour` text NOT NULL,
  `Features` text NOT NULL,
  `BulletPoint1` text NOT NULL,
  `BulletPoint2` text NOT NULL,
  `BulletPoint3` text NOT NULL,
  `BulletPoint4` text NOT NULL,
  `Searchterms` mediumtext NOT NULL,
  `Size` text NOT NULL,
  `Length` text NOT NULL,
  `SensorType` text NOT NULL,
  `IPRating` text NOT NULL,
  `NumberOfLights` text NOT NULL,
  `Voltage` text NOT NULL,
  `Finish` text NOT NULL,
  `AssemblyRequired` text NOT NULL,
  `Occassion` text NOT NULL,
  `Material` text NOT NULL,
  `PowerSource` text NOT NULL,
  `ItemWeight` text NOT NULL,
  `Theme` text NOT NULL,
  `Style` text NOT NULL,
  `InstallationArea` text NOT NULL,
  `LabelsAndCertifications` text NOT NULL,
  `Room` text NOT NULL,
  `EnergyEfficiencyRating` text NOT NULL,
  `LightingTechnology` text NOT NULL,
  `ShadeShape` text NOT NULL,
  `RequiredTools` text NOT NULL,
  `Manufacturer` text NOT NULL,
  `LightColor` text NOT NULL,
  `Department` text NOT NULL,
  `ControlStyle` text NOT NULL,
  `Mounting` text NOT NULL,
  `TopToBottom_Height` text NOT NULL,
  `overAllHeight` text NOT NULL,
  `SideToSide_Width` text NOT NULL,
  `FrontToBack_Depth` text NOT NULL,
  `BulbBase` text NOT NULL,
  `CableLength` text NOT NULL,
  `PowerConsumption` text NOT NULL,
  `ManufacturerWarranty` text NOT NULL,
  `Era` text NOT NULL,
  `ECRange` text NOT NULL,
  `Amperage` text NOT NULL,
  `Wattage` text NOT NULL,
  `CurrentType` text NOT NULL,
  `Lumens` text NOT NULL,
  `BulbWattage` text NOT NULL,
  `BulbLifeHours` text NOT NULL,
  `ColourTemperature` text NOT NULL,
  `Connectors` text NOT NULL,
  `Insulation` text NOT NULL,
  `Maxloadweight` text NOT NULL,
  `NumberOfOutput` text NOT NULL,
  `InputAmperage` text NOT NULL,
  `OutputAmperage` text NOT NULL,
  `CableCore` text NOT NULL,
  `Outlettype` text NOT NULL,
  `InputVoltage` text NOT NULL,
  `OutputVoltage` text NOT NULL,
  `RequiredBulb` text NOT NULL,
  `NumberInPack` text NOT NULL,
  `HolderType` text NOT NULL,
  `CeilingRoseHeight` text NOT NULL,
  `CeilingRoseWidth` text NOT NULL,
  `CeilingRoseDepth` text NOT NULL,
  `ShadeHeight` text NOT NULL,
  `ShadeWidth` text NOT NULL,
  `ShadeDepth` text NOT NULL,
  `AdjustableCableHangingLength` text NOT NULL,
  `CableMaxHeight` text NOT NULL,
  `CableMinHeight` text NOT NULL,
  `MaxExtensionLength` text NOT NULL,
  `AdjustableExtension` text NOT NULL,
  `OpenableChainHeight` text NOT NULL,
  `ChainFullLength` text NOT NULL,
  `OpenableChainWidth` text NOT NULL,
  `SpecialFeatures` text NOT NULL,
  `ShadeColour` text NOT NULL,
  `ShadeMaterial` text NOT NULL,
  `CableWiringMaterial` text NOT NULL,
  `linnwork` text NOT NULL,
  `type_id` text NOT NULL,
  `uploadedon` datetime DEFAULT NULL,
  `addedby` text DEFAULT NULL,
  `history` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bol_mappingsku`
--
ALTER TABLE `bol_mappingsku`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `canadaorders`
--
ALTER TABLE `canadaorders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `canadasales`
--
ALTER TABLE `canadasales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `canadasupplyorder`
--
ALTER TABLE `canadasupplyorder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `canadasupplyorderlist`
--
ALTER TABLE `canadasupplyorderlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `canadatemporders`
--
ALTER TABLE `canadatemporders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cbm`
--
ALTER TABLE `cbm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comboproducts`
--
ALTER TABLE `comboproducts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `csvtemplate`
--
ALTER TABLE `csvtemplate`
  ADD PRIMARY KEY (`temp_id`);

--
-- Indexes for table `csvtemplatelist`
--
ALTER TABLE `csvtemplatelist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ebaylistings`
--
ALTER TABLE `ebaylistings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fbasales`
--
ALTER TABLE `fbasales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fbastock`
--
ALTER TABLE `fbastock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `germanlogs`
--
ALTER TABLE `germanlogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `germanorders`
--
ALTER TABLE `germanorders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `germansales`
--
ALTER TABLE `germansales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `germansupplyorder`
--
ALTER TABLE `germansupplyorder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `germansupplyorderlist`
--
ALTER TABLE `germansupplyorderlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `germantemporders`
--
ALTER TABLE `germantemporders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invupdatelogs`
--
ALTER TABLE `invupdatelogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lowinv`
--
ALTER TABLE `lowinv`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lowinvdetails`
--
ALTER TABLE `lowinvdetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lowstockdetails`
--
ALTER TABLE `lowstockdetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mappingsku`
--
ALTER TABLE `mappingsku`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `netharlandorders`
--
ALTER TABLE `netharlandorders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `netharlandsales`
--
ALTER TABLE `netharlandsales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `netharlandtemporders`
--
ALTER TABLE `netharlandtemporders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `netherlandlogs`
--
ALTER TABLE `netherlandlogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `shippingaddresspostcode` (`shippingaddresspostcode`);

--
-- Indexes for table `picklist`
--
ALTER TABLE `picklist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_selro`
--
ALTER TABLE `sales_selro`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `selro_file`
--
ALTER TABLE `selro_file`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `shippinginfo`
--
ALTER TABLE `shippinginfo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skuchangelog`
--
ALTER TABLE `skuchangelog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staffleave`
--
ALTER TABLE `staffleave`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocktransfer`
--
ALTER TABLE `stocktransfer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocktransferlist`
--
ALTER TABLE `stocktransferlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplyorder`
--
ALTER TABLE `supplyorder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplyorderlist`
--
ALTER TABLE `supplyorderlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temporders`
--
ALTER TABLE `temporders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flags` (`flags`),
  ADD KEY `flags_2` (`flags`),
  ADD KEY `subflags` (`subflags`),
  ADD KEY `shippingservice` (`shippingservice`),
  ADD KEY `postal_service` (`postal_service`);

--
-- Indexes for table `temporders1`
--
ALTER TABLE `temporders1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testusers`
--
ALTER TABLE `testusers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timesheet`
--
ALTER TABLE `timesheet`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `typelist`
--
ALTER TABLE `typelist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `ukreplacementorders`
--
ALTER TABLE `ukreplacementorders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `variations`
--
ALTER TABLE `variations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `bol_mappingsku`
--
ALTER TABLE `bol_mappingsku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=493;

--
-- AUTO_INCREMENT for table `canadaorders`
--
ALTER TABLE `canadaorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62952;

--
-- AUTO_INCREMENT for table `canadasales`
--
ALTER TABLE `canadasales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1093;

--
-- AUTO_INCREMENT for table `canadasupplyorder`
--
ALTER TABLE `canadasupplyorder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `canadasupplyorderlist`
--
ALTER TABLE `canadasupplyorderlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `canadatemporders`
--
ALTER TABLE `canadatemporders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `cbm`
--
ALTER TABLE `cbm`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2255;

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `comboproducts`
--
ALTER TABLE `comboproducts`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14930;

--
-- AUTO_INCREMENT for table `csvtemplate`
--
ALTER TABLE `csvtemplate`
  MODIFY `temp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `csvtemplatelist`
--
ALTER TABLE `csvtemplatelist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2020;

--
-- AUTO_INCREMENT for table `ebaylistings`
--
ALTER TABLE `ebaylistings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47770;

--
-- AUTO_INCREMENT for table `fbasales`
--
ALTER TABLE `fbasales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=407;

--
-- AUTO_INCREMENT for table `fbastock`
--
ALTER TABLE `fbastock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=573;

--
-- AUTO_INCREMENT for table `germanlogs`
--
ALTER TABLE `germanlogs`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7658;

--
-- AUTO_INCREMENT for table `germanorders`
--
ALTER TABLE `germanorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99616;

--
-- AUTO_INCREMENT for table `germansales`
--
ALTER TABLE `germansales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49399;

--
-- AUTO_INCREMENT for table `germansupplyorder`
--
ALTER TABLE `germansupplyorder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `germansupplyorderlist`
--
ALTER TABLE `germansupplyorderlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3771;

--
-- AUTO_INCREMENT for table `germantemporders`
--
ALTER TABLE `germantemporders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12836;

--
-- AUTO_INCREMENT for table `invupdatelogs`
--
ALTER TABLE `invupdatelogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7659;

--
-- AUTO_INCREMENT for table `lowinv`
--
ALTER TABLE `lowinv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=581;

--
-- AUTO_INCREMENT for table `lowinvdetails`
--
ALTER TABLE `lowinvdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8933;

--
-- AUTO_INCREMENT for table `lowstockdetails`
--
ALTER TABLE `lowstockdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `mappingsku`
--
ALTER TABLE `mappingsku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5739;

--
-- AUTO_INCREMENT for table `netharlandorders`
--
ALTER TABLE `netharlandorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `netharlandsales`
--
ALTER TABLE `netharlandsales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `netharlandtemporders`
--
ALTER TABLE `netharlandtemporders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `netherlandlogs`
--
ALTER TABLE `netherlandlogs`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=529582;

--
-- AUTO_INCREMENT for table `picklist`
--
ALTER TABLE `picklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14110;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405075;

--
-- AUTO_INCREMENT for table `sales_selro`
--
ALTER TABLE `sales_selro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=527;

--
-- AUTO_INCREMENT for table `selro_file`
--
ALTER TABLE `selro_file`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40808;

--
-- AUTO_INCREMENT for table `shippinginfo`
--
ALTER TABLE `shippinginfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `skuchangelog`
--
ALTER TABLE `skuchangelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1989;

--
-- AUTO_INCREMENT for table `staffleave`
--
ALTER TABLE `staffleave`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT for table `stocktransfer`
--
ALTER TABLE `stocktransfer`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `stocktransferlist`
--
ALTER TABLE `stocktransferlist`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7845;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `supplyorder`
--
ALTER TABLE `supplyorder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=392;

--
-- AUTO_INCREMENT for table `supplyorderlist`
--
ALTER TABLE `supplyorderlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11236;

--
-- AUTO_INCREMENT for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `temporders`
--
ALTER TABLE `temporders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63051;

--
-- AUTO_INCREMENT for table `temporders1`
--
ALTER TABLE `temporders1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21232;

--
-- AUTO_INCREMENT for table `testusers`
--
ALTER TABLE `testusers`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timesheet`
--
ALTER TABLE `timesheet`
  MODIFY `ID` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8078;

--
-- AUTO_INCREMENT for table `typelist`
--
ALTER TABLE `typelist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1332;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `ukreplacementorders`
--
ALTER TABLE `ukreplacementorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1507;

--
-- AUTO_INCREMENT for table `variations`
--
ALTER TABLE `variations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
