<?php
$settings = Database::all('SELECT * FROM site_settings ORDER BY group_name, label');
$groups = [];
foreach ($settings as $s) {
    $groups[$s['group_name']][] = $s;
}
$groupTitles = ['contact' => 'Contact Details', 'email' => 'Email / SMTP', 'hero' => 'Hero Section', 'stats' => 'Statistics', 'seo' => 'SEO & Meta', 'geo' => 'Geo / Local SEO', 'social' => 'Social Profiles', 'analytics' => 'Analytics', 'general' => 'General'];

$socialPlatforms = [
    'social_facebook'  => 'Facebook',
    'social_instagram' => 'Instagram',
    'social_linkedin'  => 'LinkedIn',
    'social_youtube'   => 'YouTube',
    'social_twitter'   => 'X / Twitter',
    'social_tiktok'    => 'TikTok',
    'social_whatsapp'  => 'WhatsApp Channel',
];

$emailSections = [
    'sending' => [
        'title' => 'Sending Settings',
        'keys'  => ['email_enabled', 'mail_from_name', 'mail_from_email', 'email_logo'],
    ],
    'notifications' => [
        'title' => 'Notifications',
        'keys'  => ['inquiry_notify_email', 'mail_autoreply'],
    ],
    'smtp' => [
        'title' => 'SMTP Server',
        'desc'  => 'Leave SMTP host blank to use the server\'s built-in mail() function. Configure the fields below to send via a professional SMTP provider (Gmail, SendGrid, etc.).',
        'keys'  => ['smtp_host', 'smtp_port', 'smtp_secure', 'smtp_user', 'smtp_pass'],
    ],
];

/** Render a single setting input with the right widget for its key. */
function setting_field(array $s): string {
    $key = $s['key'];
    $name = 'setting[' . esc($key) . ']';
    $val = $s['value'];
    if (in_array($key, ['email_enabled', 'mail_autoreply'], true)) {
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

/** Look up a setting row by key from the $settings array. */
function setting_row(string $key): ?array {
    global $settings;
    foreach ($settings as $s) {
        if ($s['key'] === $key) return $s;
    }
    return null;
}
?>
<div class="ph">
  <div class="phl"><div class="pey">System</div><h1 class="pt">Site Settings</h1><p class="ps">Global content shown across the public website.</p></div>
</div>

<form method="post" action="/admin.php?action=save_settings" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <!-- ── Social Platforms ── -->
  <div class="card">
    <div class="ch"><div><div class="ct">Social Platforms</div><div class="cs">Enable each platform and provide the full profile URL. Only enabled platforms with a URL will appear on the public site.</div></div></div>
    <div class="cb">
      <div class="fr2">
        <?php foreach ($socialPlatforms as $key => $label):
          $enabled = setting($key . '_enabled') === '1';
          $url = setting($key . '_url');
        ?>
        <div class="fg" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <input type="hidden" name="setting[<?= $key ?>_enabled]" value="0">
          <input type="hidden" name="setting_group[<?= $key ?>_enabled]" value="social">
          <input type="hidden" name="setting_group[<?= $key ?>_url]" value="social">
          <label class="switch" style="margin:0;flex-shrink:0">
            <input type="checkbox" name="setting[<?= $key ?>_enabled]" value="1"<?= $enabled ? ' checked' : '' ?>>
            <span class="switch-slider"></span>
          </label>
          <label class="fl" style="margin:0;min-width:120px;font-weight:600"><?= esc($label) ?></label>
          <input class="fi" type="text" name="setting[<?= $key ?>_url]" value="<?= esc($url) ?>" placeholder="https://<?= esc($key === 'social_twitter' ? 'x.com/yourhandle' : ($key === 'social_whatsapp' ? 'whatsapp.com/channel/...' : ($key === 'social_tiktok' ? 'tiktok.com/@' : ($key . '.com/yourpage')))) ?>" style="flex:1;min-width:200px">
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ── Email / SMTP ── -->
  <div class="card">
    <div class="ch"><div><div class="ct">Email / SMTP</div><div class="cs">Control how the site sends email notifications for inquiries and customer auto-replies.</div></div></div>
    <div class="cb">
      <?php foreach ($emailSections as $section):
        $hasAny = false;
        foreach ($section['keys'] as $k) { if (setting_row($k)) { $hasAny = true; break; } }
        if (!$hasAny) continue;
      ?>
      <div style="margin-bottom:20px">
        <div style="font-size:13px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px"><?= esc($section['title']) ?></div>
        <?php if (!empty($section['desc'])): ?><p style="font-size:12px;color:#8a95a7;margin:0 0 12px"><?= $section['desc'] ?></p><?php endif; ?>
        <div class="fr2">
          <?php foreach ($section['keys'] as $k): $s = setting_row($k); if (!$s) continue; ?>
          <?php if ($k === 'email_logo'): $logoUrl = setting('email_logo'); ?>
          <div class="fg" style="grid-column:1/-1">
            <label class="fl">Email Header Logo</label>
            <div class="img-drop">
              <input type="file" class="img-file" name="email_logo_file" accept="image/jpeg,image/png,image/webp,image/gif">
              <div class="img-drop-inner">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                <span>Drop a logo or click to upload</span>
                <span class="img-drop-size">Max 4 MB · JPG, PNG, WebP</span>
              </div>
              <?php if ($logoUrl): ?>
              <div class="img-preview">
                <img src="<?= esc($logoUrl) ?>" alt="Email logo" style="max-height:50px">
                <label class="img-remove"><input type="checkbox" name="setting[email_logo_remove]" value="1"> Remove current logo</label>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <?php else: ?>
          <div class="fg">
            <label class="fl"><?= esc($s['label']) ?></label>
            <?= setting_field($s) ?>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (setting_row('smtp_host')): ?>
      <div style="padding:12px 14px;background:#f8fafb;border-radius:6px;font-size:12px;color:#5A6478;display:flex;align-items:center;gap:8px">
        <span style="width:8px;height:8px;border-radius:50%;background:<?= trim(setting('smtp_host', '')) !== '' ? 'var(--gold,#C9A84C)' : '#8a95a7' ?>;flex-shrink:0"></span>
        <?php if (trim(setting('smtp_host', '')) !== ''): ?>
          SMTP is configured — emails will be sent via <strong><?= esc(setting('smtp_host')) ?>:<?= esc(setting('smtp_port', '587')) ?></strong>
        <?php else: ?>
          SMTP is not configured — the server's built-in <strong>mail()</strong> function will be used
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php foreach ($groups as $group => $items): ?>
  <?php if (in_array($group, ['social', 'email'], true)) continue; ?>
  <div class="card">
    <div class="ch"><div><div class="ct"><?= esc($groupTitles[$group] ?? ucfirst($group)) ?></div>
      <?php if ($group === 'email'): ?><div class="cs">Configure how the site sends inquiry alerts and customer auto-replies. Leave <strong>SMTP host</strong> blank to use the server's built-in mail.</div><?php endif; ?>
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
