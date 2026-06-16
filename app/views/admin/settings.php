<?php
$settings = Database::all('SELECT * FROM site_settings ORDER BY group_name, label');
$groups = [];
foreach ($settings as $s) {
    $groups[$s['group_name']][] = $s;
}
$groupTitles = ['contact' => 'Contact Details', 'email' => 'Email / SMTP', 'hero' => 'Hero Section', 'stats' => 'Statistics', 'seo' => 'SEO & Meta', 'geo' => 'Geo / Local SEO', 'social' => 'Social Profiles', 'analytics' => 'Analytics', 'general' => 'General'];

/** Render a single setting input with the right widget for its key. */
function setting_field(array $s): string {
    $key = $s['key'];
    $name = 'setting[' . esc($key) . ']';
    $val = $s['value'];
    if (in_array($key, ['email_enabled', 'mail_autoreply'], true)) {
        // Hidden 0 + checkbox 1 (last value wins when checked).
        return '<input type="hidden" name="' . $name . '" value="0">'
            . '<label class="switch"><input type="checkbox" name="' . $name . '" value="1"' . ($val === '1' ? ' checked' : '') . '><span class="switch-slider"></span></label>';
    }
    if ($key === 'smtp_secure') {
        $opts = ['tls' => 'STARTTLS (port 587)', 'ssl' => 'SSL/TLS (port 465)', 'none' => 'None (unencrypted)'];
        $h = '<select class="fsel" name="' . $name . '">';
        foreach ($opts as $k => $lbl) $h .= '<option value="' . esc($k) . '"' . ($val === $k ? ' selected' : '') . '>' . esc($lbl) . '</option>';
        return $h . '</select>';
    }
    if (str_contains($key, 'pass')) {
        return '<input class="fi" type="password" name="' . $name . '" value="' . esc($val) . '" autocomplete="new-password">';
    }
    if (str_contains($key, 'description')) {
        return '<textarea class="fta" name="' . $name . '">' . esc($val) . '</textarea>';
    }
    $type = str_contains($key, 'email') ? 'email' : 'text';
    return '<input class="fi" type="' . $type . '" name="' . $name . '" value="' . esc($val) . '">';
}
?>
<div class="ph">
  <div class="phl"><div class="pey">System</div><h1 class="pt">Site Settings</h1><p class="ps">Global content shown across the public website.</p></div>
</div>

<form method="post" action="/admin.php?action=save_settings">
  <?= csrf_field() ?>
  <?php foreach ($groups as $group => $items): ?>
  <div class="card">
    <div class="ch"><div><div class="ct"><?= esc($groupTitles[$group] ?? ucfirst($group)) ?></div>
      <?php if ($group === 'email'): ?><div class="cs">Configure how the site sends inquiry alerts and customer auto-replies. Leave <strong>SMTP host</strong> blank to use the server’s built-in mail.</div><?php endif; ?>
    </div></div>
    <div class="cb">
      <div class="fr2">
        <?php foreach ($items as $s): ?>
        <div class="fg">
          <label class="fl"><?= esc($s['label']) ?></label>
          <?= setting_field($s) ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="mf" style="border:none"><button class="btn btn-navy" type="submit">Save Settings</button></div>
</form>

<?php if (isset($groups['email'])): ?>
<div class="card">
  <div class="ch"><div><div class="ct">Test email delivery</div><div class="cs">Sends a test message using the <strong>saved</strong> settings above. Save your changes first.</div></div></div>
  <div class="cb">
    <form method="post" action="/admin.php?action=send_test_email" class="ibg" style="gap:10px;align-items:flex-end">
      <?= csrf_field() ?>
      <div class="fg" style="margin:0;flex:1;max-width:320px">
        <label class="fl">Send test to</label>
        <input class="fi" type="email" name="test_email" value="<?= esc(setting('inquiry_notify_email', '')) ?>" placeholder="you@example.com">
      </div>
      <button class="btn btn-gold" type="submit">Send test email</button>
    </form>
  </div>
</div>
<?php endif; ?>
