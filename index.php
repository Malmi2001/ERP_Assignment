<?php
/*
 * index.php
 * ---------
 * Home page / dashboard. Shows how many customers, items and invoices
 * are in the database, and each box links to the related page.
 */
include 'includes/header.php';
?>
<h3>Dashboard</h3>
<div class="row g-3 mt-1">
<?php // Each entry: box label => [table name, page to open]
foreach (['Customers'=>['customers','customers.php'],'Items'=>['items','items.php'],'Invoices'=>['invoices','reports/invoice_report.php']] as $label=>[$t,$link]):
  // Count the rows in the table (table names here are fixed, not user input)
  $n = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn(); ?>
  <div class="col-md-4"><a href="<?= $link ?>" class="text-decoration-none"><div class="card p-4 text-center">
    <div class="display-5"><?= (int)$n ?></div><div class="text-muted"><?= $label ?></div></div></a></div>
<?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
