<?php
$counts = [
    'services'     => (int) Database::value('SELECT COUNT(*) FROM services'),
    'news'         => (int) Database::value('SELECT COUNT(*) FROM news_posts'),
    'testimonials' => (int) Database::value('SELECT COUNT(*) FROM testimonials'),
    'team'         => (int) Database::value('SELECT COUNT(*) FROM team_members'),
    'shipments'    => (int) Database::value('SELECT COUNT(*) FROM shipments'),
    'subscribers'  => (int) Database::value('SELECT COUNT(*) FROM newsletter_subscribers'),
];
$inquiriesTotal = (int) Database::value('SELECT COUNT(*) FROM inquiries');
$inquiriesNew   = (int) Database::value("SELECT COUNT(*) FROM inquiries WHERE status = 'new'");
$recentInq = Database::all('SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 6');
$recentAct = Database::all('SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 8');

// Inquiries trend — last 14 days (zero-filled).
$days = [];
for ($i = 13; $i >= 0; $i--) { $days[date('Y-m-d', strtotime("-$i days"))] = 0; }
foreach (Database::all("SELECT date(created_at) d, COUNT(*) c FROM inquiries WHERE date(created_at) >= date('now','-13 days') GROUP BY date(created_at)") as $r) {
    if (isset($days[$r['d']])) { $days[$r['d']] = (int) $r['c']; }
}
$maxDay = max(1, max($days));
$trend14 = array_sum($days);

// Inquiry status breakdown.
$statusRows = Database::all('SELECT status, COUNT(*) c FROM inquiries GROUP BY status ORDER BY c DESC');
$statusMax = 1;
foreach ($statusRows as $s) { $statusMax = max($statusMax, (int) $s['c']); }
?>
<div class="ph">
  <div class="phl">
    <div class="pey">Overview</div>
    <h1 class="pt">Welcome back, <?= esc(current_admin()['full_name']) ?></h1>
    <p class="ps">Here's what's happening across your Galilea website.</p>
  </div>
</div>

<div class="g4" style="margin-bottom:16px">
  <div class="sm gold"><div class="sml">New Inquiries</div><div class="smv"><?= $inquiriesNew ?></div><div class="sms"><?= $inquiriesTotal ?> total received</div></div>
  <div class="sm"><div class="sml">Services</div><div class="smv"><?= $counts['services'] ?></div><div class="sms">Published offerings</div></div>
  <div class="sm blue"><div class="sml">Shipments</div><div class="smv"><?= $counts['shipments'] ?></div><div class="sms">Trackable records</div></div>
  <div class="sm green"><div class="sml">Subscribers</div><div class="smv"><?= $counts['subscribers'] ?></div><div class="sms">Newsletter list</div></div>
</div>

<div class="g2" style="margin-bottom:16px">
  <div class="card">
    <div class="ch"><div><div class="ct">Inquiries — last 14 days</div><div class="cs"><?= $trend14 ?> received in this period</div></div></div>
    <div class="cb">
      <div class="chart-bars" role="img" aria-label="Daily inquiries over the last 14 days">
        <?php foreach ($days as $d => $c): ?>
        <div class="cbar" title="<?= esc(date('M j', strtotime($d))) ?>: <?= $c ?>">
          <span class="cbar-fill" style="height:<?= $c ? max(4, round($c / $maxDay * 100)) : 0 ?>%"></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="chart-axis"><span><?= esc(date('M j', strtotime(array_key_first($days)))) ?></span><span>Today</span></div>
    </div>
  </div>

  <div class="card">
    <div class="ch"><div><div class="ct">Inquiries by status</div><div class="cs">Across <?= $inquiriesTotal ?> total</div></div></div>
    <div class="cb">
      <?php if (!$statusRows): ?>
        <p style="color:var(--muted-2);font-size:13px">No inquiries yet.</p>
      <?php else: foreach ($statusRows as $s): ?>
        <div class="hbar">
          <span class="hbar-label"><?= esc($s['status']) ?></span>
          <span class="hbar-track"><span class="hbar-fill<?= $s['status'] === 'new' ? ' is-new' : '' ?>" style="width:<?= round($s['c'] / $statusMax * 100) ?>%"></span></span>
          <span class="hbar-val"><?= (int) $s['c'] ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<div class="g2">
  <div class="card">
    <div class="ch"><div><div class="ct">Recent Inquiries</div><div class="cs">Latest quote requests</div></div><a href="/admin.php?p=inquiries" class="btn btn-ghost btn-sm">View all</a></div>
    <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Name</th><th>Interest</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (!$recentInq): ?><tr><td colspan="3" style="text-align:center;color:#8a95a7;padding:20px">No inquiries yet.</td></tr><?php endif; ?>
        <?php foreach ($recentInq as $i): ?>
        <tr>
          <td style="font-weight:700"><?= esc($i['full_name']) ?><div style="font-weight:400;color:#8a95a7;font-size:11px"><?= esc($i['email']) ?></div></td>
          <td><?= esc($i['service_interest']) ?></td>
          <td><span class="bdg <?= $i['status'] === 'new' ? 'bdg-feat' : 'bdg-ok' ?>"><?= esc($i['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="card">
    <div class="ch"><div><div class="ct">Content Summary</div><div class="cs">Live records on your site</div></div></div>
    <div class="cb">
      <table class="dt" style="margin:-16px -18px;width:calc(100% + 36px)">
        <tbody>
          <tr><td style="font-weight:700">Hero Slides</td><td style="text-align:right"><a href="/admin.php?p=hero" class="btn btn-ghost btn-sm">Manage</a></td></tr>
          <tr><td style="font-weight:700">Services <span class="bdg bdg-vw"><?= $counts['services'] ?></span></td><td style="text-align:right"><a href="/admin.php?p=services" class="btn btn-ghost btn-sm">Manage</a></td></tr>
          <tr><td style="font-weight:700">News &amp; Insights <span class="bdg bdg-vw"><?= $counts['news'] ?></span></td><td style="text-align:right"><a href="/admin.php?p=news" class="btn btn-ghost btn-sm">Manage</a></td></tr>
          <tr><td style="font-weight:700">Testimonials <span class="bdg bdg-vw"><?= $counts['testimonials'] ?></span></td><td style="text-align:right"><a href="/admin.php?p=testimonials" class="btn btn-ghost btn-sm">Manage</a></td></tr>
          <tr><td style="font-weight:700">Team Members <span class="bdg bdg-vw"><?= $counts['team'] ?></span></td><td style="text-align:right"><a href="/admin.php?p=team" class="btn btn-ghost btn-sm">Manage</a></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <div class="ch"><div><div class="ct">Recent Activity</div><div class="cs">Admin audit trail</div></div><a href="/admin.php?p=activity" class="btn btn-ghost btn-sm">View all</a></div>
  <div style="overflow-x:auto">
  <table class="dt">
    <thead><tr><th>User</th><th>Action</th><th>Detail</th><th>When</th></tr></thead>
    <tbody>
      <?php if (!$recentAct): ?><tr><td colspan="4" style="text-align:center;color:#8a95a7;padding:20px">No activity recorded.</td></tr><?php endif; ?>
      <?php foreach ($recentAct as $a): ?>
      <tr><td style="font-weight:700"><?= esc($a['username']) ?></td><td><span class="bdg bdg-ed"><?= esc($a['action']) ?></span></td><td><?= esc($a['detail']) ?></td><td style="color:#8a95a7"><?= esc($a['created_at']) ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
