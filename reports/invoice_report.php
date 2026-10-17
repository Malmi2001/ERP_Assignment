<?php
/*
 * Lists invoices between two selected dates.
 *  Columns:Invoice number, Date, Customer, Customer district, Item count, Invoice amount.
 * A total of all invoice amounts is shown at the bottom.
 */
$base = '../'; 
require_once '../config.php';
// Date range from the search form (defaults to the current month)
$from = $_GET['from'] ?? date('Y-m-01'); $to = $_GET['to'] ?? date('Y-m-d');
$rows = [];
// Only search when both dates exist and From is not after To.
// Item count = number of item lines on each invoice (sub query).
if ($from && $to && $from <= $to) {
    $st = $pdo->prepare("SELECT i.invoice_no, i.invoice_date, CONCAT(c.title,' ',c.first_name,' ',c.last_name) AS customer, d.name AS district,
        (SELECT COUNT(*) FROM invoice_items ii WHERE ii.invoice_id=i.id) AS item_count, i.amount
        FROM invoices i JOIN customers c ON c.id=i.customer_id JOIN districts d ON d.id=c.district_id
        WHERE i.invoice_date BETWEEN ? AND ? ORDER BY i.invoice_date, i.invoice_no");
    $st->execute([$from, $to]); $rows = $st->fetchAll();
} else $err = 'From date must be on or before To date.';
include '../includes/header.php';
?>
<h3>Invoice Report</h3>
<?php if (!empty($err)) echo '<div class="alert alert-danger">'.h($err).'</div>'; ?>
<!-- Date range search form -->
<form class="row g-2 mb-3" method="get">
  <div class="col-auto"><input type="date" name="from" class="form-control" value="<?= h($from) ?>" required></div>
  <div class="col-auto"><input type="date" name="to" class="form-control" value="<?= h($to) ?>" required></div>
  <div class="col-auto"><button class="btn btn-primary">Search</button></div>
</form>
<table class="table table-striped bg-white"><thead><tr><th>Invoice No</th><th>Date</th><th>Customer</th><th>District</th><th class="text-end">Item count</th><th class="text-end">Amount</th></tr></thead><tbody>
<?php $tot = 0; // running total of the amount column
 foreach ($rows as $r): $tot += $r['amount']; ?>
<tr><td><?= h($r['invoice_no']) ?></td><td><?= h($r['invoice_date']) ?></td><td><?= h($r['customer']) ?></td><td><?= h($r['district']) ?></td>
<td class="text-end"><?= (int)$r['item_count'] ?></td><td class="text-end"><?= number_format($r['amount'],2) ?></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="6" class="text-center text-muted">No invoices in this range.</td></tr>'; ?>
</tbody><tfoot><tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end"><?= number_format($tot,2) ?></td></tr></tfoot></table>
<?php include '../includes/footer.php'; ?>
