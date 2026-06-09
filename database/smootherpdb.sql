-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: smootherpdb
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(30) NOT NULL,
  `isSystem` tinyint(1) NOT NULL DEFAULT 0,
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_account_org_code` (`idOrganization`,`code`),
  KEY `idx_acc_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,'1000','Cash','asset',1,1),(2,'1100','Accounts Receivable','asset',1,1),(3,'2200','Tax Payable','liability',1,1),(4,'4000','Sales Revenue','income',1,1),(5,'1200','Inventory Asset','asset',1,1),(6,'5000','Cost of Goods Sold','expense',1,1),(7,'2000','Accounts Payable','liability',1,1),(8,'5100','Rent','expense',0,1),(9,'5200','Utilities','expense',0,1),(10,'5300','Salaries & Wages','expense',0,1),(11,'5400','Office Supplies','expense',0,1),(12,'5900','Miscellaneous Expense','expense',0,1),(13,'1010','Bank','asset',0,1),(14,'1150','Payment Clearing','asset',0,1),(15,'1300','Prepaid Expenses','asset',0,1),(16,'1400','Security Deposits','asset',0,1),(17,'1500','Fixed Asset','asset',0,1),(18,'1600','Other Current Assets','asset',0,1),(19,'1700','Other Assets','asset',0,1),(20,'1800','Deferred Tax Asset','asset',0,1),(21,'2100','Credit Card','liability',0,1),(22,'2150','Accrued Expenses','liability',0,1),(23,'2250','Payroll Liabilities','liability',0,1),(24,'2300','Customer Deposits','liability',0,1),(25,'2350','Overdraft','liability',0,1),(26,'2400','Loans Payable','liability',0,1),(27,'2500','Long Term Liability','liability',0,1),(28,'2600','Other Liability','liability',0,1),(29,'2700','Deferred Tax Liability','liability',0,1),(30,'3000','Owner\'s Equity / Capital','equity',0,1),(31,'3100','Retained Earnings','equity',0,1),(32,'3200','Share Capital / Stock','equity',0,1),(33,'3300','Additional Paid-In Capital','equity',0,1),(34,'3400','Owner Draw / Withdrawals','equity',0,1),(35,'3500','Reserves','equity',0,1),(36,'3600','Retained Profit','equity',0,1),(37,'4100','Service Revenue','income',0,1),(38,'4200','Other Income','income',0,1),(39,'4300','Interest Income','income',0,1),(40,'4400','Rental Income','income',0,1),(41,'4500','Gain on Sale of Assets','income',0,1),(42,'4600','Discounts Received','income',0,1),(43,'4700','Foreign Exchange Gain','income',0,1),(44,'5500','Insurance','expense',0,1),(45,'5600','Marketing / Advertising','expense',0,1),(46,'5700','Depreciation / Amortization','expense',0,1),(47,'5750','Bank Charges','expense',0,1),(48,'5800','Travel / Meals','expense',0,1),(49,'5850','Repairs & Maintenance','expense',0,1),(50,'5910','Interest Expense','expense',0,1),(51,'5920','Taxes','expense',0,1),(52,'5930','Discounts Given','expense',0,1),(53,'5940','Foreign Exchange Loss','expense',0,1);
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Category` text NOT NULL,
  `Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_cat_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Category One','2022-12-07 18:04:16',1),(2,'Category Two','2022-12-07 18:04:20',1),(3,'Category Three','2022-12-07 18:04:24',1),(4,'Category Four','2022-12-07 18:04:27',1),(5,'Category Five','2022-12-07 18:04:31',1),(6,'Category Six','2022-12-07 18:04:36',1),(7,'Category Seven','2022-12-07 18:04:41',1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `code` varchar(3) NOT NULL,
  `name` varchar(60) NOT NULL,
  `symbol` varchar(8) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES ('AED','UAE Dirham','د.إ'),('AUD','Australian Dollar','A$'),('CAD','Canadian Dollar','C$'),('CNY','Chinese Yuan','¥'),('EUR','Euro','€'),('GBP','British Pound','£'),('GHS','Ghanaian Cedi','₵'),('INR','Indian Rupee','₹'),('JPY','Japanese Yen','¥'),('KES','Kenyan Shilling','KSh'),('NGN','Nigerian Naira','₦'),('USD','US Dollar','$'),('XOF','West African CFA','CFA'),('ZAR','South African Rand','R');
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `idDocument` int(11) NOT NULL,
  `email` text NOT NULL,
  `phone` text NOT NULL,
  `address` text NOT NULL,
  `birthdate` date NOT NULL,
  `purchases` int(11) NOT NULL,
  `lastPurchase` datetime NOT NULL,
  `registerDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_cust_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'David Cullison',123456,'davidc@mail.com','(555)567-9999','27 Joseph Street','1986-01-05',15,'2018-12-03 00:01:21','2022-12-10 13:41:42',1),(2,'Mary Yaeger',121212,'maryy@mail.com','(555) 789-9045','71 Highland Drive','1983-06-22',3,'2022-12-08 12:20:28','2022-12-10 13:41:27',1),(3,'Robert Zimmerman',122458,'robert@mail.com','(305) 455-6677','27 Joseph Street','1989-04-12',3,'2026-06-06 07:49:35','2026-06-06 12:49:35',1),(4,'Randall Williams',103698,'randalw@mail.com','(305) 256-6541','31 Romines Mill Road','1989-08-15',5,'2022-12-10 08:42:36','2022-12-10 13:42:36',1),(6,'Christine Moore',852100,'christine@mail.com','(785) 458-7888','44 Down Lane','1990-10-16',36,'2022-12-07 13:17:31','2022-12-08 18:11:56',1),(7,'Nicole Young',100254,'nicole@mail.com','(101) 222-1145','44 Sycamore Fork Road','1989-12-12',4,'2022-12-10 08:38:47','2022-12-10 13:38:47',1),(8,'Grace Moore',178500,'gracem@mail.com','(100) 124-5896','39 Cambridge Drive','1990-12-07',7,'2022-12-10 12:40:02','2022-12-10 17:40:02',1),(9,'Reed Campbell',178500,'reedc@mail.com','(100) 245-7866','87 Lang Avenue','1988-04-16',18,'2022-12-10 08:43:42','2022-12-10 13:43:42',1),(10,'Lynn',101014,'lynn@mail.com','(100) 145-8966','90 Roosevelt Road','1992-02-22',0,'0000-00-00 00:00:00','2022-12-10 17:12:55',1),(11,'Will Williams',100147,'williams@mail.com','(774) 145-8888','114 Test Address','1985-04-19',13,'2022-12-10 12:35:52','2022-12-10 17:35:52',1);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expenseNumber` varchar(50) NOT NULL,
  `idExpenseAccount` int(11) NOT NULL,
  `idPaidThrough` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expenseDate` date NOT NULL,
  `payee` varchar(150) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `idx_expense_account` (`idExpenseAccount`),
  KEY `idx_expense_paid` (`idPaidThrough`),
  KEY `idx_exp_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (2,'EXP-1',8,13,12000.00,'2026-06-08','Landlord','','',1,'2026-06-08 16:00:55',1,'USD');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_activity_log`
--

DROP TABLE IF EXISTS `invoice_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idInvoice` int(11) NOT NULL,
  `idUser` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `createdDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_activity_invoice` (`idInvoice`),
  KEY `idx_act_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_activity_log`
--

LOCK TABLES `invoice_activity_log` WRITE;
/*!40000 ALTER TABLE `invoice_activity_log` DISABLE KEYS */;
INSERT INTO `invoice_activity_log` VALUES (4,8,1,'created','Invoice created with total $ 500.00','2026-06-09 14:11:30',1),(5,9,1,'created','Invoice created with total $ 500.00','2026-06-09 14:11:30',1);
/*!40000 ALTER TABLE `invoice_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoiceNumber` varchar(50) NOT NULL,
  `orderReference` varchar(100) DEFAULT NULL,
  `idCustomer` int(11) NOT NULL,
  `idSeller` int(11) NOT NULL,
  `items` text NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discountType` varchar(10) NOT NULL DEFAULT 'amount',
  `discountValue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `adjustments` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `netPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `totalPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amountPaid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balanceDue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `dueDate` date DEFAULT NULL,
  `paymentTerms` varchar(50) DEFAULT 'due_on_receipt',
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `termsConditions` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedDate` timestamp NULL DEFAULT NULL,
  `invoiceDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `idx_inv_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,'10001',NULL,1,1,'',0.00,0.00,'amount',0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,'due_on_receipt','draft','bought by me',NULL,1,NULL,NULL,'2026-06-06 14:47:08',1,'USD'),(2,'10002',NULL,1,1,'[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"1\",\"stock\":\"64\",\"price\":\"91\",\"totalPrice\":\"91\"},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"1\",\"stock\":\"64\",\"price\":\"91\",\"totalPrice\":\"91\"},{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":\"1\",\"stock\":\"31\",\"price\":\"70\",\"totalPrice\":\"70\"},{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":\"1\",\"stock\":\"31\",\"price\":\"70\",\"totalPrice\":\"70\"}]',0.00,0.00,'amount',0.00,0.00,0.00,58.00,322.00,380.00,0.00,380.00,'2026-06-05','due_on_receipt','draft','',NULL,1,NULL,NULL,'2026-06-06 14:54:44',1,'USD');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entryDate` date NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `sourceType` varchar(30) NOT NULL,
  `sourceId` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_journal_source` (`sourceType`,`sourceId`),
  KEY `idx_je_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (15,'2026-06-08','EXP-1','expense',2,'Expense EXP-1 — Rent',1,'2026-06-08 16:04:12',1);
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idJournalEntry` int(11) NOT NULL,
  `idAccount` int(11) NOT NULL,
  `debit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_line_entry` (`idJournalEntry`),
  KEY `idx_line_account` (`idAccount`),
  KEY `idx_jl_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_lines`
