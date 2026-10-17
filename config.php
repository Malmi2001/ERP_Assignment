<?php
/*
 * This file connects the whole project to the MySQL database.
 * Every other page includes this file first, so the connection 
 * is available everywhere without repeating the code
 */


// Database settings 
const DB_HOST = 'localhost';
const DB_NAME = 'erp_db';
const DB_USER = 'root';
const DB_PASS = '';

// Create the connection using PDO. ERRMODE_EXCEPTION makes MySQL errors
// throw exceptions so we can catch them instead of failing silently
// FETCH_ASSOC returns rows as simple arrays like $row['name']
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// Small helper to make text safe before printing it on a page.
// This protects the site from XSS 
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }



