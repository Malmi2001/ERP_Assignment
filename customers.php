<?php
/*
 * Register, view, edit, delete and search customers on a single page
 *   1. Handle form submissions (POST): save / update / delete
 *   2. Load a customer into the form when "Edit" is clicked (GET ?edit=ID)
 *   3. Load the customer list (with optional search)
 *   4. Print the HTML form and table
 */
require_once 'config.php';

$errors = [];                       // error messages shown in red
$msg = '';                          // success message shown in green
$titles = ['Mr','Mrs','Miss','Dr']; // allowed titles from the assignment
// Districts come from the database so the dropdown is always up to date
$districts = $pdo->query('SELECT * FROM districts ORDER BY name')->fetchAll();
// $edit holds the values shown in the form (empty for a new customer)
$edit = ['id'=>'','title'=>'','first_name'=>'','last_name'=>'','contact_no'=>'','district_id'=>''];

// ---- Step 1: the form was submitted ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        // Delete button clicked. A customer with invoices can't be deleted
        // (foreign key),  catch the error and show a friendly message.
        try { $pdo->prepare('DELETE FROM customers WHERE id=?')->execute([(int)$_POST['id']]); $msg = 'Customer deleted.'; }
        catch (PDOException $e) { $errors[] = 'Cannot delete: this customer has invoices.'; }
    } else {
        // Save/Update button clicked: clean the input (trim the spaces) and validate it
        $edit = array_merge($edit, array_map('trim', array_intersect_key($_POST, $edit)));
        if (!in_array($edit['title'], $titles, true)) $errors[] = 'Please select a title.';
        if (!preg_match("/^[A-Za-z .'-]{1,50}$/", $edit['first_name'])) $errors[] = 'First name is required (letters only).';
        if (!preg_match("/^[A-Za-z .'-]{1,50}$/", $edit['last_name'])) $errors[] = 'Last name is required (letters only).';
        if (!preg_match('/^0\d{9}$/', $edit['contact_no'])) $errors[] = 'Contact number must be 10 digits starting with 0.';
        if (!in_array((int)$edit['district_id'], array_column($districts, 'id'))) $errors[] = 'Please select a district.';
        // Only touch the database when there are no validation errors
        if (!$errors) {
            $p = [$edit['title'],$edit['first_name'],$edit['last_name'],$edit['contact_no'],(int)$edit['district_id']];
            // Hidden id present = editing,  UPDATE; otherwise INSERT a new row.
            // Prepared statements (the ? marks) protect against from SQL injection.
            if ($edit['id']) { $p[] = (int)$edit['id'];
                $pdo->prepare('UPDATE customers SET title=?,first_name=?,last_name=?,contact_no=?,district_id=? WHERE id=?')->execute($p); $msg = 'Customer updated.';
            } else {
                $pdo->prepare('INSERT INTO customers (title,first_name,last_name,contact_no,district_id) VALUES (?,?,?,?,?)')->execute($p); $msg = 'Customer registered.';
            }
            // Clear the form after a successful save
            $edit = array_fill_keys(array_keys($edit), '');
        }
    }
} elseif (isset($_GET['edit'])) {
    // ---- Step 2: Edit link clicked,  fetch that customer and fill the form ----
    $st = $pdo->prepare('SELECT * FROM customers WHERE id=?'); $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: $edit;
}

// ---- Step 3: load the list. The search box text is matched against name, contact and district ----
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare("SELECT c.*, d.name AS district FROM customers c JOIN districts d ON d.id=c.district_id
  WHERE CONCAT(c.first_name,' ',c.last_name,' ',c.contact_no,' ',d.name) LIKE ? ORDER BY c.id DESC");
$st->execute(["%$q%"]); $rows = $st->fetchAll();
include 'includes/header.php';
// ---- Step 4: page output (the HTML) starts here ----
?>
<h3>Customers</h3>
<?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>

<!-- Register / Edit form. h() escapes values so special characters are safe -->
<div class="card mb-4"><div class="card-body">
<h5><?= $edit['id'] ? 'Edit Customer' : 'Register Customer' ?></h5>
<form method="post" class="row g-3 needs-validation" novalidate>
  <input type="hidden" name="id" value="<?= h($edit['id']) ?>">
  <div class="col-md-2"><label class="form-label">Title</label>
    <select name="title" class="form-select" required><option value="">Select</option>
    <?php foreach ($titles as $t) echo '<option '.($edit['title']===$t?'selected':'').'>'.$t.'</option>'; ?></select>
    <div class="invalid-feedback">Select a title.</div></div>
  <div class="col-md-3"><label class="form-label">First name</label>
    <input name="first_name" class="form-control" required maxlength="50" pattern="[A-Za-z .'\-]+" value="<?= h($edit['first_name']) ?>">
    <div class="invalid-feedback">Letters only, required.</div></div>
  <div class="col-md-3"><label class="form-label">Last name</label>
    <input name="last_name" class="form-control" required maxlength="50" pattern="[A-Za-z .'\-]+" value="<?= h($edit['last_name']) ?>">
    <div class="invalid-feedback">Letters only, required.</div></div>
  <div class="col-md-2"><label class="form-label">Contact number</label>
    <input name="contact_no" class="form-control" required pattern="0[0-9]{9}" maxlength="10" value="<?= h($edit['contact_no']) ?>">
    <div class="invalid-feedback">10 digits, starts with 0.</div></div>
  <div class="col-md-2"><label class="form-label">District</label>
    <select name="district_id" class="form-select" required><option value="">Select</option>
    <?php foreach ($districts as $d) echo '<option value="'.$d['id'].'" '.($edit['district_id']==$d['id']?'selected':'').'>'.h($d['name']).'</option>'; ?></select>
    <div class="invalid-feedback">Select a district.</div></div>
  <div class="col-12">
    <button class="btn btn-primary"><?= $edit['id'] ? 'Update' : 'Save' ?></button>
    <?php if ($edit['id']): ?><a href="customers.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
  </div>
</form></div></div>

<!-- Search box (GET so the search can be bookmarked) -->
<form class="input-group mb-3" method="get"><input name="q" class="form-control" placeholder="Search name, contact, district" value="<?= h($q) ?>">
<button class="btn btn-outline-primary">Search</button></form>

<!-- Customer list with Edit and Delete buttons -->
<table class="table table-striped bg-white"><thead><tr><th>#</th><th>Name</th><th>Contact</th><th>District</th><th style="width:150px">Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= $r['id'] ?></td><td><?= h("{$r['title']} {$r['first_name']} {$r['last_name']}") ?></td><td><?= h($r['contact_no']) ?></td><td><?= h($r['district']) ?></td>
<td><a class="btn btn-sm btn-warning" href="?edit=<?= $r['id'] ?>">Edit</a>
<form method="post" class="d-inline" onsubmit="return confirm('Delete this customer?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="5" class="text-center text-muted">No customers found.</td></tr>'; ?>
</tbody></table>
<script src="assets/validate.js"></script>
<?php include 'includes/footer.php'; ?>
