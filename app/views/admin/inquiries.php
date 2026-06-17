<?php
$q = trim(input('q'));
$statusFilter = trim(input('status'));
$statuses = ['new', 'in-progress', 'quoted', 'closed'];

$where = '';
$params = [];
$clauses = [];
if ($q !== '') {
    $searchCols = ['full_name', 'email', 'phone', 'company', 'service_interest', 'message'];
    foreach ($searchCols as $c) { $clauses[] = "$c LIKE ?"; $params[] = '%' . $q . '%'; }
}
if ($statusFilter !== '' && in_array($statusFilter, $statuses, true)) {
    $clauses[] = 'status = ?';
    $params[] = $statusFilter;
}
if ($clauses) {
    $where = ' WHERE ' . implode(' AND ', $clauses);
}
$total = (int) Database::value("SELECT COUNT(*) FROM inquiries" . $where, $params);
$rows = Database::all("SELECT * FROM inquiries" . $where . " ORDER BY created_at DESC", $params);
?>
<div class="ph">
  <div class="phl">
    <div class="pey">Engagement</div>
    <h1 class="pt">Inquiries</h1>
    <p class="ps"><?= $total ?> quote request<?= $total === 1 ? '' : 's' ?><?= $q !== '' ? ' matching "' . esc($q) . '"' : '' ?> from the website.</p>
  </div>
  <div class="pa" style="gap:8px;flex-wrap:wrap">
    <form method="get" class="list-search" role="search">
      <input type="hidden" name="p" value="inquiries">
      <input type="search" name="q" value="<?= esc($q) ?>" placeholder="Search inquiries…" aria-label="Search">
      <?php if ($statusFilter !== ''): ?><input type="hidden" name="status" value="<?= esc($statusFilter) ?>"><?php endif; ?>
      <?php if ($q !== ''): ?><a href="/admin.php?p=inquiries<?= $statusFilter !== '' ? '&status=' . esc($statusFilter) : '' ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <form method="get" class="list-search" role="search" style="width:auto">
      <input type="hidden" name="p" value="inquiries">
      <select name="status" class="fsel" onchange="this.form.submit()" aria-label="Filter by status" style="padding:7px 12px;font-size:12.5px;border:1px solid var(--line);border-radius:6px;font-family:var(--font-sans);min-width:140px">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $st): ?><option value="<?= $st ?>"<?= $statusFilter === $st ? ' selected' : '' ?>><?= esc(ucfirst($st)) ?></option><?php endforeach; ?>
      </select>
      <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= esc($q) ?>"><?php endif; ?>
      <?php if ($statusFilter !== ''): ?><a href="/admin.php?p=inquiries<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <?php if (($admin['role'] ?? '') === 'superadmin'): ?>
    <a href="/admin.php?action=export_inquiries" class="btn btn-ghost btn-sm" style="gap:5px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export CSV
    </a>
    <?php endif; ?>
  </div>
</div>

<form method="post" action="/admin.php?action=bulk_delete_inquiries" id="bulkForm">
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
      <th>Contact</th><th>Interest</th><th>Message</th><th>Status</th><th>Received</th><th style="text-align:right">Actions</th>
    </tr></thead>
    <tbody>
      <?php if (!$rows): ?><tr><td colspan="7" style="text-align:center;color:#8a95a7;padding:28px"><?= $q !== '' || $statusFilter !== '' ? 'No matches for the current filters.' : 'No inquiries yet.' ?></td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td class="col-check"><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" form="bulkForm" class="row-check" aria-label="Select row"></td>
        <td style="font-weight:700"><?= esc($r['full_name']) ?>
          <div style="font-weight:400;color:#8a95a7;font-size:11px"><a href="mailto:<?= esc($r['email']) ?>" style="color:#0D2645"><?= esc($r['email']) ?></a><?= $r['phone'] ? ' · ' . esc($r['phone']) : '' ?></div>
          <?php if ($r['company']): ?><div style="font-weight:400;color:#8a95a7;font-size:11px"><?= esc($r['company']) ?></div><?php endif; ?>
        </td>
        <td><?= esc($r['service_interest']) ?></td>
        <td style="max-width:280px"><span style="color:#5A6478"><?= esc(mb_strimwidth($r['message'], 0, 120, '…')) ?></span></td>
        <td>
          <form method="post" action="/admin.php?action=inquiry_status" style="display:flex;gap:5px">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <select name="status" class="fsel" style="padding:5px 8px;font-size:11px;width:auto" onchange="this.form.submit()">
              <?php foreach ($statuses as $st): ?><option value="<?= $st ?>"<?= $r['status'] === $st ? ' selected' : '' ?>><?= esc($st) ?></option><?php endforeach; ?>
            </select>
          </form>
        </td>
        <td style="color:#8a95a7;white-space:nowrap"><?= esc(date('M j, Y', strtotime($r['created_at']))) ?></td>
        <td>
          <div class="ibg" style="justify-content:flex-end">
            <form method="post" action="/admin.php?action=inquiry_delete" onsubmit="return confirm('Delete this inquiry?')" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="ib del" type="submit" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
