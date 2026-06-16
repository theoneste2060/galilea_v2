<?php
/** @var string $q @var array $results */
$meta = ['title' => ($q !== '' ? "Search: $q" : 'Search') . ' — Galilea Global Logistics', 'description' => 'Search Galilea Global Logistics services and insights.', 'robots' => 'noindex, follow',
    'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Search', 'url' => '/search']]];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page">Search</span></nav>
    <h1 class="page-hero-title">Search</h1>
    <form action="/search" method="get" role="search" style="margin-top:18px;display:flex;gap:10px;max-width:560px">
      <input type="search" name="q" value="<?= esc($q) ?>" placeholder="Search services, insights…" autocomplete="off" style="flex:1;padding:14px 16px;border:none;border-radius:10px;font-size:15px;font-family:var(--font-sans);outline:none">
      <button type="submit" style="padding:0 22px;border:none;border-radius:10px;background:var(--gold);color:var(--navy-deep);font-weight:800;cursor:pointer">Go</button>
    </form>
  </div>
</header>

<section class="section-pad">
  <div class="container" style="max-width:760px">
    <?php if ($q === '' || mb_strlen($q) < 2): ?>
      <p style="color:var(--muted)">Type at least two characters to search.</p>
    <?php elseif (!$results): ?>
      <p style="color:var(--muted)">No results for “<strong><?= esc($q) ?></strong>”. Try a broader term, or <a href="/contact" style="color:var(--gold-dark);font-weight:600">contact our team</a>.</p>
    <?php else: ?>
      <p style="color:var(--muted);margin-bottom:18px"><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for “<strong><?= esc($q) ?></strong>”</p>
      <?php foreach ($results as $r): ?>
      <a class="search-result" href="<?= esc($r['url']) ?>">
        <span class="sr-kind"><?= esc($r['kind']) ?></span>
        <div class="sr-title"><?= esc($r['title']) ?></div>
        <?php if (!empty($r['excerpt'])): ?><div class="sr-excerpt"><?= esc($r['excerpt']) ?></div><?php endif; ?>
      </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
