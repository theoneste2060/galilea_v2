<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/admin/engine.php';

send_security_headers(true); // allow Summernote/QR CDN in CSP

$config = config();
$p = input('p') ?: (is_authenticated() ? 'dashboard' : 'login');

/** Finalise a successful authentication. */
function complete_login(array $user): never
{
    Database::run('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
    Database::run("UPDATE admin_users SET last_login_at = datetime('now') WHERE id = ?", [$user['id']]);
    session_regenerate_id(true);
    unset($_SESSION['2fa']);
    $_SESSION['admin'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
        'allowed_sections' => json_decode($user['allowed_sections'] ?? '[]', true) ?: [],
        'login_time' => time(),
        'last_activity' => time(),
    ];
    log_activity('login', 'Signed in');
    redirect('/admin.php?p=dashboard');
}

/* ─────────────────────────────── LOGOUT ──────────────────────────────── */

if ($p === 'logout') {
    if (is_post()) {
        csrf_check();
        log_activity('logout');
    }
    $_SESSION = [];
    session_regenerate_id(true);
    redirect('/admin.php?p=login');
}

/* ─────────────────────────────── LOGIN ───────────────────────────────── */

if ($p === 'login') {
    $error = null;
    $step = isset($_SESSION['2fa']) ? '2fa' : 'credentials';
    if (is_authenticated()) {
        redirect('/admin.php?p=dashboard');
    }
    if (is_post()) {
        csrf_check();
        $ip = client_ip();
        Database::run("DELETE FROM login_attempts WHERE attempted_at < datetime('now', ?)",
            ['-' . (int) $config['login_lockout_secs'] . ' seconds']);
        $recent = (int) Database::value('SELECT COUNT(*) FROM login_attempts WHERE ip = ?', [$ip]);

        if ($recent >= $config['max_login_attempts']) {
            $error = 'Too many attempts. Please wait a few minutes and try again.';
        } elseif (isset($_SESSION['2fa'])) {
            // Step 2 — verify the TOTP code.
            $pending = $_SESSION['2fa'];
            if (time() - ($pending['time'] ?? 0) > 300) {
                unset($_SESSION['2fa']);
                $error = 'Verification timed out. Please sign in again.';
                $step = 'credentials';
            } else {
                $user = Database::one('SELECT * FROM admin_users WHERE id = ? AND is_active = 1', [$pending['id']]);
                if ($user && Totp::verify($user['totp_secret'] ?? '', (string) input('code'))) {
                    complete_login($user);
                }
                Database::run('INSERT INTO login_attempts (ip) VALUES (?)', [$ip]);
                $error = 'Invalid verification code.';
            }
        } else {
            // Step 1 — verify username + password.
            $username = input('username');
            $password = (string) ($_POST['password'] ?? '');
            $user = Database::one('SELECT * FROM admin_users WHERE username = ? AND is_active = 1', [$username]);
            if ($user && password_verify($password, $user['password_hash'])) {
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    Database::run('UPDATE admin_users SET password_hash = ? WHERE id = ?',
                        [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
                }
                if (!empty($user['totp_enabled'])) {
                    $_SESSION['2fa'] = ['id' => (int) $user['id'], 'time' => time()];
                    $step = '2fa';
                } else {
                    complete_login($user);
                }
            } else {
                Database::run('INSERT INTO login_attempts (ip) VALUES (?)', [$ip]);
                $error = 'Invalid username or password.';
            }
        }
    }
    require dirname(__DIR__) . '/app/views/admin/login.php';
    exit;
}

/* ─────────────────────  Everything below requires auth  ──────────────── */
require_admin();

$action = input('action');

/* ─────────────────────────────  Actions  ─────────────────────────────── */

if ($action && is_post()) {
    csrf_check();
    $resources = admin_resources();
    $key = input('resource');

    // Generic resource save / delete (gated by section access).
    if (isset($resources[$key])) {
        require_access($key);
        $res = $resources[$key];
        $id  = (int) input('id') ?: null;
        try {
            if ($action === 'save') {
                $newId = save_resource($res, $id);
                log_activity($id ? 'update' : 'create', $res['singular'] . ' #' . $newId);
                flash($res['singular'] . ($id ? ' updated.' : ' created.'));
                redirect('/admin.php?p=' . $key);
            }
            if ($action === 'delete' && $id) {
                delete_resource($res, $id);
                log_activity('delete', $res['singular'] . ' #' . $id);
                flash($res['singular'] . ' deleted.');
                redirect('/admin.php?p=' . $key);
            }
        } catch (RuntimeException $e) {
            flash($e->getMessage(), 'error');
            redirect('/admin.php?p=' . $key . ($id ? '&edit=' . $id : '&new=1'));
        }
    }

    if ($action === 'save_settings') {
        require_role('superadmin');
        foreach (($_POST['setting'] ?? []) as $k => $v) {
            Database::run("UPDATE site_settings SET value = ?, updated_at = datetime('now') WHERE key = ?",
                [trim((string) $v), (string) $k]);
        }
        log_activity('update', 'Site settings');
        flash('Settings saved.');
        redirect('/admin.php?p=settings');
    }

    if ($action === 'inquiry_status') {
        require_access('inquiries');
        Database::run('UPDATE inquiries SET status = ? WHERE id = ?',
            [input('status') ?: 'new', (int) input('id')]);
        flash('Inquiry updated.');
        redirect('/admin.php?p=inquiries');
    }
    if ($action === 'inquiry_delete') {
        require_access('inquiries');
        Database::run('DELETE FROM inquiries WHERE id = ?', [(int) input('id')]);
        flash('Inquiry deleted.');
        redirect('/admin.php?p=inquiries');
    }
    if ($action === 'subscriber_delete') {
        require_access('subscribers');
        Database::run('DELETE FROM newsletter_subscribers WHERE id = ?', [(int) input('id')]);
        flash('Subscriber removed.');
        redirect('/admin.php?p=subscribers');
    }

    /* ── My Account: password + 2FA ── */
    if ($action === 'account_password') {
        $me = Database::one('SELECT * FROM admin_users WHERE id = ?', [current_admin()['id']]);
        $cur = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        if (!password_verify($cur, $me['password_hash'])) {
            flash('Current password is incorrect.', 'error');
        } elseif (strlen($new) < 8) {
            flash('New password must be at least 8 characters.', 'error');
        } elseif ($new !== $confirm) {
            flash('New passwords do not match.', 'error');
        } else {
            Database::run("UPDATE admin_users SET password_hash = ?, password_changed_at = datetime('now') WHERE id = ?",
                [password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            log_activity('password_change', 'Changed own password');
            flash('Password updated.');
        }
        redirect('/admin.php?p=account');
    }
    if ($action === 'account_2fa_init') {
        $_SESSION['pending_totp'] = Totp::secret();
        redirect('/admin.php?p=account');
    }
    if ($action === 'account_2fa_enable') {
        $secret = $_SESSION['pending_totp'] ?? '';
        if ($secret && Totp::verify($secret, (string) input('code'))) {
            Database::run('UPDATE admin_users SET totp_secret = ?, totp_enabled = 1 WHERE id = ?',
                [$secret, current_admin()['id']]);
            unset($_SESSION['pending_totp']);
            log_activity('2fa_enable', 'Enabled two-factor auth');
            flash('Two-factor authentication enabled.');
        } else {
            flash('Code did not match. Please try again.', 'error');
        }
        redirect('/admin.php?p=account');
    }
    if ($action === 'account_2fa_disable') {
        $me = Database::one('SELECT * FROM admin_users WHERE id = ?', [current_admin()['id']]);
        if (password_verify((string) ($_POST['password'] ?? ''), $me['password_hash'])) {
            Database::run('UPDATE admin_users SET totp_secret = NULL, totp_enabled = 0 WHERE id = ?', [$me['id']]);
            log_activity('2fa_disable', 'Disabled two-factor auth');
            flash('Two-factor authentication disabled.');
        } else {
            flash('Password incorrect — 2FA not changed.', 'error');
        }
        redirect('/admin.php?p=account');
    }

    /* ── Backups & exports (superadmin) ── */
    if (in_array($action, ['backup_db', 'export_inquiries', 'export_subscribers'], true)) {
        require_role('superadmin');
        if ($action === 'backup_db') {
            $tmp = sys_get_temp_dir() . '/galilea-backup-' . date('Ymd-His') . '.sqlite';
            // Server-generated path (no user input); VACUUM INTO needs a literal.
            Database::pdo()->exec("VACUUM INTO '" . str_replace("'", "''", $tmp) . "'");
            log_activity('backup', 'Downloaded database backup');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="galilea-backup-' . date('Ymd-His') . '.sqlite"');
            header('Content-Length: ' . filesize($tmp));
            readfile($tmp);
            @unlink($tmp);
            exit;
        }
        $map = [
            'export_inquiries' => ['inquiries', 'SELECT id,full_name,email,phone,company,service_interest,status,created_at FROM inquiries ORDER BY created_at DESC'],
            'export_subscribers' => ['subscribers', 'SELECT id,email,source,is_active,created_at FROM newsletter_subscribers ORDER BY created_at DESC'],
        ];
        [$label, $sql] = $map[$action];
        log_activity('export', $label);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="galilea-' . $label . '-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        $rows = Database::all($sql);
        if ($rows) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $r) fputcsv($out, $r);
        }
        fclose($out);
        exit;
    }

    /* ── User management (superadmin) ── */
    if (in_array($action, ['user_save', 'user_delete'], true)) {
        require_role('superadmin');
        if ($action === 'user_save') {
            $id = (int) input('id') ?: null;
            $username = input('username');
            $full = input('full_name');
            $role = in_array(input('role'), ['superadmin', 'editor'], true) ? input('role') : 'editor';
            $active = !empty($_POST['is_active']) ? 1 : 0;
            $pass = (string) ($_POST['password'] ?? '');
            $sections = array_values(array_intersect(
                array_keys(admin_sections()),
                (array) ($_POST['sections'] ?? [])
            ));
            $sectionsJson = json_encode($sections);
            try {
                if (strlen($username) < 3 || strlen($full) < 2) {
                    throw new RuntimeException('Username and full name are required.');
                }
                if ($id) {
                    Database::run("UPDATE admin_users SET username=?, full_name=?, role=?, is_active=?, allowed_sections=?, updated_at=datetime('now') WHERE id=?",
                        [$username, $full, $role, $active, $sectionsJson, $id]);
                    if ($pass !== '') {
                        if (strlen($pass) < 8) throw new RuntimeException('Password must be at least 8 characters.');
                        Database::run("UPDATE admin_users SET password_hash=?, password_changed_at=datetime('now') WHERE id=?",
                            [password_hash($pass, PASSWORD_DEFAULT), $id]);
                    }
                } else {
                    if (strlen($pass) < 8) throw new RuntimeException('Password must be at least 8 characters.');
                    Database::run('INSERT INTO admin_users (username, full_name, password_hash, role, is_active, allowed_sections) VALUES (?,?,?,?,?,?)',
                        [$username, $full, password_hash($pass, PASSWORD_DEFAULT), $role, $active, $sectionsJson]);
                }
                log_activity('user_save', $username);
                flash('User saved.');
            } catch (RuntimeException $e) {
                flash($e->getMessage(), 'error');
            } catch (PDOException $e) {
                flash('Username already exists.', 'error');
            }
            redirect('/admin.php?p=users');
        }
        if ($action === 'user_delete') {
            $id = (int) input('id');
            if ($id === (int) current_admin()['id']) {
                flash('You cannot delete your own account.', 'error');
            } else {
                Database::run('DELETE FROM admin_users WHERE id = ?', [$id]);
                log_activity('user_delete', 'User #' . $id);
                flash('User deleted.');
            }
            redirect('/admin.php?p=users');
        }
    }

    redirect('/admin.php?p=dashboard');
}

/* ──────────────────────────────  Render  ─────────────────────────────── */

$resources = admin_resources();

// Section access control.
$superadminOnly = ['settings', 'users', 'backup'];
if (in_array($p, $superadminOnly, true)) {
    require_role('superadmin');
} elseif (isset($resources[$p]) || in_array($p, ['inquiries', 'subscribers', 'activity'], true)) {
    require_access($p);
}

require dirname(__DIR__) . '/app/views/admin/layout_top.php';

if (isset($resources[$p])) {
    require dirname(__DIR__) . '/app/views/admin/resource.php';
} else {
    $viewMap = [
        'dashboard'   => 'dashboard.php',
        'settings'    => 'settings.php',
        'inquiries'   => 'inquiries.php',
        'subscribers' => 'subscribers.php',
        'users'       => 'users.php',
        'activity'    => 'activity.php',
        'account'     => 'account.php',
        'backup'      => 'backup.php',
    ];
    $view = $viewMap[$p] ?? 'dashboard.php';
    require dirname(__DIR__) . '/app/views/admin/' . $view;
}

require dirname(__DIR__) . '/app/views/admin/layout_bottom.php';
