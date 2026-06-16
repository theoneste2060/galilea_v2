<?php
$rows = Database::all('SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 200');
?>
<div class="ph">
  <div class="phl"><div class="pey">System</div><h1 class="pt">Activity Logs</h1><p class="ps">Most recent 200 admin events.</p></div>
</div>
<div class="card"><div style="overflow-x:auto">
  <table class="dt">
    <thead><tr><th>When</th><th>User</th><th>Action</th><th>Detail</th><th>IP</th></tr></thead>
    <tbody>
      <?php if (!$rows): ?><tr><td colspan="5" style="text-align:center;color:#8a95a7;padding:28px">No activity recorded.</td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td style="color:#8a95a7;white-space:nowrap"><?= esc($r['created_at']) ?></td>
        <td style="font-weight:700"><?= esc($r['username']) ?></td>
        <td><span class="bdg bdg-ed"><?= esc($r['action']) ?></span></td>
        <td><?= esc($r['detail']) ?></td>
        <td style="color:#8a95a7"><?= esc($r['ip']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
