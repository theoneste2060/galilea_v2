<?php
$dir = config('upload_dir');
$urlBase = rtrim(config('upload_url'), '/');
$files = [];
if (is_dir($dir)) {
    foreach (scandir($dir) ?: [] as $f) {
        if (preg_match('/\.(jpe?g|png|webp|gif)$/i', $f)) {
            $files[] = ['name' => $f, 'url' => $urlBase . '/' . $f, 'size' => round(filesize($dir . '/' . $f) / 1024, 1), 'mtime' => filemtime($dir . '/' . $f)];
        }
    }
    usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
}
?>
<div class="ph">
  <div class="phl"><div class="pey">Content</div><h1 class="pt">Media Library</h1><p class="ps"><?= count($files) ?> image<?= count($files) === 1 ? '' : 's' ?> · auto-optimised to WebP on upload</p></div>
</div>

<div class="card">
  <div class="cb">
    <form method="post" action="/admin.php?action=media_upload" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="img-drop" data-input="file">
        <div class="img-drop-inner"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg><span>Drag &amp; drop an image, or click to browse</span><small>JPG, PNG, WEBP or GIF · max 4 MB</small></div>
        <input type="file" name="file" accept="image/png,image/jpeg,image/webp,image/gif" class="img-file" hidden>
      </div>
      <div class="mf" style="border:none"><button class="btn btn-navy" type="submit">Upload</button></div>
    </form>
  </div>
</div>

<?php if ($files): ?>
<div class="media-grid">
  <?php foreach ($files as $f): ?>
  <div class="media-item">
    <div class="media-thumb"><img src="<?= esc($f['url']) ?>" alt="<?= esc($f['name']) ?>" loading="lazy"></div>
    <div class="media-meta">
      <div class="media-size"><?= esc($f['size']) ?> KB</div>
      <div class="media-actions">
        <button type="button" class="btn btn-ghost btn-sm" data-copy="<?= esc($f['url']) ?>">Copy URL</button>
        <form method="post" action="/admin.php?action=media_delete" onsubmit="return confirm('Delete this image? Content using it will show a broken image.');" style="display:inline">
          <?= csrf_field() ?>
          <input type="hidden" name="file" value="<?= esc($f['name']) ?>">
          <button type="submit" class="ib del" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg></button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card"><div class="cb" style="text-align:center;color:#8a95a7;padding:24px">No images yet. Upload your first image above.</div></div>
<?php endif; ?>
