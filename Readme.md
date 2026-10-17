# Mini ERP (PHP + MySQL)

Customer & Item management with Invoice, Invoice Item and Item reports.
Stack: PHP 8 (PDO), MySQL/MariaDB, Bootstrap 5, JavaScript.

## Demo Video
Watch the project walkthrough here: [Mini ERP Demo Video](https://drive.google.com/file/d/1PgeeerxRUTx0668neHnp4HqrqROPf0SA/view?usp=sharing)

## Drive Folder (Demo Video + Code)
(https://drive.google.com/drive/folders/1_QnXqK77mEbmwDJOtKwmMd_vZ-HuDIVh?usp=sharing)

## Setup (local)
1. Install XAMPP (or WAMP/Laragon) with PHP 7.4+ and MySQL. Start **Apache** and **MySQL**.
2. Copy the `erp` folder into `C:\xampp\htdocs\` (so you have `htdocs\erp\index.php`).
3. Open phpMyAdmin (http://localhost/phpmyadmin) -> **Import** -> choose `schema.sql` -> Go.
   (If you were given an official database file, import that instead and adjust the queries/table names, see Assumptions.)
4. Edit `config.php` if your MySQL user/password differ (XAMPP default: root / empty).
5. Browse to http://localhost/erp/

## Features
- Customers: add, edit, delete, search, list (client + server validation)
- Items: add, edit, delete, search, list; category -> sub category dependent dropdown
- Reports: Invoice report and Invoice item report (date range), Item report (unique item names)

## Assumptions
- No database file was supplied with my copy of the task, so I designed `schema.sql` (tables: districts, customers, item_categories, item_sub_categories, items, invoices, invoice_items) including sample data.
- Contact number: 10 digits starting with 0 (Sri Lankan format).
- Names: letters, spaces, `.`, `'`, `-` only.
- Item code is unique. Sub category must belong to the selected category.
- "Item count" in the Invoice report = number of item lines on the invoice.
- Item report groups by item name (never repeats) and sums quantity if duplicate names exist.
- A customer or item used in an invoice cannot be deleted (foreign key protection, friendly message shown).
- Invoice creation was not requested; invoices come from the database/sample data.
