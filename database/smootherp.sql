/*
 Navicat Premium Dump SQL

 Source Server         : SERP DB
 Source Server Type    : MySQL
 Source Server Version : 80409 (8.4.9)
 Source Host           : 127.0.0.1:3306
 Source Schema         : smootherp

 Target Server Type    : MySQL
 Target Server Version : 80409 (8.4.9)
 File Encoding         : 65001

 Date: 10/06/2026 05:38:39
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for accounts
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `type` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `isSystem` tinyint(1) NOT NULL DEFAULT 0,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_account_org_code`(`idOrganization` ASC, `code` ASC) USING BTREE,
  INDEX `idx_acc_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 172 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of accounts
-- ----------------------------
INSERT INTO `accounts` VALUES (1, '1000', 'Cash', 'asset', 1, 1);
INSERT INTO `accounts` VALUES (2, '1100', 'Accounts Receivable', 'asset', 1, 1);
INSERT INTO `accounts` VALUES (3, '2200', 'Tax Payable', 'liability', 1, 1);
INSERT INTO `accounts` VALUES (4, '4000', 'Sales Revenue', 'income', 1, 1);
INSERT INTO `accounts` VALUES (5, '1200', 'Inventory Asset', 'asset', 1, 1);
INSERT INTO `accounts` VALUES (6, '5000', 'Cost of Goods Sold', 'expense', 1, 1);
INSERT INTO `accounts` VALUES (7, '2000', 'Accounts Payable', 'liability', 1, 1);
INSERT INTO `accounts` VALUES (8, '5100', 'Rent', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (9, '5200', 'Utilities', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (10, '5300', 'Salaries & Wages', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (11, '5400', 'Office Supplies', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (12, '5900', 'Miscellaneous Expense', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (13, '1010', 'Bank', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (14, '1150', 'Payment Clearing', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (15, '1300', 'Prepaid Expenses', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (16, '1400', 'Security Deposits', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (17, '1500', 'Fixed Asset', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (18, '1600', 'Other Current Assets', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (19, '1700', 'Other Assets', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (20, '1800', 'Deferred Tax Asset', 'asset', 0, 1);
INSERT INTO `accounts` VALUES (21, '2100', 'Credit Card', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (22, '2150', 'Accrued Expenses', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (23, '2250', 'Payroll Liabilities', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (24, '2300', 'Customer Deposits', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (25, '2350', 'Overdraft', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (26, '2400', 'Loans Payable', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (27, '2500', 'Long Term Liability', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (28, '2600', 'Other Liability', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (29, '2700', 'Deferred Tax Liability', 'liability', 0, 1);
INSERT INTO `accounts` VALUES (30, '3000', 'Owner\'s Equity / Capital', 'equity', 0, 1);
INSERT INTO `accounts` VALUES (31, '3100', 'Retained Earnings', 'equity', 0, 1);
INSERT INTO `accounts` VALUES (32, '3200', 'Share Capital / Stock', 'equity', 0, 1);
INSERT INTO `accounts` VALUES (33, '3300', 'Additional Paid-In Capital', 'equity', 0, 1);
INSERT INTO `accounts` VALUES (34, '3400', 'Owner Draw / Withdrawals', 'equity', 0, 1);
INSERT INTO `accounts` VALUES (35, '3500', 'Reserves', 'equity', 0, 1);
INSERT INTO `accounts` VALUES (36, '3600', 'Retained Profit', 'equity', 0, 1);
INSERT INTO `accounts` VALUES (37, '4100', 'Service Revenue', 'income', 0, 1);
INSERT INTO `accounts` VALUES (38, '4200', 'Other Income', 'income', 0, 1);
INSERT INTO `accounts` VALUES (39, '4300', 'Interest Income', 'income', 0, 1);
INSERT INTO `accounts` VALUES (40, '4400', 'Rental Income', 'income', 0, 1);
INSERT INTO `accounts` VALUES (41, '4500', 'Gain on Sale of Assets', 'income', 0, 1);
INSERT INTO `accounts` VALUES (42, '4600', 'Discounts Received', 'income', 0, 1);
INSERT INTO `accounts` VALUES (43, '4700', 'Foreign Exchange Gain', 'income', 0, 1);
INSERT INTO `accounts` VALUES (44, '5500', 'Insurance', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (45, '5600', 'Marketing / Advertising', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (46, '5700', 'Depreciation / Amortization', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (47, '5750', 'Bank Charges', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (48, '5800', 'Travel / Meals', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (49, '5850', 'Repairs & Maintenance', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (50, '5910', 'Interest Expense', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (51, '5920', 'Taxes', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (52, '5930', 'Discounts Given', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (53, '5940', 'Foreign Exchange Loss', 'expense', 0, 1);
INSERT INTO `accounts` VALUES (109, '1000', 'Cash', 'asset', 1, 6);
INSERT INTO `accounts` VALUES (110, '1100', 'Accounts Receivable', 'asset', 1, 6);
INSERT INTO `accounts` VALUES (111, '2200', 'Tax Payable', 'liability', 1, 6);
INSERT INTO `accounts` VALUES (112, '4000', 'Sales Revenue', 'income', 1, 6);
INSERT INTO `accounts` VALUES (113, '1200', 'Inventory Asset', 'asset', 1, 6);
INSERT INTO `accounts` VALUES (114, '5000', 'Cost of Goods Sold', 'expense', 1, 6);
INSERT INTO `accounts` VALUES (115, '2000', 'Accounts Payable', 'liability', 1, 6);
INSERT INTO `accounts` VALUES (116, '5100', 'Rent', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (117, '5200', 'Utilities', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (118, '5300', 'Salaries & Wages', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (119, '5400', 'Office Supplies', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (120, '5900', 'Miscellaneous Expense', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (121, '1010', 'Bank', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (122, '1150', 'Payment Clearing', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (123, '1300', 'Prepaid Expenses', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (124, '1400', 'Security Deposits', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (125, '1500', 'Fixed Asset', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (126, '1600', 'Other Current Assets', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (127, '1700', 'Other Assets', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (128, '1800', 'Deferred Tax Asset', 'asset', 0, 6);
INSERT INTO `accounts` VALUES (129, '2100', 'Credit Card', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (130, '2150', 'Accrued Expenses', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (131, '2250', 'Payroll Liabilities', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (132, '2300', 'Customer Deposits', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (133, '2350', 'Overdraft', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (134, '2400', 'Loans Payable', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (135, '2500', 'Long Term Liability', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (136, '2600', 'Other Liability', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (137, '2700', 'Deferred Tax Liability', 'liability', 0, 6);
INSERT INTO `accounts` VALUES (138, '3000', 'Owner\'s Equity / Capital', 'equity', 0, 6);
INSERT INTO `accounts` VALUES (139, '3100', 'Retained Earnings', 'equity', 0, 6);
INSERT INTO `accounts` VALUES (140, '3200', 'Share Capital / Stock', 'equity', 0, 6);
INSERT INTO `accounts` VALUES (141, '3300', 'Additional Paid-In Capital', 'equity', 0, 6);
INSERT INTO `accounts` VALUES (142, '3400', 'Owner Draw / Withdrawals', 'equity', 0, 6);
INSERT INTO `accounts` VALUES (143, '3500', 'Reserves', 'equity', 0, 6);
INSERT INTO `accounts` VALUES (144, '3600', 'Retained Profit', 'equity', 0, 6);
INSERT INTO `accounts` VALUES (145, '4100', 'Service Revenue', 'income', 0, 6);
INSERT INTO `accounts` VALUES (146, '4200', 'Other Income', 'income', 0, 6);
INSERT INTO `accounts` VALUES (147, '4300', 'Interest Income', 'income', 0, 6);
INSERT INTO `accounts` VALUES (148, '4400', 'Rental Income', 'income', 0, 6);
INSERT INTO `accounts` VALUES (149, '4500', 'Gain on Sale of Assets', 'income', 0, 6);
INSERT INTO `accounts` VALUES (150, '4600', 'Discounts Received', 'income', 0, 6);
INSERT INTO `accounts` VALUES (151, '4700', 'Foreign Exchange Gain', 'income', 0, 6);
INSERT INTO `accounts` VALUES (152, '5500', 'Insurance', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (153, '5600', 'Marketing / Advertising', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (154, '5700', 'Depreciation / Amortization', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (155, '5750', 'Bank Charges', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (156, '5800', 'Travel / Meals', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (157, '5850', 'Repairs & Maintenance', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (158, '5910', 'Interest Expense', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (159, '5920', 'Taxes', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (160, '5930', 'Discounts Given', 'expense', 0, 6);
INSERT INTO `accounts` VALUES (161, '5940', 'Foreign Exchange Loss', 'expense', 0, 6);

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `Category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_cat_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of categories
-- ----------------------------
INSERT INTO `categories` VALUES (1, 'Category One', '2022-12-07 21:04:16', 1);
INSERT INTO `categories` VALUES (2, 'Category Two', '2022-12-07 21:04:20', 1);
INSERT INTO `categories` VALUES (3, 'Category Three', '2022-12-07 21:04:24', 1);
INSERT INTO `categories` VALUES (4, 'Category Four', '2022-12-07 21:04:27', 1);
INSERT INTO `categories` VALUES (5, 'Category Five', '2022-12-07 21:04:31', 1);
INSERT INTO `categories` VALUES (6, 'Category Six', '2022-12-07 21:04:36', 1);
INSERT INTO `categories` VALUES (7, 'Category Seven', '2022-12-07 21:04:41', 1);

-- ----------------------------
-- Table structure for currencies
-- ----------------------------
DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies`  (
  `code` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `name` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `symbol` varchar(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of currencies
-- ----------------------------
INSERT INTO `currencies` VALUES ('AED', 'UAE Dirham', 'د.إ');
INSERT INTO `currencies` VALUES ('AUD', 'Australian Dollar', 'A$');
INSERT INTO `currencies` VALUES ('CAD', 'Canadian Dollar', 'C$');
INSERT INTO `currencies` VALUES ('CNY', 'Chinese Yuan', '¥');
INSERT INTO `currencies` VALUES ('EUR', 'Euro', '€');
INSERT INTO `currencies` VALUES ('GBP', 'British Pound', '£');
INSERT INTO `currencies` VALUES ('GHS', 'Ghanaian Cedi', '₵');
INSERT INTO `currencies` VALUES ('INR', 'Indian Rupee', '₹');
INSERT INTO `currencies` VALUES ('JPY', 'Japanese Yen', '¥');
INSERT INTO `currencies` VALUES ('KES', 'Kenyan Shilling', 'KSh');
INSERT INTO `currencies` VALUES ('NGN', 'Nigerian Naira', '₦');
INSERT INTO `currencies` VALUES ('USD', 'US Dollar', '$');
INSERT INTO `currencies` VALUES ('XOF', 'West African CFA', 'CFA');
INSERT INTO `currencies` VALUES ('ZAR', 'South African Rand', 'R');

-- ----------------------------
-- Table structure for customers
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `idDocument` int NOT NULL,
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `phone` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `birthdate` date NOT NULL,
  `purchases` int NOT NULL,
  `lastPurchase` datetime NOT NULL,
  `registerDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_cust_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of customers
-- ----------------------------
INSERT INTO `customers` VALUES (1, 'David Cullison', 123456, 'davidc@mail.com', '(555)567-9999', '27 Joseph Street', '1986-01-05', 15, '2018-12-03 00:01:21', '2022-12-10 16:41:42', 1);
INSERT INTO `customers` VALUES (2, 'Mary Yaeger', 121212, 'maryy@mail.com', '(555) 789-9045', '71 Highland Drive', '1983-06-22', 3, '2022-12-08 12:20:28', '2022-12-10 16:41:27', 1);
INSERT INTO `customers` VALUES (3, 'Robert Zimmerman', 122458, 'robert@mail.com', '(305) 455-6677', '27 Joseph Street', '1989-04-12', 3, '2026-06-06 07:49:35', '2026-06-06 15:49:35', 1);
INSERT INTO `customers` VALUES (4, 'Randall Williams', 103698, 'randalw@mail.com', '(305) 256-6541', '31 Romines Mill Road', '1989-08-15', 5, '2022-12-10 08:42:36', '2022-12-10 16:42:36', 1);
INSERT INTO `customers` VALUES (6, 'Christine Moore', 852100, 'christine@mail.com', '(785) 458-7888', '44 Down Lane', '1990-10-16', 36, '2022-12-07 13:17:31', '2022-12-08 21:11:56', 1);
INSERT INTO `customers` VALUES (7, 'Nicole Young', 100254, 'nicole@mail.com', '(101) 222-1145', '44 Sycamore Fork Road', '1989-12-12', 4, '2022-12-10 08:38:47', '2022-12-10 16:38:47', 1);
INSERT INTO `customers` VALUES (8, 'Grace Moore', 178500, 'gracem@mail.com', '(100) 124-5896', '39 Cambridge Drive', '1990-12-07', 7, '2022-12-10 12:40:02', '2022-12-10 20:40:02', 1);
INSERT INTO `customers` VALUES (9, 'Reed Campbell', 178500, 'reedc@mail.com', '(100) 245-7866', '87 Lang Avenue', '1988-04-16', 18, '2022-12-10 08:43:42', '2022-12-10 16:43:42', 1);
INSERT INTO `customers` VALUES (10, 'Lynn', 101014, 'lynn@mail.com', '(100) 145-8966', '90 Roosevelt Road', '1992-02-22', 0, '0000-00-00 00:00:00', '2022-12-10 20:12:55', 1);
INSERT INTO `customers` VALUES (11, 'Will Williams', 100147, 'williams@mail.com', '(774) 145-8888', '114 Test Address', '1985-04-19', 13, '2022-12-10 12:35:52', '2022-12-10 20:35:52', 1);

-- ----------------------------
-- Table structure for expenses
-- ----------------------------
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `expenseNumber` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `idExpenseAccount` int NOT NULL,
  `idPaidThrough` int NOT NULL,
  `amount` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `expenseDate` date NOT NULL,
  `payee` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `reference` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `createdBy` int NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  `currency` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_expense_account`(`idExpenseAccount` ASC) USING BTREE,
  INDEX `idx_expense_paid`(`idPaidThrough` ASC) USING BTREE,
  INDEX `idx_exp_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of expenses
-- ----------------------------
INSERT INTO `expenses` VALUES (2, 'EXP-1', 8, 13, 12000.00, '2026-06-08', 'Landlord', '', '', 1, '2026-06-08 19:00:55', 1, 'USD');

-- ----------------------------
-- Table structure for invoice_activity_log
-- ----------------------------
DROP TABLE IF EXISTS `invoice_activity_log`;
CREATE TABLE `invoice_activity_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `idInvoice` int NOT NULL,
  `idUser` int NULL DEFAULT NULL,
  `action` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_activity_invoice`(`idInvoice` ASC) USING BTREE,
  INDEX `idx_act_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of invoice_activity_log
-- ----------------------------
INSERT INTO `invoice_activity_log` VALUES (4, 8, 1, 'created', 'Invoice created with total $ 500.00', '2026-06-09 17:11:30', 1);
INSERT INTO `invoice_activity_log` VALUES (5, 9, 1, 'created', 'Invoice created with total $ 500.00', '2026-06-09 17:11:30', 1);

-- ----------------------------
-- Table structure for invoices
-- ----------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoiceNumber` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `orderReference` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `idCustomer` int NOT NULL,
  `idSeller` int NOT NULL,
  `items` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `subtotal` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `discountType` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'amount',
  `discountValue` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `adjustments` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `netPrice` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `totalPrice` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `amountPaid` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `balanceDue` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `dueDate` date NULL DEFAULT NULL,
  `paymentTerms` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT 'due_on_receipt',
  `status` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'draft',
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `termsConditions` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `createdBy` int NULL DEFAULT NULL,
  `modifiedBy` int NULL DEFAULT NULL,
  `modifiedDate` timestamp NULL DEFAULT NULL,
  `invoiceDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  `currency` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_inv_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of invoices
-- ----------------------------
INSERT INTO `invoices` VALUES (1, '10001', NULL, 1, 1, '', 0.00, 0.00, 'amount', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'due_on_receipt', 'draft', 'bought by me', NULL, 1, NULL, NULL, '2026-06-06 17:47:08', 1, 'USD');
INSERT INTO `invoices` VALUES (2, '10002', NULL, 1, 1, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"1\",\"stock\":\"64\",\"price\":\"91\",\"totalPrice\":\"91\"},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"1\",\"stock\":\"64\",\"price\":\"91\",\"totalPrice\":\"91\"},{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":\"1\",\"stock\":\"31\",\"price\":\"70\",\"totalPrice\":\"70\"},{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":\"1\",\"stock\":\"31\",\"price\":\"70\",\"totalPrice\":\"70\"}]', 0.00, 0.00, 'amount', 0.00, 0.00, 0.00, 58.00, 322.00, 380.00, 0.00, 380.00, '2026-06-05', 'due_on_receipt', 'draft', '', NULL, 1, NULL, NULL, '2026-06-06 17:54:44', 1, 'USD');

-- ----------------------------
-- Table structure for journal_entries
-- ----------------------------
DROP TABLE IF EXISTS `journal_entries`;
CREATE TABLE `journal_entries`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `entryDate` date NOT NULL,
  `reference` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `sourceType` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `sourceId` int NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `createdBy` int NULL DEFAULT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_journal_source`(`sourceType` ASC, `sourceId` ASC) USING BTREE,
  INDEX `idx_je_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of journal_entries
-- ----------------------------
INSERT INTO `journal_entries` VALUES (15, '2026-06-08', 'EXP-1', 'expense', 2, 'Expense EXP-1 — Rent', 1, '2026-06-08 19:04:12', 1);

-- ----------------------------
-- Table structure for journal_lines
-- ----------------------------
DROP TABLE IF EXISTS `journal_lines`;
CREATE TABLE `journal_lines`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `idJournalEntry` int NOT NULL,
  `idAccount` int NOT NULL,
  `debit` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `credit` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_line_entry`(`idJournalEntry` ASC) USING BTREE,
  INDEX `idx_line_account`(`idAccount` ASC) USING BTREE,
  INDEX `idx_jl_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 56 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of journal_lines
-- ----------------------------
INSERT INTO `journal_lines` VALUES (36, 15, 8, 12000.00, 0.00, 1);
INSERT INTO `journal_lines` VALUES (37, 15, 13, 0.00, 12000.00, 1);

-- ----------------------------
-- Table structure for organization_currencies
-- ----------------------------
DROP TABLE IF EXISTS `organization_currencies`;
CREATE TABLE `organization_currencies`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `idOrganization` int NOT NULL,
  `currencyCode` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `isBase` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_org_currency`(`idOrganization` ASC, `currencyCode` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of organization_currencies
-- ----------------------------
INSERT INTO `organization_currencies` VALUES (1, 1, 'USD', 1);
INSERT INTO `organization_currencies` VALUES (4, 6, 'USD', 1);
INSERT INTO `organization_currencies` VALUES (5, 1, 'AED', 0);
INSERT INTO `organization_currencies` VALUES (6, 1, 'GHS', 0);

-- ----------------------------
-- Table structure for organizations
-- ----------------------------
DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `code` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `baseCurrency` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'USD',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `industry` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `website` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `fax` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `region` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `postalCode` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `themeColor` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '#1e3a5f',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_org_code`(`code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of organizations
-- ----------------------------
INSERT INTO `organizations` VALUES (1, 'Default Organization', 'DEFAULT', NULL, NULL, NULL, 'USD', 1, '2026-06-09 10:31:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '#1e3a5f');
INSERT INTO `organizations` VALUES (6, 'TRACE365', 'TRACE365', 'info@trace365.net', '0', 'Accra, Ghana', 'USD', 1, '2026-06-10 02:04:01', '', '', '', '', '', '', '', NULL, '#df2559');

-- ----------------------------
-- Table structure for payments_received
-- ----------------------------
DROP TABLE IF EXISTS `payments_received`;
CREATE TABLE `payments_received`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paymentNumber` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `idInvoice` int NOT NULL,
  `idCustomer` int NOT NULL,
  `amount` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `paymentDate` date NOT NULL,
  `paymentMode` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'cash',
  `reference` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `createdBy` int NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  `currency` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_payments_invoice`(`idInvoice` ASC) USING BTREE,
  INDEX `idx_payments_customer`(`idCustomer` ASC) USING BTREE,
  INDEX `idx_pay_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of payments_received
-- ----------------------------

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `idCategory` int NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL DEFAULT 'good',
  `code` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `stock` int NOT NULL,
  `buyingPrice` float NOT NULL,
  `sellingPrice` float NOT NULL,
  `sales` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_prod_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 75 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of products
-- ----------------------------
INSERT INTO `products` VALUES (25, 3, 'good', '301', 'Product Sample Two', 'views/img/products/default/anonymous.png', 18, 144, 185, 23, '2026-06-08 19:12:12', 1);
INSERT INTO `products` VALUES (36, 4, 'good', '401', 'Product Sample Three', 'views/img/products/default/anonymous.png', 55, 98, 125, 22, '2022-12-10 16:42:36', 1);
INSERT INTO `products` VALUES (61, 7, 'good', '518', 'Test Product', 'views/img/products/518/204.jpg', 19, 20, 28, 41, '2022-12-07 21:19:13', 1);
INSERT INTO `products` VALUES (62, 4, 'good', '519', 'Product Sample Five', 'views/img/products/default/anonymous.png', 95, 120, 156, 0, '2022-12-10 20:12:55', 1);
INSERT INTO `products` VALUES (63, 7, 'good', '520', 'Product Sample Six', 'views/img/products/default/anonymous.png', 53, 70, 98, 0, '2022-12-10 20:12:55', 1);
INSERT INTO `products` VALUES (64, 1, 'good', '521', 'Product Sample Seven', 'views/img/products/default/anonymous.png', 32, 50, 70, 0, '2022-12-08 20:31:25', 1);
INSERT INTO `products` VALUES (66, 4, 'good', '523', 'Product Sample Nine', 'views/img/products/default/anonymous.png', 37, 25, 35, 23, '2022-12-10 20:35:52', 1);
INSERT INTO `products` VALUES (67, 5, 'good', '524', 'Product Sample Ten', 'views/img/products/default/anonymous.png', 65, 65, 91, 6, '2022-12-10 16:43:42', 1);
INSERT INTO `products` VALUES (68, 4, 'good', '525', 'Product Sample Eleven', 'views/img/products/default/anonymous.png', 13, 120, 168, 13, '2026-06-06 15:49:35', 1);
INSERT INTO `products` VALUES (71, 2, 'good', '1202', 'Lambogini', 'views/img/products/default/anonymous.png', 45, 500000, 1000000, 0, '2026-06-08 19:24:27', 1);
INSERT INTO `products` VALUES (72, 0, 'good', '140', 'FMB140', 'views/img/products/default/anonymous.png', 45, 150, 225, 0, '2026-06-08 19:25:40', 1);
INSERT INTO `products` VALUES (73, 2, 'good', '8765', 'startek', 'views/img/products/default/anonymous.png', 12, 89, 124.6, 0, '2026-06-08 19:39:33', 1);

-- ----------------------------
-- Table structure for quotations
-- ----------------------------
DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `quoteNumber` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `orderReference` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `idCustomer` int NOT NULL,
  `idSeller` int NOT NULL,
  `items` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `subtotal` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `discountType` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'amount',
  `discountValue` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `adjustments` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `netPrice` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `totalPrice` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `expiryDate` date NULL DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'draft',
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `termsConditions` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `idInvoice` int NULL DEFAULT NULL,
  `createdBy` int NULL DEFAULT NULL,
  `modifiedBy` int NULL DEFAULT NULL,
  `modifiedDate` timestamp NULL DEFAULT NULL,
  `quoteDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  `currency` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_quote_customer`(`idCustomer` ASC) USING BTREE,
  INDEX `idx_quote_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quotations
-- ----------------------------
INSERT INTO `quotations` VALUES (1, '1001', '', 10, 1, '[{\"id\":\"\",\"description\":\"Installation\",\"quantity\":\"100\",\"stock\":\"999899\",\"price\":\"20\",\"totalPrice\":\"2000\"},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"2\",\"stock\":\"11\",\"price\":\"168\",\"totalPrice\":\"336\"}]', 2336.00, 1168.00, 'percent', 50.00, 500.00, 0.00, 333.60, 1668.00, 2001.60, '2026-06-10', 'draft', '', '', NULL, 1, NULL, NULL, '2026-06-08 18:53:45', 1, 'USD');

-- ----------------------------
-- Table structure for sales
-- ----------------------------
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` int NOT NULL,
  `idCustomer` int NOT NULL,
  `idSeller` int NOT NULL,
  `products` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `tax` int NOT NULL,
  `netPrice` float NOT NULL,
  `totalPrice` float NOT NULL,
  `paymentMethod` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `saledate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  `currency` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_sale_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of sales
-- ----------------------------
INSERT INTO `sales` VALUES (9, 10001, 2, 2, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"3\",\"stock\":\"29\",\"price\":\"185\",\"totalPrice\":\"555\"}]', 17, 555, 572, 'cash', '2018-12-04 03:53:28', 1, 'USD');
INSERT INTO `sales` VALUES (11, 10002, 3, 1, '[{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"4\",\"stock\":\"16\",\"price\":\"490\",\"totalPrice\":\"1960\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"6\",\"stock\":\"14\",\"price\":\"125\",\"totalPrice\":\"750\"}]', 0, 2710, 2710, 'cash', '2018-12-05 09:30:28', 1, 'USD');
INSERT INTO `sales` VALUES (12, 10003, 3, 1, '[{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"1\",\"stock\":\"2\",\"price\":\"490\",\"totalPrice\":\"490\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"1\",\"stock\":\"8\",\"price\":\"125\",\"totalPrice\":\"125\"},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"1\",\"stock\":\"23\",\"price\":\"185\",\"totalPrice\":\"185\"},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":\"2\",\"stock\":\"114\",\"price\":\"78\",\"totalPrice\":\"156\"}]', 48, 956, 1004, 'cash', '2019-04-10 01:59:10', 1, 'USD');
INSERT INTO `sales` VALUES (14, 10005, 6, 1, '[{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":\"9\",\"stock\":\"31\",\"price\":\"28\",\"totalPrice\":\"252\"},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"3\",\"stock\":\"3\",\"price\":\"490\",\"totalPrice\":\"1470\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"5\",\"stock\":\"3\",\"price\":\"125\",\"totalPrice\":\"625\"}]', 117, 2347, 2464, 'cash', '2020-02-26 08:34:45', 1, 'USD');
INSERT INTO `sales` VALUES (15, 10006, 6, 1, '[{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":\"17\",\"stock\":\"19\",\"price\":\"28\",\"totalPrice\":\"476\"},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"2\",\"stock\":\"1\",\"price\":\"185\",\"totalPrice\":\"370\"}]', 25, 846, 871, 'cash', '2021-01-05 18:36:20', 1, 'USD');
INSERT INTO `sales` VALUES (17, 10008, 4, 1, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"2\",\"stock\":\"69\",\"price\":\"91\",\"totalPrice\":\"182\"}]', 0, 182, 182, 'cash', '2021-09-28 08:18:53', 1, 'USD');
INSERT INTO `sales` VALUES (18, 10009, 7, 1, '[{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"3\",\"stock\":\"57\",\"price\":\"35\",\"totalPrice\":\"105\"},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":\"1\",\"stock\":\"40\",\"price\":\"140\",\"totalPrice\":\"140\"}]', 5, 245, 250, 'cash', '2022-02-14 02:58:09', 1, 'USD');
INSERT INTO `sales` VALUES (19, 10010, 4, 1, '[{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"3\",\"stock\":\"55\",\"price\":\"125\",\"totalPrice\":\"375\"}]', 4, 375, 379, 'cash', '2022-06-29 06:42:37', 1, 'USD');
INSERT INTO `sales` VALUES (20, 10011, 9, 1, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"4\",\"stock\":\"65\",\"price\":\"91\",\"totalPrice\":\"364\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"10\",\"stock\":\"47\",\"price\":\"35\",\"totalPrice\":\"350\"},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":\"4\",\"stock\":\"36\",\"price\":\"140\",\"totalPrice\":\"560\"}]', 64, 1274, 1338, 'CC-110101458966', '2022-09-20 16:43:42', 1, 'USD');
INSERT INTO `sales` VALUES (21, 10012, 11, 1, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"3\",\"stock\":\"23\",\"price\":\"168\",\"totalPrice\":\"504\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"10\",\"stock\":\"37\",\"price\":\"35\",\"totalPrice\":\"350\"}]', 68, 854, 922, 'CC-100000147850', '2022-12-10 20:35:52', 1, 'USD');
INSERT INTO `sales` VALUES (22, 10013, 8, 2, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"7\",\"stock\":\"16\",\"price\":\"168\",\"totalPrice\":\"1176\"}]', 0, 1176, 1176, 'cash', '2022-12-10 20:40:02', 1, 'USD');
INSERT INTO `sales` VALUES (23, 10014, 3, 1, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"3\",\"stock\":\"13\",\"price\":\"168\",\"totalPrice\":\"504\"}]', 101, 504, 605, 'CC-09876542437483', '2026-06-06 15:49:35', 1, 'USD');

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
  `settingKey` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `settingValue` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`idOrganization`, `settingKey`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES ('accounting_enabled', '1', 1);
INSERT INTO `settings` VALUES ('multicurrency_enabled', '1', 1);
INSERT INTO `settings` VALUES ('accounting_enabled', '1', 6);
INSERT INTO `settings` VALUES ('multicurrency_enabled', '0', 6);

-- ----------------------------
-- Table structure for stock_movements
-- ----------------------------
DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `idProduct` int NOT NULL,
  `sourceType` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `sourceId` int NOT NULL DEFAULT 0,
  `qtyChange` int NOT NULL,
  `unitCost` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `movementDate` date NOT NULL,
  `note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_movement_product`(`idProduct` ASC) USING BTREE,
  INDEX `idx_movement_source`(`sourceType` ASC, `sourceId` ASC) USING BTREE,
  INDEX `idx_sm_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of stock_movements
-- ----------------------------
INSERT INTO `stock_movements` VALUES (1, 18, 'opening', 0, 10, 56.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (2, 25, 'opening', 0, 18, 144.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (3, 36, 'opening', 0, 55, 98.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (4, 44, 'opening', 0, 8, 350.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (5, 61, 'opening', 0, 19, 20.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (6, 62, 'opening', 0, 95, 120.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (7, 63, 'opening', 0, 53, 70.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (8, 64, 'opening', 0, 32, 50.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (9, 65, 'opening', 0, 5, 100.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (10, 66, 'opening', 0, 37, 25.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (11, 67, 'opening', 0, 65, 65.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);
INSERT INTO `stock_movements` VALUES (12, 68, 'opening', 0, 13, 120.00, '2026-06-08', 'Opening balance', '2026-06-08 13:27:43', 1);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `user` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `password` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `profile` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `photo` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `phone` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `status` int NOT NULL,
  `lastLogin` datetime NOT NULL,
  `resetToken` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `resetTokenExpires` datetime NULL DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idOrganization` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_org`(`idOrganization` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'Administrator', 'admin', '$2y$10$XgUq9IwiWF5NHW4QXD696euIePVHpFxI59x9RgTj20Xr6IajzJD7W', 'Administrator', 'views/img/users/admin/admin-icn.png', 'admon@yahoo.com', '', 1, '2026-06-09 09:07:44', NULL, NULL, '2026-06-09 17:07:44', 1);
INSERT INTO `users` VALUES (2, 'Kwasi Sarpong', 'seller', '', '', 'views/img/users/jonathan/239.jpg', '', '', 1, '2022-12-10 12:39:15', NULL, NULL, '2026-06-09 10:31:52', 1);
INSERT INTO `users` VALUES (3, 'Nana Banyin', 'carmen', '', '', 'views/img/users/carmen/215.jpg', '', '', 1, '2022-12-10 12:17:55', NULL, NULL, '2026-06-09 10:31:52', 1);
INSERT INTO `users` VALUES (4, 'Super Admin', 'superadmin', '$2y$10$YhTeDW2wQ6PaktkzHhFVLul/FX1OsVK1qqpASf937aa7fLy97WAFa', 'SuperAdmin', '', '', '', 1, '2026-06-09 18:01:23', NULL, NULL, '2026-06-10 02:01:23', NULL);
INSERT INTO `users` VALUES (6, 'Yaw Koree', 'yaw', '$2y$10$T55SOvVg6PZ13sxe9SOnnO6UBqhIPmPE37g/h2w4xi1eBU4viUR2W', 'Administrator', '', 'yaw@trace365.net', '', 1, '2026-06-10 02:04:01', NULL, NULL, '2026-06-10 02:04:01', 6);

SET FOREIGN_KEY_CHECKS = 1;
