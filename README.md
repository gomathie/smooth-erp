# Smooth ERP

Smooth ERP is a web-based Point of Sale, inventory, invoicing, and accounting system built with PHP and MySQL. It helps small and medium-sized businesses manage day-to-day sales operations, track products and stock, handle customers, issue quotations and invoices, record payments, manage expenses, and view basic financial reports from one system.

The application started as a POS system and now includes ERP-style business tools such as accounting, customer statements, quotations, invoices, payments, expenses, stock movements, and double-entry journal records.

## What The App Does

Smooth ERP gives businesses a central place to manage retail and back-office operations.

It can be used to:

- Manage products, product categories, stock quantities, buying prices, and selling prices.
- Support both physical goods and service-based products.
- Process sales and keep a history of completed transactions.
- Manage customers and track customer purchase activity.
- Create quotations for customers before a sale is confirmed.
- Convert accepted quotations into invoices.
- Create, edit, print, and manage invoices.
- Record partial or full payments against invoices.
- Track outstanding balances, paid invoices, overdue invoices, and customer statements.
- Record business expenses.
- Manage a chart of accounts.
- Generate accounting summaries such as trial balance, journal entries, income, expenses, receivables, tax payable, and profit/loss snapshots.
- Generate sales and business reports.
- Control access through user roles.
- Send password reset emails using SMTP configuration.

## Main Modules

### Dashboard

The dashboard gives users a quick overview of business activity, including sales, customers, products, and recent product records.

### Products And Inventory

The product module stores product details, categories, pricing, images, stock levels, and product type. Products can be marked as goods or services. Goods are stock-tracked, while services can be sold or invoiced without reducing inventory.

### Customers

The customer module stores customer contact information and transaction history. It also supports customer statements so businesses can view invoices, payments, and outstanding balances for each customer.

### Sales

The sales module supports POS-style transaction processing. It updates product stock, customer purchase counts, and sales records.

### Quotations

The quotation module allows businesses to prepare estimates before billing a customer. A quotation can include products, services, discounts, tax, shipping, and adjustments. Accepted quotations can be converted into draft invoices.

### Invoices

The invoice module supports billing workflows outside immediate POS sales. Users can create invoices, edit invoice lines, print invoice PDFs, track payment status, and view detailed invoice activity.

Invoice statuses include draft, sent, partially paid, paid, and overdue.

### Payments

Payments are recorded against invoices. The system recalculates invoice balances automatically and records the financial impact in the accounting journal.

### Expenses

The expenses module records money spent by the business, including the expense account, paid-through account, amount, date, payee, reference, and notes.

### Accounting

The accounting module provides a lightweight double-entry accounting layer. It tracks journal entries created from invoices, payments, expenses, inventory movement, and cost of goods sold.

Accounting features include:

- Chart of accounts
- Trial balance
- Recent journal entries
- Income tracking
- Expense tracking
- Accounts receivable
- Tax payable
- Net profit/loss snapshot
- Outstanding and overdue invoice summaries

### Reports

Reports help users understand sales performance, best-selling products, customer activity, and business trends.

### Settings

Administrators can configure system settings, including enabling or disabling the visible accounting module.

## Who Can Use Smooth ERP

Smooth ERP is suitable for:

- Retail shops
- Small supermarkets
- Boutiques
- Electronics and phone shops
- Spare parts stores
- Service businesses
- Small distributors
- Freelancers or agencies that issue invoices
- Businesses that need simple stock, sales, invoicing, and accounting records
- Students or developers learning PHP, MySQL, POS systems, and ERP workflows

## User Roles

Smooth ERP uses role-based access control so different users only see the tools they need.

### Administrator

Administrators have full access to the system. They can manage products, users, customers, sales, quotations, invoices, payments, expenses, accounting, reports, and settings.

Best for:

- Business owners
- Managers
- Accountants
- System administrators

### Seller

Sellers can handle operational sales tasks such as creating sales, serving customers, and working with daily transaction records depending on the permissions configured in the app.

Best for:

- Cashiers
- Sales staff
- Front-desk operators

### Special / Limited User

Special users have restricted access. This role is useful for staff who should only view or perform limited actions.

Best for:

- Assistants
- Inventory helpers
- Temporary staff
- Users who should not delete or manage sensitive records

## Benefits

Smooth ERP helps businesses:

- Save time by managing sales, products, customers, invoices, and accounting in one place.
- Reduce manual errors in stock, payments, and invoice balances.
- Improve cash flow visibility by showing unpaid, partially paid, and overdue invoices.
- Track business expenses and profitability more clearly.
- Keep better customer records and statements.
- Create professional quotations and invoices.
- Monitor stock movement and avoid unexpected shortages.
- Separate user responsibilities with role-based access.
- Make better decisions using sales, accounting, and inventory reports.
- Move from a simple POS workflow toward a more complete business management system.

## Technology Stack

- PHP
- MySQL / MariaDB
- JavaScript / jQuery
- DataTables
- Bootstrap / AdminLTE
- TCPDF for PDF generation
- SMTP for password reset emails

## Version

Current documented version: **1.2**

Version 1.2 adds the accounting, invoicing, quotations, payments, expenses, customer statement, settings, SMTP password reset, and expanded reporting workflows.