--

LOCK TABLES `journal_lines` WRITE;
/*!40000 ALTER TABLE `journal_lines` DISABLE KEYS */;
INSERT INTO `journal_lines` VALUES (36,15,8,12000.00,0.00,1),(37,15,13,0.00,12000.00,1);
/*!40000 ALTER TABLE `journal_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_currencies`
--

DROP TABLE IF EXISTS `organization_currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idOrganization` int(11) NOT NULL,
  `currencyCode` varchar(3) NOT NULL,
  `isBase` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_currency` (`idOrganization`,`currencyCode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_currencies`
--

LOCK TABLES `organization_currencies` WRITE;
/*!40000 ALTER TABLE `organization_currencies` DISABLE KEYS */;
INSERT INTO `organization_currencies` VALUES (1,1,'USD',1);
/*!40000 ALTER TABLE `organization_currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(30) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `baseCurrency` varchar(3) NOT NULL DEFAULT 'USD',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdDate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (1,'Default Organization','DEFAULT',NULL,NULL,NULL,'USD',1,'2026-06-09 07:31:51');
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments_received`
--

DROP TABLE IF EXISTS `payments_received`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_received` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentNumber` varchar(50) NOT NULL,
  `idInvoice` int(11) NOT NULL,
  `idCustomer` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paymentDate` date NOT NULL,
  `paymentMode` varchar(50) NOT NULL DEFAULT 'cash',
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `idx_payments_invoice` (`idInvoice`),
  KEY `idx_payments_customer` (`idCustomer`),
  KEY `idx_pay_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments_received`
--

LOCK TABLES `payments_received` WRITE;
/*!40000 ALTER TABLE `payments_received` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments_received` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'good',
  `code` text NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `stock` int(11) NOT NULL,
  `buyingPrice` float NOT NULL,
  `sellingPrice` float NOT NULL,
  `sales` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_prod_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (25,3,'good','301','Product Sample Two','views/img/products/default/anonymous.png',18,144,185,23,'2026-06-08 16:12:12',1),(36,4,'good','401','Product Sample Three','views/img/products/default/anonymous.png',55,98,125,22,'2022-12-10 13:42:36',1),(61,7,'good','518','Test Product','views/img/products/518/204.jpg',19,20,28,41,'2022-12-07 18:19:13',1),(62,4,'good','519','Product Sample Five','views/img/products/default/anonymous.png',95,120,156,0,'2022-12-10 17:12:55',1),(63,7,'good','520','Product Sample Six','views/img/products/default/anonymous.png',53,70,98,0,'2022-12-10 17:12:55',1),(64,1,'good','521','Product Sample Seven','views/img/products/default/anonymous.png',32,50,70,0,'2022-12-08 17:31:25',1),(66,4,'good','523','Product Sample Nine','views/img/products/default/anonymous.png',37,25,35,23,'2022-12-10 17:35:52',1),(67,5,'good','524','Product Sample Ten','views/img/products/default/anonymous.png',65,65,91,6,'2022-12-10 13:43:42',1),(68,4,'good','525','Product Sample Eleven','views/img/products/default/anonymous.png',13,120,168,13,'2026-06-06 12:49:35',1),(71,2,'good','1202','Lambogini','views/img/products/default/anonymous.png',45,500000,1000000,0,'2026-06-08 16:24:27',1),(72,0,'good','140','FMB140','views/img/products/default/anonymous.png',45,150,225,0,'2026-06-08 16:25:40',1),(73,2,'good','8765','startek','views/img/products/default/anonymous.png',12,89,124.6,0,'2026-06-08 16:39:33',1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quoteNumber` varchar(50) NOT NULL,
  `orderReference` varchar(100) DEFAULT NULL,
  `idCustomer` int(11) NOT NULL,
  `idSeller` int(11) NOT NULL,
  `items` text NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discountType` varchar(10) NOT NULL DEFAULT 'amount',
  `discountValue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `adjustments` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `netPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `totalPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiryDate` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `termsConditions` text DEFAULT NULL,
  `idInvoice` int(11) DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedDate` timestamp NULL DEFAULT NULL,
  `quoteDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `idx_quote_customer` (`idCustomer`),
  KEY `idx_quote_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotations`
--

LOCK TABLES `quotations` WRITE;
/*!40000 ALTER TABLE `quotations` DISABLE KEYS */;
INSERT INTO `quotations` VALUES (1,'1001','',10,1,'[{\"id\":\"\",\"description\":\"Installation\",\"quantity\":\"100\",\"stock\":\"999899\",\"price\":\"20\",\"totalPrice\":\"2000\"},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"2\",\"stock\":\"11\",\"price\":\"168\",\"totalPrice\":\"336\"}]',2336.00,1168.00,'percent',50.00,500.00,0.00,333.60,1668.00,2001.60,'2026-06-10','draft','','',NULL,1,NULL,NULL,'2026-06-08 15:53:45',1,'USD');
/*!40000 ALTER TABLE `quotations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `idCustomer` int(11) NOT NULL,
  `idSeller` int(11) NOT NULL,
  `products` text NOT NULL,
  `tax` int(11) NOT NULL,
  `netPrice` float NOT NULL,
  `totalPrice` float NOT NULL,
  `paymentMethod` text NOT NULL,
  `saledate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `idx_sale_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (9,10001,2,2,'[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"3\",\"stock\":\"29\",\"price\":\"185\",\"totalPrice\":\"555\"}]',17,555,572,'cash','2018-12-04 00:53:28',1,'USD'),(11,10002,3,1,'[{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"4\",\"stock\":\"16\",\"price\":\"490\",\"totalPrice\":\"1960\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"6\",\"stock\":\"14\",\"price\":\"125\",\"totalPrice\":\"750\"}]',0,2710,2710,'cash','2018-12-05 06:30:28',1,'USD'),(12,10003,3,1,'[{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"1\",\"stock\":\"2\",\"price\":\"490\",\"totalPrice\":\"490\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"1\",\"stock\":\"8\",\"price\":\"125\",\"totalPrice\":\"125\"},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"1\",\"stock\":\"23\",\"price\":\"185\",\"totalPrice\":\"185\"},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":\"2\",\"stock\":\"114\",\"price\":\"78\",\"totalPrice\":\"156\"}]',48,956,1004,'cash','2019-04-09 22:59:10',1,'USD'),(14,10005,6,1,'[{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":\"9\",\"stock\":\"31\",\"price\":\"28\",\"totalPrice\":\"252\"},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"3\",\"stock\":\"3\",\"price\":\"490\",\"totalPrice\":\"1470\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"5\",\"stock\":\"3\",\"price\":\"125\",\"totalPrice\":\"625\"}]',117,2347,2464,'cash','2020-02-26 05:34:45',1,'USD'),(15,10006,6,1,'[{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":\"17\",\"stock\":\"19\",\"price\":\"28\",\"totalPrice\":\"476\"},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"2\",\"stock\":\"1\",\"price\":\"185\",\"totalPrice\":\"370\"}]',25,846,871,'cash','2021-01-05 15:36:20',1,'USD'),(17,10008,4,1,'[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"2\",\"stock\":\"69\",\"price\":\"91\",\"totalPrice\":\"182\"}]',0,182,182,'cash','2021-09-28 05:18:53',1,'USD'),(18,10009,7,1,'[{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"3\",\"stock\":\"57\",\"price\":\"35\",\"totalPrice\":\"105\"},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":\"1\",\"stock\":\"40\",\"price\":\"140\",\"totalPrice\":\"140\"}]',5,245,250,'cash','2022-02-13 23:58:09',1,'USD'),(19,10010,4,1,'[{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"3\",\"stock\":\"55\",\"price\":\"125\",\"totalPrice\":\"375\"}]',4,375,379,'cash','2022-06-29 03:42:37',1,'USD'),(20,10011,9,1,'[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"4\",\"stock\":\"65\",\"price\":\"91\",\"totalPrice\":\"364\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"10\",\"stock\":\"47\",\"price\":\"35\",\"totalPrice\":\"350\"},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":\"4\",\"stock\":\"36\",\"price\":\"140\",\"totalPrice\":\"560\"}]',64,1274,1338,'CC-110101458966','2022-09-20 13:43:42',1,'USD'),(21,10012,11,1,'[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"3\",\"stock\":\"23\",\"price\":\"168\",\"totalPrice\":\"504\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"10\",\"stock\":\"37\",\"price\":\"35\",\"totalPrice\":\"350\"}]',68,854,922,'CC-100000147850','2022-12-10 17:35:52',1,'USD'),(22,10013,8,2,'[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"7\",\"stock\":\"16\",\"price\":\"168\",\"totalPrice\":\"1176\"}]',0,1176,1176,'cash','2022-12-10 17:40:02',1,'USD'),(23,10014,3,1,'[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"3\",\"stock\":\"13\",\"price\":\"168\",\"totalPrice\":\"504\"}]',101,504,605,'CC-09876542437483','2026-06-06 12:49:35',1,'USD');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `settingKey` varchar(50) NOT NULL,
  `settingValue` varchar(255) DEFAULT NULL,
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`idOrganization`,`settingKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('accounting_enabled','0',1),('multicurrency_enabled','0',1);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idProduct` int(11) NOT NULL,
  `sourceType` varchar(30) NOT NULL,
  `sourceId` int(11) NOT NULL DEFAULT 0,
  `qtyChange` int(11) NOT NULL,
  `unitCost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `movementDate` date NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `createdDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idOrganization` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_movement_product` (`idProduct`),
  KEY `idx_movement_source` (`sourceType`,`sourceId`),
  KEY `idx_sm_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,18,'opening',0,10,56.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(2,25,'opening',0,18,144.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(3,36,'opening',0,55,98.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(4,44,'opening',0,8,350.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(5,61,'opening',0,19,20.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(6,62,'opening',0,95,120.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(7,63,'opening',0,53,70.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(8,64,'opening',0,32,50.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(9,65,'opening',0,5,100.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(10,66,'opening',0,37,25.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(11,67,'opening',0,65,65.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1),(12,68,'opening',0,13,120.00,'2026-06-08','Opening balance','2026-06-08 10:27:43',1);
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `user` text NOT NULL,
  `password` text NOT NULL,
  `profile` text NOT NULL,
  `photo` text NOT NULL,
  `email` text NOT NULL,
  `phone` text NOT NULL,
  `status` int(1) NOT NULL,
  `lastLogin` datetime NOT NULL,
  `resetToken` varchar(64) DEFAULT NULL,
  `resetTokenExpires` datetime DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idOrganization` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_org` (`idOrganization`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin','$2y$10$XgUq9IwiWF5NHW4QXD696euIePVHpFxI59x9RgTj20Xr6IajzJD7W','Administrator','views/img/users/admin/admin-icn.png','admon@yahoo.com','',1,'2026-06-09 09:07:44',NULL,NULL,'2026-06-09 14:07:44',1),(2,'Kwasi Sarpong','seller','','','views/img/users/jonathan/239.jpg','','',1,'2022-12-10 12:39:15',NULL,NULL,'2026-06-09 07:31:52',1),(3,'Nana Banyin','carmen','','','views/img/users/carmen/215.jpg','','',1,'2022-12-10 12:17:55',NULL,NULL,'2026-06-09 07:31:52',1),(4,'Super Admin','superadmin','$2y$10$YhTeDW2wQ6PaktkzHhFVLul/FX1OsVK1qqpASf937aa7fLy97WAFa','SuperAdmin','','','',1,'2026-06-09 10:32:15',NULL,NULL,'2026-06-09 07:32:15',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-10  1:22:49
