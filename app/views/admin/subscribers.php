<?php
$rows = Database::all('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC');
?>
<div class="ph">
  <div class="phl"><div class="pey">Engagement</div><h1 class="pt">Newsletter Subscribers</h1><p class="ps"><?= count($rows) ?> subscriber<?= count($rows) === 1 ? '' : 's' ?>.</p></div>
</div>

<div class="card">
  <div style="overflow-x:auto">
  <table class="dt">
    <thead><tr><th>Email</th><th>Source</th><th>Subscribed</th><th style="text-align:right">Actions</th></tr></thead>
    <tbody>
      <?php if (!$rows): ?><tr><td colspan="4" style="text-align:center;color:#8a95a7;padding:28px">No subscribers yet.</td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td style="font-weight:700"><?= esc($r['email']) ?></td>
        <td><span class="bdg bdg-vw"><?= esc($r['source']) ?></span></td>
        <td style="color:#8a95a7"><?= esc(date('M j, Y', strtotime($r['created_at']))) ?></td>
        <td>
          <form method="post" action="/admin.php?action=subscriber_delete" onsubmit="return confirm('Remove this subscriber?')" style="text-align:right">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button class="ib del" type="submit" title="Remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
