<?php
$settings = Database::all('SELECT * FROM site_settings ORDER BY group_name, label');
$groups = [];
foreach ($settings as $s) {
    $groups[$s['group_name']][] = $s;
}
$groupTitles = ['contact' => 'Contact Details', 'hero' => 'Hero Section', 'stats' => 'Statistics', 'seo' => 'SEO & Meta', 'geo' => 'Geo / Local SEO', 'social' => 'Social Profiles', 'analytics' => 'Analytics', 'general' => 'General'];
?>
<div class="ph">
  <div class="phl"><div class="pey">System</div><h1 class="pt">Site Settings</h1><p class="ps">Global content shown across the public website.</p></div>
</div>

<form method="post" action="/admin.php?action=save_settings">
  <?= csrf_field() ?>
  <?php foreach ($groups as $group => $items): ?>
  <div class="card">
    <div class="ch"><div><div class="ct"><?= esc($groupTitles[$group] ?? ucfirst($group)) ?></div></div></div>
    <div class="cb">
      <div class="fr2">
        <?php foreach ($items as $s): ?>
        <div class="fg">
          <label class="fl"><?= esc($s['label']) ?></label>
          <?php if (str_contains($s['key'], 'description')): ?>
            <textarea class="fta" name="setting[<?= esc($s['key']) ?>]"><?= esc($s['value']) ?></textarea>
          <?php else: ?>
            <input class="fi" type="text" name="setting[<?= esc($s['key']) ?>]" value="<?= esc($s['value']) ?>">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="mf" style="border:none"><button class="btn btn-navy" type="submit">Save Settings</button></div>
</form>
