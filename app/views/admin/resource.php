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
$rows = Database::all("SELECT * FROM $table ORDER BY " . $res['order']);
?>
<div class="ph">
  <div class="phl">
    <div class="pey">Content</div>
    <h1 class="pt"><?= esc($res['label']) ?></h1>
    <p class="ps"><?= count($rows) ?> record<?= count($rows) === 1 ? '' : 's' ?></p>
  </div>
  <div class="pa"><a href="/admin.php?p=<?= esc($p) ?>&new=1" class="btn btn-gold">+ New <?= esc($res['singular']) ?></a></div>
</div>

<div class="card">
  <div style="overflow-x:auto">
  <table class="dt">
    <thead><tr>
      <?php foreach ($res['list_columns'] as $col => $label): ?><th><?= esc($label) ?></th><?php endforeach; ?>
      <th style="text-align:right">Actions</th>
    </tr></thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="<?= count($res['list_columns']) + 1 ?>" style="text-align:center;color:#8a95a7;padding:28px">No records yet. Create your first <?= esc(strtolower($res['singular'])) ?>.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
        <?php foreach ($res['list_columns'] as $col => $label): $v = $r[$col] ?? ''; ?>
          <td>
          <?php if (in_array($col, ['is_active', 'featured', 'published'], true)): ?>
            <?php if ($v): ?><span class="bdg bdg-ok"><span class="dot5" style="background:#4ade80"></span>Yes</span><?php else: ?><span class="bdg bdg-off">No</span><?php endif; ?>
          <?php elseif ($col === 'status'): ?>
            <span class="bdg bdg-ed"><?= esc($v) ?></span>
          <?php elseif ($col === 'rating'): ?>
            <?= str_repeat('★', max(0, min(5, (int) $v))) ?>
          <?php else: ?>
            <span<?= in_array($col, ['title','full_name','client_name','reference_number'], true) ? ' style="font-weight:700"' : '' ?>><?= esc(mb_strimwidth((string) $v, 0, 60, '…')) ?></span>
          <?php endif; ?>
          </td>
        <?php endforeach; ?>
        <td>
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
</div>
<?php endif; ?>
