<?php
/** @var array $cms */
$meta = ['title' => $cms['title'] . ' | Galilea Global Logistics', 'description' => $cms['meta_description'] ?: setting('seo_description'),
    'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => $cms['title'], 'url' => '/' . $cms['slug']]]];
require __DIR__ . '/partials/head.php';
?>
<header class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span aria-current="page"><?= esc($cms['title']) ?></span></nav>
    <h1 class="page-hero-title"><?= esc($cms['title']) ?></h1>
  </div>
</header>
<section class="section-pad">
  <div class="container" style="max-width:820px">
    <article class="article-body"><?= sanitize_html($cms['body']) ?></article>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
