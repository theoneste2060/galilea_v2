<?php
$q = trim(input('q'));
$sourceFilter = trim(input('source'));
$sources = Database::all('SELECT DISTINCT source FROM newsletter_subscribers WHERE source != "" ORDER BY source');

$where = '';
$params = [];
$clauses = [];
if ($q !== '') {
    $searchCols = ['email', 'source', 'full_name'];
    foreach ($searchCols as $c) { $clauses[] = "$c LIKE ?"; $params[] = '%' . $q . '%'; }
}
if ($sourceFilter !== '') {
    $clauses[] = 'source = ?';
    $params[] = $sourceFilter;
}
if ($clauses) {
    $where = ' WHERE ' . implode(' AND ', $clauses);
}
$total = (int) Database::value("SELECT COUNT(*) FROM newsletter_subscribers" . $where, $params);
$rows = Database::all("SELECT * FROM newsletter_subscribers" . $where . " ORDER BY created_at DESC", $params);
?>
<div class="ph">
  <div class="phl">
    <div class="pey">Engagement</div>
    <h1 class="pt">Newsletter Subscribers</h1>
    <p class="ps"><?= $total ?> subscriber<?= $total === 1 ? '' : 's' ?><?= $q !== '' ? ' matching "' . esc($q) . '"' : '' ?>.</p>
  </div>
  <div class="pa" style="gap:8px;flex-wrap:wrap">
    <form method="get" class="list-search" role="search">
      <input type="hidden" name="p" value="subscribers">
      <input type="search" name="q" value="<?= esc($q) ?>" placeholder="Search subscribers…" aria-label="Search">
      <?php if ($sourceFilter !== ''): ?><input type="hidden" name="source" value="<?= esc($sourceFilter) ?>"><?php endif; ?>
      <?php if ($q !== ''): ?><a href="/admin.php?p=subscribers<?= $sourceFilter !== '' ? '&source=' . esc($sourceFilter) : '' ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <form method="get" class="list-search" role="search" style="width:auto">
      <input type="hidden" name="p" value="subscribers">
      <select name="source" class="fsel" onchange="this.form.submit()" aria-label="Filter by source" style="padding:7px 12px;font-size:12.5px;border:1px solid var(--line);border-radius:6px;font-family:var(--font-sans);min-width:140px">
        <option value="">All sources</option>
        <?php foreach ($sources as $s): $src = $s['source']; ?><option value="<?= esc($src) ?>"<?= $sourceFilter === $src ? ' selected' : '' ?>><?= esc($src) ?></option><?php endforeach; ?>
      </select>
      <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= esc($q) ?>"><?php endif; ?>
      <?php if ($sourceFilter !== ''): ?><a href="/admin.php?p=subscribers<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <?php if (($admin['role'] ?? '') === 'superadmin'): ?>
    <a href="/admin.php?action=export_subscribers" class="btn btn-ghost btn-sm" style="gap:5px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export CSV
    </a>
    <?php endif; ?>
  </div>
</div>

<form method="post" action="/admin.php?action=bulk_delete_subscribers" id="bulkForm">
  <?= csrf_field() ?>
  <div class="tablenav">
    <select class="bulk-select" id="bulkAction" name="bulk_action" aria-label="Bulk actions">
      <option value="-1">Bulk actions</option>
      <option value="delete">Delete</option>
    </select>
    <button type="submit" class="bulk-apply" id="bulkApply">Apply</button>
    <span class="bulk-count" id="bulkCount"></span>
  </div>
</form>
<div class="card">
  <div style="overflow-x:auto">
  <table class="dt">
    <thead><tr>
      <th class="col-check"><input type="checkbox" id="bulkAll" aria-label="Select all"></th>
      <th>Email</th><th>Source</th><th>Subscribed</th><th style="text-align:right">Actions</th>
    </tr></thead>
    <tbody>
      <?php if (!$rows): ?><tr><td colspan="5" style="text-align:center;color:#8a95a7;padding:28px"><?= $q !== '' || $sourceFilter !== '' ? 'No matches for the current filters.' : 'No subscribers yet.' ?></td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td class="col-check"><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" form="bulkForm" class="row-check" aria-label="Select row"></td>
        <td style="font-weight:700"><?= esc($r['email']) ?>
          <?php if ($r['full_name']): ?><div style="font-weight:400;color:#8a95a7;font-size:11px"><?= esc($r['full_name']) ?></div><?php endif; ?>
        </td>
        <td><span class="bdg bdg-vw"><?= esc($r['source']) ?></span></td>
        <td style="color:#8a95a7"><?= esc(date('M j, Y', strtotime($r['created_at']))) ?></td>
        <td>
          <div class="ibg" style="justify-content:flex-end">
            <form method="post" action="/admin.php?action=subscriber_delete" onsubmit="return confirm('Remove this subscriber?')" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="ib del" type="submit" title="Remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
