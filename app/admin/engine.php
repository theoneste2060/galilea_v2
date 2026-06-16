<?php
declare(strict_types=1);

/**
 * Generic admin CRUD engine. Renders form inputs from a field schema and
 * persists submissions with strict validation, upload handling and HTML
 * sanitisation.
 */

function admin_resources(): array
{
    static $res = null;
    if ($res === null) {
        $res = require dirname(__DIR__) . '/admin/resources.php';
    }
    return $res;
}

/** Sections an editor can be granted access to (key => label). */
function admin_sections(): array
{
    $sections = [];
    foreach (admin_resources() as $key => $res) {
        $sections[$key] = $res['label'];
    }
    $sections['inquiries'] = 'Inquiries';
    $sections['subscribers'] = 'Subscribers';
    $sections['media'] = 'Media Library';
    $sections['activity'] = 'Activity Logs';
    return $sections;
}

function resource_or_404(string $key): array
{
    $all = admin_resources();
    if (!isset($all[$key])) {
        http_response_code(404);
        exit('Unknown resource.');
    }
    return $all[$key];
}

/** Render a single form field. */
function render_field(string $name, array $field, $value): string
{
    $label = esc($field['label'] ?? $name);
    $req   = !empty($field['required']) ? ' <span style="color:#b91c1c">*</span>' : '';
    $type  = $field['type'];
    $val   = $value ?? ($field['default'] ?? '');
    $out   = '<div class="fg">';

    switch ($type) {
        case 'textarea':
            $out .= "<label class=\"fl\">$label$req</label>";
            $out .= '<textarea class="fta" name="' . esc($name) . '">' . esc((string) $val) . '</textarea>';
            break;

        case 'richtext':
            $out .= "<label class=\"fl\">$label$req</label>";
            $out .= '<textarea class="summernote" name="' . esc($name) . '">' . esc((string) $val) . '</textarea>';
            break;

        case 'image':
            $out .= "<label class=\"fl\">$label</label>";
            $out .= '<div class="img-drop" data-input="' . esc($name) . '_file">';
            if (!empty($val)) {
                $out .= '<div class="img-preview"><img src="' . esc((string) $val) . '" alt="current"></div>';
            }
            $out .= '<div class="img-drop-inner"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>'
                . '<span>Drag &amp; drop an image here, or click to browse</span><small>JPG, PNG, WEBP or GIF · max 4 MB</small></div>';
            $out .= '<input type="file" name="' . esc($name) . '_file" accept="image/png,image/jpeg,image/webp,image/gif" class="img-file" hidden>';
            $out .= '</div>';
            if (!empty($val)) {
                $out .= '<label class="img-remove"><input type="checkbox" name="' . esc($name) . '_remove" value="1"> Remove current image</label>';
            }
            break;

        case 'checkbox':
            $checked = !empty($val) ? ' checked' : '';
            $out .= '<label class="fl-check"><input type="checkbox" name="' . esc($name) . '" value="1"' . $checked . '> ' . $label . '</label>';
            break;

        case 'parent':
            $out .= "<label class=\"fl\">$label</label><select class=\"fsel\" name=\"" . esc($name) . '">';
            $out .= '<option value="">— Top-level (mega-menu heading) —</option>';
            $tops = Database::all('SELECT id, title FROM menu_items WHERE parent_id IS NULL ORDER BY sort_order, title');
            foreach ($tops as $t) {
                $sel = ((string) $val === (string) $t['id']) ? ' selected' : '';
                $out .= '<option value="' . (int) $t['id'] . '"' . $sel . '>' . esc($t['title']) . '</option>';
            }
            $out .= '</select>';
            break;

        case 'select':
            $out .= "<label class=\"fl\">$label$req</label><select class=\"fsel\" name=\"" . esc($name) . '">';
            foreach (($field['options'] ?? []) as $opt) {
                $sel = ((string) $val === (string) $opt) ? ' selected' : '';
                $out .= '<option value="' . esc($opt) . '"' . $sel . '>' . esc($opt) . '</option>';
            }
            $out .= '</select>';
            break;

        case 'number':
            $min = isset($field['min']) ? ' min="' . (int) $field['min'] . '"' : '';
            $max = isset($field['max']) ? ' max="' . (int) $field['max'] . '"' : '';
            $out .= "<label class=\"fl\">$label$req</label>";
            $out .= '<input class="fi" type="number"' . $min . $max . ' name="' . esc($name) . '" value="' . esc((string) ($val === '' ? 0 : $val)) . '">';
            break;

        case 'datetime':
            $dt = $val ? date('Y-m-d\TH:i', strtotime((string) $val)) : date('Y-m-d\TH:i');
            $out .= "<label class=\"fl\">$label$req</label>";
            $out .= '<input class="fi" type="datetime-local" name="' . esc($name) . '" value="' . esc($dt) . '">';
            break;

        case 'stages':
            $stages = is_string($val) ? (json_decode($val, true) ?: []) : (is_array($val) ? $val : []);
            $lines = [];
            foreach ($stages as $st) {
                $lines[] = ($st['label'] ?? '') . ' | ' . ($st['timestamp'] ?? '') . ' | ' . (!empty($st['completed']) ? '1' : '0');
            }
            $out .= "<label class=\"fl\">$label</label>";
            // Visual builder (JS-enhanced). The textarea stays as the canonical
            // value and is hidden when the editor mounts; it remains the
            // graceful fallback when JavaScript is unavailable.
            $out .= '<div class="stage-editor" data-stage-editor></div>';
            $out .= '<textarea class="fta stage-source" name="' . esc($name) . '" rows="5" placeholder="One stage per line: Label | Timestamp | 1 or 0">' . esc(implode("\n", $lines)) . '</textarea>';
            $out .= '<p class="fh">Add the milestones a customer sees when tracking. Mark a stage <strong>complete</strong> once it has happened.</p>';
            break;

        case 'slug':
            $out .= "<label class=\"fl\">$label</label>";
            $out .= '<input class="fi" type="text" name="' . esc($name) . '" value="' . esc((string) $val) . '" placeholder="auto-generated if blank">';
            break;

        case 'email':
            $out .= "<label class=\"fl\">$label$req</label>";
            $out .= '<input class="fi" type="email" name="' . esc($name) . '" value="' . esc((string) $val) . '">';
            break;

        default: // text
            $out .= "<label class=\"fl\">$label$req</label>";
            $out .= '<input class="fi" type="text" name="' . esc($name) . '" value="' . esc((string) $val) . '">';
    }
    $out .= '</div>';
    return $out;
}

