<?php
/*
 * Lists every item sold on invoices between two selected dates. 
 * Columns:Invoice number, Invoiced date, Customer name, Item name with code,Item category, Item unit price (the price on the invoice line).
 */
$base = '../'; // this file is inside /reports, so go one folder up for links require_once '../config.php';
// Date range from the search form (defaults to the current month)
$from = $_GET['from'] ?? date('Y-m-01'); $to = $_GET['to'] ?? date('Y-m-d');
$rows = [];
// One row per invoice line for join invoices, customers, items and categories
// to get all the names needed for the report.
if ($from && $to && $from <= $to) {
    $st = $pdo->prepare("SELECT i.invoice_no, i.invoice_date, CONCAT(c.title,' ',c.first_name,' ',c.last_name) AS customer,
        CONCAT(it.item_name,' (',it.item_code,')') AS item, cat.name AS category, ii.unit_price
        FROM invoice_items ii JOIN invoices i ON i.id=ii.invoice_id JOIN customers c ON c.id=i.customer_id
        JOIN items it ON it.id=ii.item_id JOIN item_categories cat ON cat.id=it.category_id
        WHERE i.invoice_date BETWEEN ? AND ? ORDER BY i.invoice_date, i.invoice_no");
    $st->execute([$from, $to]); $rows = $st->fetchAll();
} else $err = 'From date must be on or before To date.';
include '../includes/header.php';
?>
<h3>Invoice Item Report</h3>
<?php if (!empty($err)) echo '<div class="alert alert-danger">'.h($err).'</div>'; ?>
<!-- Date range search form -->
<form class="row g-2 mb-3" method="get">
  <div class="col-auto"><input type="date" name="from" class="form-control" value="<?= h($from) ?>" required></div>
  <div class="col-auto"><input type="date" name="to" class="form-control" value="<?= h($to) ?>" required></div>
  <div class="col-auto"><button class="btn btn-primary">Search</button></div>
</form>
<table class="table table-striped bg-white"><thead><tr><th>Invoice No</th><th>Invoiced date</th><th>Customer</th><th>Item (code)</th><th>Category</th><th class="text-end">Unit price</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= h($r['invoice_no']) ?></td><td><?= h($r['invoice_date']) ?></td><td><?= h($r['customer']) ?></td><td><?= h($r['item']) ?></td><td><?= h($r['category']) ?></td><td class="text-end"><?= number_format($r['unit_price'],2) ?></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="6" class="text-center text-muted">No data in this range.</td></tr>'; ?>
</tbody></table>
<?php include '../includes/footer.php'; ?>
