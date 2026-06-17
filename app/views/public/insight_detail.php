<?php
/** @var array $post @var array $more */
$meta = [
    'title' => $post['title'] . ' | Galilea Insights',
    'description' => $post['excerpt'],
    'type' => 'article',
    'image' => $post['image_path'] ?: '/assets/img/logo.jpeg',
    'published' => $post['published_at'],
    'modified' => $post['updated_at'] ?? $post['published_at'],
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Insights', 'url' => '/insights'],
        ['name' => $post['title'], 'url' => '/insights/' . $post['slug']],
    ],
    'schema' => [[
        '@type' => 'NewsArticle',
        'headline' => $post['title'],
        'description' => $post['excerpt'],
        'image' => abs_url($post['image_path'] ?: '/assets/img/logo.jpeg'),
        'datePublished' => date('c', strtotime($post['published_at'])),
        'dateModified' => date('c', strtotime($post['updated_at'] ?? $post['published_at'])),
        'articleSection' => $post['category'],
        'author' => ['@id' => base_url() . '/#organization'],
        'publisher' => ['@id' => base_url() . '/#organization'],
        'mainEntityOfPage' => abs_url('/insights/' . $post['slug']),
    ]],
];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><a href="/insights">Insights</a><span>/</span><span aria-current="page"><?= esc(mb_strimwidth($post['title'], 0, 40, '…')) ?></span></nav>
    <span class="nc-tag tag-green" style="margin-bottom:12px"><?= esc($post['category']) ?></span>
    <h1 class="page-hero-title"><?= esc($post['title']) ?></h1>
    <p class="page-hero-sub"><?= esc(date('F j, Y', strtotime($post['published_at']))) ?></p>
  </div>
</header>

<section class="section-pad">
  <div class="container" style="max-width:820px">
    <article class="article-body">
      <?php if (!empty($post['image_path'])): ?><img src="<?= esc($post['image_path']) ?>" alt="<?= esc($post['title']) ?>" class="article-cover" loading="lazy"><?php endif; ?>
      <p class="lead"><?= esc($post['excerpt']) ?></p>
      <?= sanitize_html($post['body']) ?>
    </article>
    <div class="share-row">
      <a href="/contact" class="btn-gold-lg">Talk to our team</a>
      <a href="/insights" class="btn-link">Back to all insights</a>
    </div>
  </div>

  <?php if ($more): ?>
  <div class="container">
    <div class="related-block">
      <h2 class="section-title" style="font-size:22px;margin-bottom:18px">More insights</h2>
      <div class="g3">
        <?php foreach ($more as $m): ?>
        <a class="mini-card" href="/insights/<?= esc($m['slug']) ?>">
          <?php if (!empty($m['image_path'])): ?><img src="<?= esc($m['image_path']) ?>" alt="" loading="lazy"><?php endif; ?>
          <div class="mini-body"><h3><?= esc($m['title']) ?></h3><p><?= esc($m['excerpt']) ?></p></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
