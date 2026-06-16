<?php
$rows = Database::all('SELECT * FROM inquiries ORDER BY created_at DESC');
$statuses = ['new', 'in-progress', 'quoted', 'closed'];
?>
<div class="ph">
  <div class="phl"><div class="pey">Engagement</div><h1 class="pt">Inquiries</h1><p class="ps"><?= count($rows) ?> quote request<?= count($rows) === 1 ? '' : 's' ?> from the website.</p></div>
</div>

<div class="card">
  <div style="overflow-x:auto">
  <table class="dt">
    <thead><tr><th>Contact</th><th>Interest</th><th>Message</th><th>Status</th><th>Received</th><th style="text-align:right">Actions</th></tr></thead>
    <tbody>
      <?php if (!$rows): ?><tr><td colspan="6" style="text-align:center;color:#8a95a7;padding:28px">No inquiries yet.</td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
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
          <form method="post" action="/admin.php?action=inquiry_delete" onsubmit="return confirm('Delete this inquiry?')" style="text-align:right">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button class="ib del" type="submit" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
