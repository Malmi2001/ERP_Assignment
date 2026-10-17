<?php
/*
 * Shows Item name, Category, Sub category and Quantity.
 * The assignment says the item name must not repeat, so I GROUP BY
 * item name and add up the quantities if the same name appears twice.
 */
$base = '../';
require_once __DIR__ . '/../config.php'; // database connection
// GROUP BY item name = no repeated names. SUM() adds the quantities together.
$rows = $pdo->query("SELECT i.item_name, MIN(c.name) AS category, MIN(s.name) AS sub_category, SUM(i.quantity) AS qty
    FROM items i JOIN item_categories c ON c.id=i.category_id JOIN item_sub_categories s ON s.id=i.sub_category_id
    GROUP BY i.item_name ORDER BY i.item_name")->fetchAll();
include '../includes/header.php';
?>
<h3>Item Report</h3>
<table class="table table-striped bg-white"><thead><tr><th>Item name</th><th>Category</th><th>Sub category</th><th class="text-end">Quantity</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= h($r['item_name']) ?></td><td><?= h($r['category']) ?></td><td><?= h($r['sub_category']) ?></td><td class="text-end"><?= (int)$r['qty'] ?></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="4" class="text-center text-muted">No items.</td></tr>'; ?>
</tbody></table>
<?php include '../includes/footer.php'; ?>
