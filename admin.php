<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/admin/engine.php';

send_security_headers(true); // allow Summernote CDN in CSP

$config = config();
$p = input('p') ?: (is_authenticated() ? 'dashboard' : 'login');

/* ─────────────────────────────── LOGIN ───────────────────────────────── */

if ($p === 'logout') {
    if (is_post()) {
        csrf_check();
        log_activity('logout');
        $_SESSION = [];
        session_regenerate_id(true);
    }
    redirect('/admin.php?p=login');
}

if ($p === 'login') {
    $error = null;
    if (is_authenticated()) {
        redirect('/admin.php?p=dashboard');
    }
    if (is_post()) {
        csrf_check();
        $ip = client_ip();

        // Rate limiting per IP.
        Database::run("DELETE FROM login_attempts WHERE attempted_at < datetime('now', ?)",
            ['-' . (int) $config['login_lockout_secs'] . ' seconds']);
        $recent = (int) Database::value('SELECT COUNT(*) FROM login_attempts WHERE ip = ?', [$ip]);

        if ($recent >= $config['max_login_attempts']) {
            $error = 'Too many attempts. Please wait a few minutes and try again.';
        } else {
            $username = input('username');
            $password = (string) ($_POST['password'] ?? '');
            $user = Database::one('SELECT * FROM admin_users WHERE username = ? AND is_active = 1', [$username]);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Rehash if algorithm parameters changed.
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    Database::run('UPDATE admin_users SET password_hash = ? WHERE id = ?',
                        [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
                }
                Database::run('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
                session_regenerate_id(true);
                $_SESSION['admin'] = [
                    'id' => (int) $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                ];
                log_activity('login', 'Signed in');
                redirect('/admin.php?p=dashboard');
            } else {
                Database::run('INSERT INTO login_attempts (ip) VALUES (?)', [$ip]);
                $error = 'Invalid username or password.';
            }
        }
    }
    require __DIR__ . '/app/views/admin/login.php';
    exit;
}

/* ─────────────────────  Everything below requires auth  ──────────────── */
require_admin();

/* ─────────────────────────────  Actions  ─────────────────────────────── */

$action = input('action');

// Generic resource save / delete / toggle.
if ($action && is_post()) {
    csrf_check();
    $resources = admin_resources();
    $key = input('resource');

    if (isset($resources[$key])) {
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

    // Settings.
    if ($action === 'save_settings') {
        foreach (($_POST['setting'] ?? []) as $k => $v) {
            Database::run("UPDATE site_settings SET value = ?, updated_at = datetime('now') WHERE key = ?",
                [trim((string) $v), (string) $k]);
        }
        log_activity('update', 'Site settings');
        flash('Settings saved.');
        redirect('/admin.php?p=settings');
    }

    // Inquiry status / delete.
    if ($action === 'inquiry_status') {
        Database::run('UPDATE inquiries SET status = ? WHERE id = ?',
            [input('status') ?: 'new', (int) input('id')]);
        flash('Inquiry updated.');
        redirect('/admin.php?p=inquiries');
    }
    if ($action === 'inquiry_delete') {
        Database::run('DELETE FROM inquiries WHERE id = ?', [(int) input('id')]);
        flash('Inquiry deleted.');
        redirect('/admin.php?p=inquiries');
    }
    if ($action === 'subscriber_delete') {
        Database::run('DELETE FROM newsletter_subscribers WHERE id = ?', [(int) input('id')]);
        flash('Subscriber removed.');
        redirect('/admin.php?p=subscribers');
    }

    // User management (superadmin only).
    if (in_array($action, ['user_save', 'user_delete'], true)) {
        require_role('superadmin');
        if ($action === 'user_save') {
            $id = (int) input('id') ?: null;
            $username = input('username');
            $full = input('full_name');
            $role = in_array(input('role'), ['superadmin', 'editor'], true) ? input('role') : 'editor';
            $active = !empty($_POST['is_active']) ? 1 : 0;
            $pass = (string) ($_POST['password'] ?? '');
            try {
                if (strlen($username) < 3 || strlen($full) < 2) {
                    throw new RuntimeException('Username and full name are required.');
                }
                if ($id) {
                    Database::run('UPDATE admin_users SET username=?, full_name=?, role=?, is_active=?, updated_at=datetime(\'now\') WHERE id=?',
                        [$username, $full, $role, $active, $id]);
                    if ($pass !== '') {
                        if (strlen($pass) < 8) throw new RuntimeException('Password must be at least 8 characters.');
                        Database::run('UPDATE admin_users SET password_hash=? WHERE id=?',
                            [password_hash($pass, PASSWORD_DEFAULT), $id]);
                    }
                } else {
                    if (strlen($pass) < 8) throw new RuntimeException('Password must be at least 8 characters.');
                    Database::run('INSERT INTO admin_users (username, full_name, password_hash, role, is_active) VALUES (?,?,?,?,?)',
                        [$username, $full, password_hash($pass, PASSWORD_DEFAULT), $role, $active]);
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
require __DIR__ . '/app/views/admin/layout_top.php';

if (isset($resources[$p])) {
    require __DIR__ . '/app/views/admin/resource.php';
} else {
    $viewMap = [
        'dashboard'   => 'dashboard.php',
        'settings'    => 'settings.php',
        'inquiries'   => 'inquiries.php',
        'subscribers' => 'subscribers.php',
        'users'       => 'users.php',
        'activity'    => 'activity.php',
    ];
    $view = $viewMap[$p] ?? 'dashboard.php';
    require __DIR__ . '/app/views/admin/' . $view;
}

require __DIR__ . '/app/views/admin/layout_bottom.php';
