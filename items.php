<?php
/*
 * Register, view, edit, delete and search items on a single page.
 * Same structure as customers.php:
 *   1. Handle POST (save / update / delete)
 *   2. Load an item for editing (GET ?edit=ID)
 *   3. Load the list with optional search
 *   4. Print the form and table
 * Extra feature: the Sub Category dropdown changes depending on the selected
 * Category it done with JavaScript at the bottom of this file.
 */
require_once 'config.php';

$errors = [];   // error messages shown in red
$msg = '';      // success message shown in green
// Load categories and sub categories for the two dropdowns
$cats = $pdo->query('SELECT * FROM item_categories ORDER BY name')->fetchAll();
$subs = $pdo->query('SELECT * FROM item_sub_categories ORDER BY name')->fetchAll();
// Values shown in the form (empty when adding a new item)
$edit = ['id'=>'','item_code'=>'','item_name'=>'','category_id'=>'','sub_category_id'=>'','quantity'=>'','unit_price'=>''];

// ---- Step 1: the form was submitted ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'delete') {
        // An item used in an invoice can't be deleted, catch that case
        try { $pdo->prepare('DELETE FROM items WHERE id=?')->execute([(int)$_POST['id']]); $msg = 'Item deleted.'; }
        catch (PDOException $e) { $errors[] = 'Cannot delete: this item is used in invoices.'; }
    } else {
        // Save/Update: trim the input then validate every field
        $edit = array_merge($edit, array_map('trim', array_intersect_key($_POST, $edit)));
        if (!preg_match('/^[A-Za-z0-9_-]{1,30}$/', $edit['item_code'])) $errors[] = 'Item code is required (letters, numbers, - or _).';
        if ($edit['item_name'] === '' || strlen($edit['item_name']) > 100) $errors[] = 'Item name is required (max 100 chars).';
        // The chosen sub category must really belong to the chosen category
        $okSub = false;
        foreach ($subs as $s) if ($s['id'] == $edit['sub_category_id'] && $s['category_id'] == $edit['category_id']) $okSub = true;
        if (!$okSub) $errors[] = 'Select a valid category and matching sub category.';
        if (!ctype_digit($edit['quantity'])) $errors[] = 'Quantity must be a whole number (0 or more).';
        if (!is_numeric($edit['unit_price']) || $edit['unit_price'] < 0) $errors[] = 'Unit price must be a positive number.';
        // Item code must be unique (ignore the item we are currently editing)
        if (!$errors) {
            $dup = $pdo->prepare('SELECT COUNT(*) FROM items WHERE item_code=? AND id<>?'); $dup->execute([$edit['item_code'], (int)$edit['id']]);
            if ($dup->fetchColumn()) $errors[] = 'Item code already exists.';
        }
        // Save only if everything is valid: UPDATE when editing, INSERT when new
        if (!$errors) {
            $p = [$edit['item_code'],$edit['item_name'],(int)$edit['category_id'],(int)$edit['sub_category_id'],(int)$edit['quantity'],$edit['unit_price']];
            if ($edit['id']) { $p[] = (int)$edit['id'];
                $pdo->prepare('UPDATE items SET item_code=?,item_name=?,category_id=?,sub_category_id=?,quantity=?,unit_price=? WHERE id=?')->execute($p); $msg = 'Item updated.';
            } else {
                $pdo->prepare('INSERT INTO items (item_code,item_name,category_id,sub_category_id,quantity,unit_price) VALUES (?,?,?,?,?,?)')->execute($p); $msg = 'Item registered.';
            }
            $edit = array_fill_keys(array_keys($edit), '');
        }
    }
} elseif (isset($_GET['edit'])) {
    // ---- Step 2: Edit clicked, so load that item into the form ----
    $st = $pdo->prepare('SELECT * FROM items WHERE id=?'); $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: $edit;
}

