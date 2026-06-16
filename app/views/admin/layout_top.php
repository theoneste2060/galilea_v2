<?php
/** @var string $p @var array $resources */
$admin = current_admin();
$initials = strtoupper(mb_substr($admin['full_name'] ?? 'A', 0, 1));

// Sidebar navigation groups.
$contentNav = [
    'dashboard'    => ['Dashboard', '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
    'hero'         => ['Hero Slides', $resources['hero']['icon']],
    'services'     => ['Services', $resources['services']['icon']],
    'news'         => ['News & Insights', $resources['news']['icon']],
    'testimonials' => ['Testimonials', $resources['testimonials']['icon']],
    'team'         => ['Team Members', $resources['team']['icon']],
    'shipments'    => ['Shipments', $resources['shipments']['icon']],
    'pages'        => ['Static Pages', $resources['pages']['icon']],
    'menu'         => ['Navigation Menu', $resources['menu']['icon']],
];
$engageNav = [
    'inquiries'   => ['Inquiries', '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>'],
    'subscribers' => ['Subscribers', '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'],
];
$systemNav = [
    'settings' => ['Site Settings', '<path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>'],
    'activity' => ['Activity Logs', '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
];
if (($admin['role'] ?? '') === 'superadmin') {
    $systemNav = ['users' => ['Users', '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>']] + $systemNav;
}

$titles = array_merge(
    array_map(fn($v) => $v[0], $contentNav + $engageNav + $systemNav)
);
$pageTitle = $titles[$p] ?? ($resources[$p]['label'] ?? 'Dashboard');

function nav_item(string $key, array $item, string $current): string {
    $active = $key === $current ? ' active' : '';
    return '<a class="nav-item' . $active . '" href="/admin.php?p=' . esc($key) . '">'
        . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' . $item[1] . '</svg>'
        . '<span class="nit">' . esc($item[0]) . '</span></a>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle) ?> — Galilea Admin</title>
<link rel="icon" href="/assets/img/logo.jpeg">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<aside class="sidebar" id="sb">
  <div class="sb-brand">
    <div style="display:flex;align-items:center;gap:9px;min-width:0">
      <div class="sb-lm" style="overflow:hidden;padding:0"><img src="/assets/img/logo.jpeg" alt="Galilea" style="width:100%;height:100%;object-fit:cover;border-radius:7px"></div>
      <div class="sb-brand-text">
        <div class="sb-name">Galilea</div>
        <span class="sb-sub">Global Logistics</span>
        <div><span class="sb-tag">Admin</span></div>
      </div>
    </div>
    <button class="sb-toggle" onclick="document.getElementById('sb').classList.toggle('collapsed');document.getElementById('mn').classList.toggle('collapsed')" title="Collapse"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
  </div>
  <nav class="sb-nav">
    <?php foreach ($contentNav as $k => $item) echo nav_item($k, $item, $p); ?>
    <div style="height:1px;background:rgba(255,255,255,.08);margin:8px 6px"></div>
    <?php foreach ($engageNav as $k => $item) echo nav_item($k, $item, $p); ?>
    <div style="height:1px;background:rgba(255,255,255,.08);margin:8px 6px"></div>
    <?php foreach ($systemNav as $k => $item) echo nav_item($k, $item, $p); ?>
  </nav>
  <div class="sb-footer">
    <div class="sb-user">
      <div class="uav"><?= esc($initials) ?></div>
      <div class="sb-ui"><div class="un"><?= esc($admin['full_name']) ?></div><div class="ue"><?= esc($admin['role']) ?></div></div>
      <form method="post" action="/admin.php?p=logout" style="margin-left:auto">
        <?= csrf_field() ?>
        <button class="logout-btn" type="submit" title="Sign out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></button>
      </form>
    </div>
  </div>
</aside>

<div class="main" id="mn">
  <div class="topbar">
    <div class="bc"><span class="bc-h">Admin</span><span class="bc-s">/</span><span class="bc-c"><?= esc($pageTitle) ?></span></div>
    <div class="topbar-right">
      <span class="tb-badge"><?= esc($admin['role']) ?></span>
      <a href="/" target="_blank" class="btn btn-ghost btn-sm" style="text-decoration:none">View Site</a>
    </div>
  </div>
  <div class="pc">
    <?php foreach (take_flashes() as $f): ?>
      <div class="alert alert-<?= $f['type'] === 'error' ? 'err' : 'ok' ?>"><?= esc($f['msg']) ?></div>
    <?php endforeach; ?>
