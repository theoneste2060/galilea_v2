<?php
/** @var string $p @var array $resources */
$res   = $resources[$p];
$table = $res['table'];

$editId = (int) input('edit');
$isNew  = input('new') === '1';
$row    = null;
if ($editId) {
    $row = Database::one("SELECT * FROM $table WHERE id = ?", [$editId]);
    if (!$row) { flash('Record not found.', 'error'); redirect('/admin.php?p=' . $p); }
}

/* ───────────────────────────── Edit / New form ───────────────────────── */
if ($editId || $isNew):
?>
<div class="ph">
  <div class="phl">
    <div class="pey">Manage</div>
    <h1 class="pt"><?= $editId ? 'Edit' : 'New' ?> <?= esc($res['singular']) ?></h1>
    <p class="ps"><?= esc($res['label']) ?></p>
  </div>
  <div class="pa"><a href="/admin.php?p=<?= esc($p) ?>" class="btn btn-ghost">← Back to list</a></div>
</div>

<div class="card">
  <div class="cb">
    <form method="post" action="/admin.php?action=save&resource=<?= esc($p) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <?php if ($editId): ?><input type="hidden" name="id" value="<?= (int) $editId ?>"><?php endif; ?>
      <?php foreach ($res['fields'] as $name => $field): ?>
        <?= render_field($name, $field, $row[$name] ?? null) ?>
      <?php endforeach; ?>
      <div class="mf" style="border-top:none;padding-top:6px">
        <a href="/admin.php?p=<?= esc($p) ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-navy">Save <?= esc($res['singular']) ?></button>
      </div>
    </form>
  </div>
</div>

<?php
/* ────────────────────────────────── List ─────────────────────────────── */
else:
$q = trim(input('q'));
$pg = max(1, (int) input('pg'));
$perPage = 20;
$hasSort = isset($res['fields']['sort_order']);

// Searchable text columns (skip booleans/ids/status/order).
$skip = ['is_active','featured','published','parent_id','sort_order','status','rating'];
$searchCols = array_values(array_diff(array_keys($res['list_columns']), $skip));

$where = '';
$params = [];
if ($q !== '' && $searchCols) {
    $clauses = [];
    foreach ($searchCols as $c) { $clauses[] = "$c LIKE ?"; $params[] = '%' . $q . '%'; }
    $where = ' WHERE (' . implode(' OR ', $clauses) . ')';
}
$total = (int) Database::value("SELECT COUNT(*) FROM $table" . $where, $params);
$pages = max(1, (int) ceil($total / $perPage));
$pg = min($pg, $pages);
$offset = ($pg - 1) * $perPage;
// Drag-reorder only when viewing the full, unfiltered, sort-ordered first page.
$canReorder = $hasSort && $q === '' && $total <= $perPage;
$rows = Database::all("SELECT * FROM $table" . $where . ' ORDER BY ' . $res['order'] . " LIMIT $perPage OFFSET $offset", $params);
?>
<div class="ph">
  <div class="phl">
    <div class="pey">Content</div>
    <h1 class="pt"><?= esc($res['label']) ?></h1>
    <p class="ps"><?= $total ?> record<?= $total === 1 ? '' : 's' ?><?= $q !== '' ? ' matching “' . esc($q) . '”' : '' ?></p>
  </div>
  <div class="pa">
    <?php if ($searchCols): ?>
    <form method="get" class="list-search" role="search">
      <input type="hidden" name="p" value="<?= esc($p) ?>">
      <input type="search" name="q" value="<?= esc($q) ?>" placeholder="Search <?= esc(strtolower($res['label'])) ?>…" aria-label="Search">
      <?php if ($q !== ''): ?><a href="/admin.php?p=<?= esc($p) ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <?php endif; ?>
    <a href="/admin.php?p=<?= esc($p) ?>&new=1" class="btn btn-gold">+ New <?= esc($res['singular']) ?></a>
  </div>
</div>

<form method="post" action="/admin.php?action=bulk_delete&resource=<?= esc($p) ?>" id="bulkForm" onsubmit="return confirm('Delete the selected items? This cannot be undone.');">
  <?= csrf_field() ?>
  <div class="bulk-bar" id="bulkBar" hidden>
    <span id="bulkCount">0 selected</span>
    <button type="submit" class="btn btn-danger btn-sm">Delete selected</button>
  </div>
</form>
<div class="card">
  <div class="table-scroll">
  <table class="dt"<?= $canReorder ? ' data-reorder="' . esc($p) . '"' : '' ?>>
    <thead><tr>
      <th class="col-check"><input type="checkbox" id="bulkAll" aria-label="Select all"></th>
      <?php if ($canReorder): ?><th class="col-drag" aria-label="Reorder"></th><?php endif; ?>
      <?php foreach ($res['list_columns'] as $col => $label): ?><th><?= esc($label) ?></th><?php endforeach; ?>
      <th style="text-align:right">Actions</th>
    </tr></thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="<?= count($res['list_columns']) + ($canReorder ? 3 : 2) ?>" style="text-align:center;color:#8a95a7;padding:28px"><?= $q !== '' ? 'No matches for “' . esc($q) . '”.' : 'No records yet. Create your first ' . esc(strtolower($res['singular'])) . '.' ?></td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr data-id="<?= (int) $r['id'] ?>"<?= $canReorder ? ' draggable="true"' : '' ?>>
        <td class="col-check"><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" form="bulkForm" class="row-check" aria-label="Select row"></td>
        <?php if ($canReorder): ?><td class="col-drag" title="Drag to reorder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="18" r="1"/></svg></td><?php endif; ?>
        <?php foreach ($res['list_columns'] as $col => $label): $v = $r[$col] ?? ''; ?>
          <td data-label="<?= esc($label) ?>">
          <?php if (in_array($col, ['is_active', 'featured', 'published'], true)): ?>
            <?php if ($v): ?><span class="bdg bdg-ok"><span class="dot5" style="background:#4ade80"></span>Yes</span><?php else: ?><span class="bdg bdg-off">No</span><?php endif; ?>
          <?php elseif ($col === 'parent_id'): ?>
            <?php if ($v): ?><span class="bdg bdg-vw">sub-item</span><?php else: ?><span class="bdg bdg-adm">top-level</span><?php endif; ?>
          <?php elseif ($col === 'status'): ?>
            <span class="bdg bdg-ed"><?= esc($v) ?></span>
          <?php elseif ($col === 'rating'): ?>
            <?= str_repeat('★', max(0, min(5, (int) $v))) ?>
          <?php else: ?>
            <span<?= in_array($col, ['title','full_name','client_name','reference_number'], true) ? ' style="font-weight:700"' : '' ?>><?= esc(mb_strimwidth((string) $v, 0, 60, '…')) ?></span>
          <?php endif; ?>
          </td>
        <?php endforeach; ?>
        <td data-label="Actions">
          <div class="ibg" style="justify-content:flex-end">
            <a href="/admin.php?p=<?= esc($p) ?>&edit=<?= (int) $r['id'] ?>" class="ib edit" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
            <form method="post" action="/admin.php?action=delete&resource=<?= esc($p) ?>" onsubmit="return confirm('Delete this <?= esc(strtolower($res['singular'])) ?>? This cannot be undone.');" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="ib del" title="Delete" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php if ($pages > 1): ?>
  <div class="admin-pager">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a href="/admin.php?p=<?= esc($p) ?>&pg=<?= $i ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="apg<?= $i === $pg ? ' active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
