<?php
/*
 * Shared top part of every page: HTML head, Bootstrap CSS and the navigation bar
 * Each page includes this so the menu looks the same everywhere
 * $base is the path prefix to the project root
 */
require_once __DIR__ . '/../config.php'; $base = $base ?? ''; ?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mini ERP</title>
<!-- Bootstrap 5 loaded from a CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= $base ?>assets/style.css" rel="stylesheet">
</head><body>
<!-- Top navigation bar with links to all modules -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4"><!-- Main page content starts here; footer.php closes this container -->
<div class="container">
<a class="navbar-brand" href="<?= $base ?>index.php">Mini ERP</a>
<div class="navbar-nav">
<a class="nav-link" href="<?= $base ?>customers.php">Customers</a>
<a class="nav-link" href="<?= $base ?>items.php">Items</a>
<a class="nav-link" href="<?= $base ?>reports/invoice_report.php">Invoice Report</a>
<a class="nav-link" href="<?= $base ?>reports/invoice_item_report.php">Invoice Item Report</a>
<a class="nav-link" href="<?= $base ?>reports/item_report.php">Item Report</a>
</div></div></nav>
<div class="container">