// ---- Step 3: item list. JOINs give us the category and sub category names.
// The search text is matched against code, name, category and sub category ----
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare("SELECT i.*, c.name AS category, s.name AS sub_category FROM items i
  JOIN item_categories c ON c.id=i.category_id JOIN item_sub_categories s ON s.id=i.sub_category_id
  WHERE CONCAT(i.item_code,' ',i.item_name,' ',c.name,' ',s.name) LIKE ? ORDER BY i.id DESC");
$st->execute(["%$q%"]); $rows = $st->fetchAll();
include 'includes/header.php';
// ---- Step 4: page output (the HTML) ----
?>
<h3>Items</h3>
<?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>

<!-- Register / Edit item form -->
<div class="card mb-4"><div class="card-body">
<h5><?= $edit['id'] ? 'Edit Item' : 'Register Item' ?></h5>
<form method="post" class="row g-3 needs-validation" novalidate>
  <input type="hidden" name="id" value="<?= h($edit['id']) ?>">
  <div class="col-md-2"><label class="form-label">Item code</label>
    <input name="item_code" class="form-control" required maxlength="30" pattern="[A-Za-z0-9_\-]+" value="<?= h($edit['item_code']) ?>">
    <div class="invalid-feedback">Required (letters/numbers).</div></div>
  <div class="col-md-3"><label class="form-label">Item name</label>
    <input name="item_name" class="form-control" required maxlength="100" value="<?= h($edit['item_name']) ?>">
    <div class="invalid-feedback">Required.</div></div>
  <div class="col-md-2"><label class="form-label">Category</label>
    <select name="category_id" id="category" class="form-select" required><option value="">Select</option>
    <?php foreach ($cats as $c) echo '<option value="'.$c['id'].'" '.($edit['category_id']==$c['id']?'selected':'').'>'.h($c['name']).'</option>'; ?></select>
    <div class="invalid-feedback">Select a category.</div></div>
  <div class="col-md-2"><label class="form-label">Sub category</label>
    <select name="sub_category_id" id="subcategory" class="form-select" required><option value="">Select</option></select>
    <div class="invalid-feedback">Select a sub category.</div></div>
  <div class="col-md-1"><label class="form-label">Qty</label>
    <input type="number" name="quantity" class="form-control" required min="0" step="1" value="<?= h($edit['quantity']) ?>">
    <div class="invalid-feedback">0 or more.</div></div>
  <div class="col-md-2"><label class="form-label">Unit price</label>
    <input type="number" name="unit_price" class="form-control" required min="0" step="0.01" value="<?= h($edit['unit_price']) ?>">
    <div class="invalid-feedback">Valid price.</div></div>
  <div class="col-12">
    <button class="btn btn-primary"><?= $edit['id'] ? 'Update' : 'Save' ?></button>
    <?php if ($edit['id']): ?><a href="items.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
  </div>
</form></div></div>

<!-- Search box -->
<form class="input-group mb-3" method="get"><input name="q" class="form-control" placeholder="Search code, name, category" value="<?= h($q) ?>">
<button class="btn btn-outline-primary">Search</button></form>

<!-- Item list with Edit and Delete buttons -->
<table class="table table-striped bg-white"><thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Sub category</th><th class="text-end">Qty</th><th class="text-end">Unit price</th><th style="width:150px">Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= h($r['item_code']) ?></td><td><?= h($r['item_name']) ?></td><td><?= h($r['category']) ?></td><td><?= h($r['sub_category']) ?></td>
<td class="text-end"><?= (int)$r['quantity'] ?></td><td class="text-end"><?= number_format($r['unit_price'],2) ?></td>
<td><a class="btn btn-sm btn-warning" href="?edit=<?= $r['id'] ?>">Edit</a>
<form method="post" class="d-inline" onsubmit="return confirm('Delete this item?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="text-center text-muted">No items found.</td></tr>'; ?>
</tbody></table>
<script>
// Dependent dropdown: show only the sub categories of the selected category
// PHP sends all sub categories as JSON; JavaScript filters them in the browser
// so the page doesn't need to reload when the category changes
const subs = <?= json_encode($subs) ?>, selected = "<?= h($edit['sub_category_id']) ?>";
const cat = document.getElementById('category'), sub = document.getElementById('subcategory');
// Rebuild the sub category list. When editing, re-select the saved value.
function fillSubs() {
  sub.innerHTML = '<option value="">Select</option>';
  subs.filter(s => s.category_id == cat.value).forEach(s => {
    const o = new Option(s.name, s.id); if (s.id == selected) o.selected = true; sub.add(o);
  });
}
cat.addEventListener('change', () => { fillSubs(); }); // run on every category change
fillSubs();                                           // run once when the page loads
</script>
<script src="assets/validate.js"></script>
<?php include 'includes/footer.php'; ?>