/**
 * Persist a create/update for a resource. Returns the row id.
 * Throws RuntimeException with a user-safe message on validation failure.
 */
function save_resource(array $res, ?int $id): int
{
    $config = config();
    $table  = $res['table'];
    $cols   = [];
    $params = [];

    foreach ($res['fields'] as $name => $field) {
        $type = $field['type'];

        if ($type === 'image') {
            // Uploaded file wins; otherwise keep existing unless "remove" ticked.
            $uploaded = handle_image_upload($name . '_file', $config);
            if ($uploaded !== null) {
                $cols[$name] = $uploaded;
            } elseif (!empty($_POST[$name . '_remove'])) {
                $cols[$name] = null;
            }
            // else: leave column untouched (handled below for inserts).
            continue;
        }

        if ($type === 'checkbox') {
            $cols[$name] = !empty($_POST[$name]) ? 1 : 0;
            continue;
        }

        if ($type === 'richtext') {
            $cols[$name] = sanitize_html((string) ($_POST[$name] ?? ''));
            continue;
        }

        if ($type === 'stages') {
            $cols[$name] = json_encode(parse_stages((string) ($_POST[$name] ?? '')));
            continue;
        }

        if ($type === 'parent') {
            $pv = trim((string) ($_POST[$name] ?? ''));
            $cols[$name] = $pv === '' ? null : (int) $pv;
            continue;
        }

        if ($type === 'number') {
            $v = (int) ($_POST[$name] ?? ($field['default'] ?? 0));
            if (isset($field['min'])) $v = max((int) $field['min'], $v);
            if (isset($field['max'])) $v = min((int) $field['max'], $v);
            $cols[$name] = $v;
            continue;
        }

        if ($type === 'datetime') {
            $raw = trim((string) ($_POST[$name] ?? ''));
            $cols[$name] = $raw !== '' ? date('Y-m-d H:i:s', strtotime($raw)) : date('Y-m-d H:i:s');
            continue;
        }

        $value = trim((string) ($_POST[$name] ?? ''));

        if ($type === 'slug') {
            if ($value === '') {
                $src = trim((string) ($_POST[$field['source'] ?? ''] ?? ''));
                $value = slugify($src !== '' ? $src : ($field['source'] ?? 'item'));
            } else {
                $value = slugify($value);
            }
        }

        if ($type === 'email' && $value !== '' && !valid_email($value)) {
            throw new RuntimeException('Please enter a valid email for "' . ($field['label'] ?? $name) . '".');
        }

        if (!empty($field['required']) && $value === '') {
            throw new RuntimeException('"' . ($field['label'] ?? $name) . '" is required.');
        }

        $cols[$name] = $value;
    }

    if ($id) {
        // For images on update with no new file and no removal, skip the column.
        $set = [];
        foreach ($cols as $c => $v) {
            $set[] = "$c = ?";
            $params[] = $v;
        }
        // touch updated_at if present
        if (column_exists($table, 'updated_at')) {
            $set[] = "updated_at = datetime('now')";
        }
        $params[] = $id;
        try {
            Database::run("UPDATE $table SET " . implode(', ', $set) . ' WHERE id = ?', $params);
        } catch (PDOException $e) {
            throw new RuntimeException(friendly_db_error($e));
        }
        return $id;
    }

    // Insert: ensure image columns exist even if null.
    foreach ($res['fields'] as $name => $field) {
        if ($field['type'] === 'image' && !array_key_exists($name, $cols)) {
            $cols[$name] = null;
        }
    }
    $names = array_keys($cols);
    $place = implode(', ', array_fill(0, count($names), '?'));
    try {
        Database::run(
            "INSERT INTO $table (" . implode(', ', $names) . ") VALUES ($place)",
            array_values($cols)
        );
    } catch (PDOException $e) {
        throw new RuntimeException(friendly_db_error($e));
    }
    return (int) Database::pdo()->lastInsertId();
}

function parse_stages(string $text): array
{
    $out = [];
    foreach (preg_split('/\r?\n/', $text) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = array_map('trim', explode('|', $line));
        $out[] = [
            'label'     => $parts[0] ?? '',
            'timestamp' => $parts[1] ?? '',
            'completed' => isset($parts[2]) && in_array($parts[2], ['1', 'true', 'yes'], true),
        ];
    }
    return $out;
}

function column_exists(string $table, string $col): bool
{
    static $cache = [];
    if (!isset($cache[$table])) {
        $cache[$table] = [];
        foreach (Database::all("PRAGMA table_info($table)") as $c) {
            $cache[$table][$c['name']] = true;
        }
    }
    return isset($cache[$table][$col]);
}

function friendly_db_error(PDOException $e): string
{
    if (str_contains($e->getMessage(), 'UNIQUE')) {
        return 'That value must be unique — a record with the same key/slug already exists.';
    }
    error_log('[galilea] db error: ' . $e->getMessage());
    return 'Could not save — please check your input and try again.';
}

function delete_resource(array $res, int $id): void
{
    Database::run('DELETE FROM ' . $res['table'] . ' WHERE id = ?', [$id]);
}
