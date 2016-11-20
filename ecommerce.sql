-- phpMyAdmin SQL Dump
-- version 4.4.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 20, 2016 at 06:40 PM
-- Server version: 5.6.24
-- PHP Version: 5.6.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE IF NOT EXISTS `cart` (
  `cartId` int(11) NOT NULL,
  `deviceId` bigint(20) NOT NULL,
  `productId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `productQuantity` int(11) NOT NULL,
  `productPrice` double NOT NULL,
  `productDiscount` double NOT NULL,
  `categoryTax` double NOT NULL,
  `cartIsDeleted` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cartId`, `deviceId`, `productId`, `categoryId`, `productQuantity`, `productPrice`, `productDiscount`, `categoryTax`, `cartIsDeleted`) VALUES
(1, 2547854785547, 2, 2, 4, 50, 5, 10, 1),
(2, 2547854785547, 1, 1, 1, 50, 10, 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `categoryId` int(11) NOT NULL,
  `categoryName` varchar(225) NOT NULL,
  `categoryDesc` text NOT NULL,
  `categoryTax` double NOT NULL,
  `categoryIsDeleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=>active, 0=>Deleted'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryId`, `categoryName`, `categoryDesc`, `categoryTax`, `categoryIsDeleted`) VALUES
(1, 'Veg', 'Veg is healthy for body', 10, 1),
(2, 'Veg', 'Veg is healthy for body', 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `productId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `productName` varchar(225) NOT NULL,
  `productDesc` text NOT NULL,
  `productPrice` int(11) NOT NULL,
  `productDiscount` double NOT NULL,
  `productIsDeleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=>active, 0=>Deleted'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productId`, `categoryId`, `productName`, `productDesc`, `productPrice`, `productDiscount`, `productIsDeleted`) VALUES
(1, 2, 'Apple', 'An apple a day keep docter away', 50, 10, 1),
(2, 2, 'Apple', 'An apple a day keep docter away', 50, 5, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cartId`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryId`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cartId` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productId` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
