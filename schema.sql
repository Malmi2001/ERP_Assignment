-- schema.sql
-- ----------
-- Creates the whole database for the Mini ERP project.
-- Import this file in phpMyAdmin (Import tab) and everything is created for you.
-- Tables:
--   districts            list of districts used by the customer form
--   customers            Task 1 data
--   item_categories      main categories (Electronics, Stationery...)
--   item_sub_categories  sub categories, each belongs to one category
--   items                Task 2 data
--   invoices             one row per invoice (used by the reports)
--   invoice_items        the items sold on each invoice
-- Foreign keys keep the data consistent (e.g. you can't delete a customer
-- who already has invoices).

DROP DATABASE IF EXISTS erp_db;
CREATE DATABASE erp_db CHARACTER SET utf8mb4;
USE erp_db;
-- Drop old tables first (children before parents) so the script can be re-run safely
DROP TABLE IF EXISTS invoice_items, invoices, items, item_sub_categories, item_categories, customers, districts;

-- Districts (all 25 districts of Sri Lanka)
CREATE TABLE districts (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO districts (name) VALUES ('Ampara'),('Anuradhapura'),('Badulla'),('Batticaloa'),('Colombo'),('Galle'),('Gampaha'),('Hambantota'),('Jaffna'),('Kalutara'),('Kandy'),('Kegalle'),('Kilinochchi'),('Kurunegala'),('Mannar'),('Matale'),('Matara'),('Monaragala'),('Mullaitivu'),('Nuwara Eliya'),('Polonnaruwa'),('Puttalam'),('Ratnapura'),('Trincomalee'),('Vavuniya');

-- Customers: fields required by the assignment
CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title ENUM('Mr','Mrs','Miss','Dr') NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  contact_no VARCHAR(10) NOT NULL,
  district_id INT NOT NULL,
  FOREIGN KEY (district_id) REFERENCES districts(id)
) ENGINE=InnoDB;

-- Item categories and sub categories (a sub category belongs to one category)
CREATE TABLE item_categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL UNIQUE) ENGINE=InnoDB;
CREATE TABLE item_sub_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  FOREIGN KEY (category_id) REFERENCES item_categories(id)
) ENGINE=InnoDB;

-- Items: item_code is UNIQUE so the same code can't be saved twice
CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_code VARCHAR(30) NOT NULL UNIQUE,
  item_name VARCHAR(100) NOT NULL,
  category_id INT NOT NULL,
  sub_category_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  unit_price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (category_id) REFERENCES item_categories(id),
  FOREIGN KEY (sub_category_id) REFERENCES item_sub_categories(id)
) ENGINE=InnoDB;

-- Invoice header: number, date, customer and total amount
CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(20) NOT NULL UNIQUE,
  invoice_date DATE NOT NULL,
  customer_id INT NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;

-- Invoice lines: which item, how many and at what price on that invoice
CREATE TABLE invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id),
  FOREIGN KEY (item_id) REFERENCES items(id)
) ENGINE=InnoDB;

-- Sample data so the lists and reports show something right after import.
-- Invoice dates are in October 2026, use that range when testing the reports.
INSERT INTO item_categories (name) VALUES ('Electronics'),('Stationery'),('Grocery');
INSERT INTO item_sub_categories (category_id,name) VALUES (1,'Mobile Phones'),(1,'Accessories'),(2,'Books'),(2,'Pens'),(3,'Beverages'),(3,'Snacks');
-- Sample customers
INSERT INTO customers (title,first_name,last_name,contact_no,district_id) VALUES
('Mr','Nimal','Perera','0771234567',5),('Mrs','Kumari','Silva','0712345678',7),('Dr','Ravi','Fernando','0753456789',11);
-- Sample items
INSERT INTO items (item_code,item_name,category_id,sub_category_id,quantity,unit_price) VALUES
('E001','Smart Phone X',1,1,25,85000.00),('E002','USB Cable',1,2,100,850.00),
('S001','Exercise Book',2,3,500,180.00),('S002','Blue Pen',2,4,1000,40.00),('G001','Orange Juice',3,5,200,250.00);
-- Sample invoices and their lines
INSERT INTO invoices (invoice_no,invoice_date,customer_id,amount) VALUES
('INV-001','2026-10-01',1,85850.00),('INV-002','2026-10-05',2,1080.00),('INV-003','2026-10-10',3,500.00);
INSERT INTO invoice_items (invoice_id,item_id,quantity,unit_price) VALUES
(1,1,1,85000.00),(1,2,1,850.00),(2,3,6,180.00),(3,5,2,250.00);
