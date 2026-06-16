<?php
$me = Database::one('SELECT * FROM admin_users WHERE id = ?', [current_admin()['id']]);
$twofaOn = !empty($me['totp_enabled']);
$pending = $_SESSION['pending_totp'] ?? '';
$uri = $pending ? Totp::uri($pending, $me['username'], 'Galilea Admin') : '';
?>
<div class="ph">
  <div class="phl"><div class="pey">Security</div><h1 class="pt">My Account</h1><p class="ps">Signed in as <strong><?= esc($me['username']) ?></strong> · <?= esc($me['role']) ?></p></div>
</div>

<div class="g2">
  <!-- Change password -->
  <div class="card">
    <div class="ch"><div><div class="ct">Change Password</div><div class="cs">Use a strong, unique password (min 8 characters)</div></div></div>
    <div class="cb">
      <form method="post" action="/admin.php?action=account_password" autocomplete="off">
        <?= csrf_field() ?>
        <div class="fg"><label class="fl">Current password</label><input class="fi" type="password" name="current_password" autocomplete="current-password" required></div>
        <div class="fg"><label class="fl">New password</label><input class="fi" type="password" name="new_password" autocomplete="new-password" required></div>
        <div class="fg"><label class="fl">Confirm new password</label><input class="fi" type="password" name="confirm_password" autocomplete="new-password" required></div>
        <button class="btn btn-navy" type="submit">Update Password</button>
      </form>
    </div>
  </div>

  <!-- Two-factor -->
  <div class="card">
    <div class="ch"><div><div class="ct">Two-Factor Authentication</div><div class="cs">Time-based one-time codes (TOTP)</div></div>
      <span class="bdg <?= $twofaOn ? 'bdg-suc' : 'bdg-off' ?>"><?= $twofaOn ? 'Enabled' : 'Disabled' ?></span>
    </div>
    <div class="cb">
      <?php if ($twofaOn): ?>
        <p style="font-size:12.5px;color:#5A6478;margin-bottom:14px">Your account is protected by an authenticator app. To turn it off, confirm your password.</p>
        <form method="post" action="/admin.php?action=account_2fa_disable" autocomplete="off">
          <?= csrf_field() ?>
          <div class="fg"><label class="fl">Password</label><input class="fi" type="password" name="password" autocomplete="current-password" required></div>
          <button class="btn btn-ds" type="submit">Disable 2FA</button>
        </form>
      <?php elseif ($pending): ?>
        <p style="font-size:12.5px;color:#5A6478;margin-bottom:12px">1. Scan this QR code in Google Authenticator, Authy or 1Password — or enter the key manually.</p>
        <div id="totp-qr" data-uri="<?= esc($uri) ?>" style="width:170px;height:170px;margin:0 auto 12px;background:#fff;padding:6px;border:1px solid #e8edf3;border-radius:8px"></div>
        <p style="text-align:center;font-size:11px;color:#8a95a7;margin-bottom:14px">Manual key:<br><code style="font-size:12px;word-break:break-all"><?= esc($pending) ?></code></p>
        <form method="post" action="/admin.php?action=account_2fa_enable" autocomplete="off">
          <?= csrf_field() ?>
          <div class="fg"><label class="fl">2. Enter the 6-digit code to confirm</label><input class="fi" name="code" inputmode="numeric" maxlength="6" pattern="[0-9]*" placeholder="123456" required style="letter-spacing:.3em;text-align:center"></div>
          <button class="btn btn-navy" type="submit">Verify &amp; Enable</button>
        </form>
      <?php else: ?>
        <p style="font-size:12.5px;color:#5A6478;margin-bottom:14px">Add a second layer of protection. You'll enter a 6-digit code from your authenticator app each time you sign in.</p>
        <form method="post" action="/admin.php?action=account_2fa_init">
          <?= csrf_field() ?>
          <button class="btn btn-gold" type="submit">Set up 2FA</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($pending): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php endif; ?>
