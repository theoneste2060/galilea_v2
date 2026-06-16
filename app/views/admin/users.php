<?php
require_role('superadmin');
$rows = Database::all('SELECT id, username, full_name, role, is_active, created_at FROM admin_users ORDER BY id');
$editId = (int) input('edit');
$isNew = input('new') === '1';
$edit = $editId ? Database::one('SELECT * FROM admin_users WHERE id = ?', [$editId]) : null;
?>
<div class="ph">
  <div class="phl"><div class="pey">System</div><h1 class="pt">Admin Users</h1><p class="ps">Manage who can access this portal.</p></div>
  <?php if (!$editId && !$isNew): ?><div class="pa"><a href="/admin.php?p=users&new=1" class="btn btn-gold">+ New User</a></div><?php endif; ?>
</div>

<?php if ($editId || $isNew): ?>
<div class="card"><div class="cb">
  <form method="post" action="/admin.php?action=user_save">
    <?= csrf_field() ?>
    <?php if ($editId): ?><input type="hidden" name="id" value="<?= (int) $editId ?>"><?php endif; ?>
    <div class="fr2">
      <div class="fg"><label class="fl">Username *</label><input class="fi" name="username" value="<?= esc($edit['username'] ?? '') ?>" required></div>
      <div class="fg"><label class="fl">Full name *</label><input class="fi" name="full_name" value="<?= esc($edit['full_name'] ?? '') ?>" required></div>
    </div>
    <div class="fr2">
      <div class="fg"><label class="fl">Role</label><select class="fsel" name="role"><option value="editor"<?= ($edit['role'] ?? '') === 'editor' ? ' selected' : '' ?>>Editor</option><option value="superadmin"<?= ($edit['role'] ?? '') === 'superadmin' ? ' selected' : '' ?>>Super Admin</option></select></div>
      <div class="fg"><label class="fl">Password <?= $editId ? '(leave blank to keep)' : '*' ?></label><input class="fi" type="password" name="password" autocomplete="new-password" <?= $editId ? '' : 'required' ?>><p class="fh">Minimum 8 characters.</p></div>
    </div>
    <div class="fg"><label class="fl-check"><input type="checkbox" name="is_active" value="1"<?= (!isset($edit['is_active']) || $edit['is_active']) ? ' checked' : '' ?>> Active account</label></div>
    <div class="mf" style="border:none"><a href="/admin.php?p=users" class="btn btn-ghost">Cancel</a><button class="btn btn-navy" type="submit">Save User</button></div>
  </form>
</div></div>
<?php else: ?>
<div class="card"><div style="overflow-x:auto">
  <table class="dt">
    <thead><tr><th>Username</th><th>Name</th><th>Role</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td style="font-weight:700"><?= esc($r['username']) ?></td>
        <td><?= esc($r['full_name']) ?></td>
        <td><span class="bdg <?= $r['role'] === 'superadmin' ? 'bdg-adm' : 'bdg-ed' ?>"><?= esc($r['role']) ?></span></td>
        <td><?php if ($r['is_active']): ?><span class="bdg bdg-ok"><span class="dot5" style="background:#4ade80"></span>Active</span><?php else: ?><span class="bdg bdg-off">Disabled</span><?php endif; ?></td>
        <td>
          <div class="ibg" style="justify-content:flex-end">
            <a href="/admin.php?p=users&edit=<?= (int) $r['id'] ?>" class="ib edit" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
            <?php if ((int) $r['id'] !== (int) current_admin()['id']): ?>
            <form method="post" action="/admin.php?action=user_delete" onsubmit="return confirm('Delete this user?')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="ib del" type="submit" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg></button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>
