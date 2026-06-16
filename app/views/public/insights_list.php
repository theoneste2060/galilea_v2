<?php
/** @var array $posts @var int $page @var int $pages */
$meta = ['title' => 'Insights & News — Galilea Global Logistics', 'description' => 'Shipping updates, China sourcing tips and East Africa trade news from Galilea Global Logistics.'];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page">Insights</span></nav>
    <p class="section-label" style="color:#C9A84C">Insights &amp; News</p>
    <h1 class="page-hero-title">Galilea Insights &amp; Updates</h1>
  </div>
</header>

<section class="section-pad">
  <div class="container">
    <?php if (!$posts): ?>
      <p style="text-align:center;color:#5A6478">No articles published yet. Check back soon.</p>
    <?php else: ?>
    <div class="news-grid">
      <?php foreach ($posts as $n): ?>
      <a class="news-card" href="/insights/<?= esc($n['slug']) ?>">
        <div class="nc-img"><?php if (!empty($n['image_path'])): ?><img src="<?= esc($n['image_path']) ?>" alt="<?= esc($n['title']) ?>" loading="lazy"><?php endif; ?></div>
        <div class="nc-body">
          <span class="nc-tag tag-green"><?= esc($n['category']) ?></span>
          <h2 class="nc-title"><?= esc($n['title']) ?></h2>
          <p class="nc-meta"><?= esc(date('F j, Y', strtotime($n['published_at']))) ?></p>
          <p class="nc-excerpt"><?= esc($n['excerpt']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Pagination">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="/insights?page=<?= $i ?>" class="pager-link<?= $i === $page ? ' active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
