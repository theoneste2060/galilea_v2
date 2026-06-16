<?php
require_role('superadmin');
$counts = [
    'inquiries' => (int) Database::value('SELECT COUNT(*) FROM inquiries'),
    'subscribers' => (int) Database::value('SELECT COUNT(*) FROM newsletter_subscribers'),
];
$dbPath = config('db_path');
$dbSize = is_file($dbPath) ? round(filesize($dbPath) / 1024, 1) . ' KB' : 'n/a';
?>
<div class="ph">
  <div class="phl"><div class="pey">System</div><h1 class="pt">Backup &amp; Export</h1><p class="ps">Download a full database snapshot or export data as CSV.</p></div>
</div>

<div class="g2">
  <div class="card">
    <div class="ch"><div><div class="ct">Database Backup</div><div class="cs">Consistent SQLite snapshot · current size <?= esc($dbSize) ?></div></div></div>
    <div class="cb">
      <p style="font-size:12.5px;color:#5A6478;margin-bottom:14px">Downloads a complete copy of the live database. Store it somewhere safe. To restore, replace <code>data/galilea.sqlite</code> on the server with the downloaded file.</p>
      <form method="post" action="/admin.php?action=backup_db">
        <?= csrf_field() ?>
        <button class="btn btn-navy" type="submit">Download .sqlite backup</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="ch"><div><div class="ct">CSV Exports</div><div class="cs">Spreadsheet-friendly data</div></div></div>
    <div class="cb">
      <div style="display:flex;flex-direction:column;gap:10px">
        <form method="post" action="/admin.php?action=export_inquiries" style="display:flex;align-items:center;justify-content:space-between;gap:10px">
          <?= csrf_field() ?>
          <span style="font-size:13px;font-weight:600;color:#0D2645">Inquiries <span class="bdg bdg-vw"><?= $counts['inquiries'] ?></span></span>
          <button class="btn btn-exp" type="submit">Export CSV</button>
        </form>
        <form method="post" action="/admin.php?action=export_subscribers" style="display:flex;align-items:center;justify-content:space-between;gap:10px">
          <?= csrf_field() ?>
          <span style="font-size:13px;font-weight:600;color:#0D2645">Subscribers <span class="bdg bdg-vw"><?= $counts['subscribers'] ?></span></span>
          <button class="btn btn-exp" type="submit">Export CSV</button>
        </form>
      </div>
    </div>
  </div>
</div>
