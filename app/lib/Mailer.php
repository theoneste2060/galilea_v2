<?php
declare(strict_types=1);

/**
 * Minimal, dependency-free SMTP mailer.
 *
 * Speaks just enough SMTP to deliver a single HTML message with AUTH LOGIN over
 * STARTTLS (port 587), implicit SSL (port 465), or an unencrypted connection.
 * Falls back to PHP's mail() when no SMTP host is configured. All settings come
 * from the admin Site Settings (the "email" group).
 */
class Mailer
{
    private string $lastError = '';

    /** @var string[] transcript of the SMTP conversation (for debugging). */
    private array $log = [];

    public function lastError(): string { return $this->lastError; }
    public function log(): array { return $this->log; }

    /**
     * Send an HTML email. Returns true on success; on failure returns false and
     * exposes the reason via lastError().
     */
    public function send(string $toEmail, string $subject, string $htmlBody, ?string $replyTo = null): bool
    {
        $cfg = self::config();
        if ($cfg['enabled'] !== '1') {
            $this->lastError = 'Email sending is disabled in Site Settings.';
            return false;
        }
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $this->lastError = 'Invalid recipient address.';
            return false;
        }
        if ($cfg['from_email'] === '') {
            $this->lastError = 'No "From" address configured.';
            return false;
        }

        // No SMTP host → use the local mail() transport.
        if ($cfg['host'] === '') {
            return $this->sendViaMail($toEmail, $subject, $htmlBody, $cfg, $replyTo);
        }
        return $this->sendViaSmtp($toEmail, $subject, $htmlBody, $cfg, $replyTo);
    }

    /** Pull the email settings into a normalised array. */
    public static function config(): array
    {
        return [
            'enabled'    => setting('email_enabled', '0'),
            'host'       => trim(setting('smtp_host', '')),
            'port'       => (int) (setting('smtp_port', '587') ?: 587),
            'secure'     => setting('smtp_secure', 'tls'),   // tls | ssl | none
            'user'       => setting('smtp_user', ''),
            'pass'       => setting('smtp_pass', ''),
            'from_email' => trim(setting('mail_from_email', '')) ?: trim(setting('site_email', '')),
            'from_name'  => setting('mail_from_name', '') ?: setting('site_name', 'Galilea Global Logistics'),
        ];
    }

    private function headers(string $toEmail, string $subject, array $cfg, ?string $replyTo): array
    {
        $from = '=?UTF-8?B?' . base64_encode($cfg['from_name']) . '?= <' . $cfg['from_email'] . '>';
        $h = [
            'Date: ' . date('r'),
            'From: ' . $from,
            'To: ' . $toEmail,
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $h[] = 'Reply-To: ' . $replyTo;
        }
        return $h;
    }

    private function sendViaMail(string $to, string $subject, string $html, array $cfg, ?string $replyTo): bool
    {
        $headers = $this->headers($to, $subject, $cfg, $replyTo);
        // mail() takes the Subject/To separately, so drop them from the header list.
        $headers = array_values(array_filter($headers, fn($l) => !str_starts_with($l, 'To: ') && !str_starts_with($l, 'Subject: ')));
        $ok = @mail($to, $subject, chunk_split(base64_encode($html)), implode("\r\n", $headers));
        if (!$ok) {
            $this->lastError = 'PHP mail() failed (no SMTP host set and the local mailer is unavailable).';
        }
        return $ok;
    }

    private function sendViaSmtp(string $to, string $subject, string $html, array $cfg, ?string $replyTo): bool
    {
        $transport = $cfg['secure'] === 'ssl' ? 'ssl://' : '';
        $errno = 0; $errstr = '';
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $fp = @stream_socket_client($transport . $cfg['host'] . ':' . $cfg['port'], $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            $this->lastError = "Could not connect to {$cfg['host']}:{$cfg['port']} ($errstr)";
            return false;
        }
        stream_set_timeout($fp, 10);

        try {
            $this->expect($fp, 220);
            $ehloHost = $this->ehloName($cfg['from_email']);
            $this->cmd($fp, "EHLO $ehloHost", 250);

            if ($cfg['secure'] === 'tls') {
                $this->cmd($fp, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                    throw new RuntimeException('STARTTLS negotiation failed.');
                }
                $this->cmd($fp, "EHLO $ehloHost", 250);
            }

            if ($cfg['user'] !== '') {
                $this->cmd($fp, 'AUTH LOGIN', 334);
                $this->cmd($fp, base64_encode($cfg['user']), 334);
                $this->cmd($fp, base64_encode($cfg['pass']), 235);
            }

            $this->cmd($fp, 'MAIL FROM:<' . $cfg['from_email'] . '>', 250);
            $this->cmd($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->cmd($fp, 'DATA', 354);

            $body = implode("\r\n", $this->headers($to, $subject, $cfg, $replyTo))
                . "\r\n\r\n" . chunk_split(base64_encode($html));
            // Dot-stuffing: any line starting with '.' must be escaped.
            $body = preg_replace('/^\./m', '..', $body);
            $this->cmd($fp, $body . "\r\n.", 250);
            $this->cmd($fp, 'QUIT', [221]);
            fclose($fp);
            return true;
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            @fclose($fp);
            return false;
        }
    }

    private function ehloName(string $fromEmail): string
    {
        $host = $_SERVER['SERVER_NAME'] ?? '';
        if ($host === '' && str_contains($fromEmail, '@')) {
            $host = substr(strrchr($fromEmail, '@'), 1);
        }
        return $host !== '' ? $host : 'localhost';
    }

    /** Send a command and assert the reply code. */
    private function cmd($fp, string $line, $expected): void
    {
        fwrite($fp, $line . "\r\n");
        // Don't log credentials.
        $this->log[] = '> ' . (preg_match('/^[A-Za-z0-9+\/]{8,}={0,2}$/', $line) ? '[redacted]' : substr($line, 0, 80));
        $this->expect($fp, $expected);
    }

    /** Read a (possibly multi-line) reply and check the status code. */
    private function expect($fp, $expected): void
    {
        $expected = (array) $expected;
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            // Lines like "250-..." continue; "250 ..." terminates.
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $this->log[] = '< ' . trim($data);
        $code = (int) substr($data, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new RuntimeException('SMTP error: ' . trim($data));
        }
    }
}

/* ───────────────────────────  Convenience API  ───────────────────────────── */

/**
 * Wrap content in the branded HTML email shell.
 */
function email_template(string $heading, string $bodyHtml): string
{
    $name = esc(setting('site_name', 'Galilea Global Logistics'));
    $year = date('Y');
    return '<!DOCTYPE html><html><body style="margin:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1a2332">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:24px 0"><tr><td align="center">'
        . '<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(6,21,40,.08)">'
        . '<tr><td style="background:#0D2645;padding:22px 28px;color:#fff;font-size:18px;font-weight:bold">' . $name . '</td></tr>'
        . '<tr><td style="padding:28px"><h2 style="margin:0 0 14px;color:#0D2645;font-size:19px">' . esc($heading) . '</h2>'
        . $bodyHtml . '</td></tr>'
        . '<tr><td style="padding:16px 28px;background:#f5f7fa;color:#8a95a7;font-size:12px">&copy; ' . $year . ' ' . $name . ' · This is an automated message.</td></tr>'
        . '</table></td></tr></table></body></html>';
}

/**
 * Send the admin notification + optional customer auto-reply for a new inquiry.
 * Failures are swallowed (logged to the activity feed) so they never block the
 * public submission.
 */
function notify_new_inquiry(array $d): void
{
    if (setting('email_enabled', '0') !== '1') {
        return;
    }
    $mailer = new Mailer();

    // 1) Notify the team.
    $to = trim(setting('inquiry_notify_email', '')) ?: trim(setting('site_email', ''));
    if ($to !== '') {
        $rows = '';
        foreach ([
            'Name' => $d['name'], 'Email' => $d['email'], 'Phone' => $d['phone'] ?: '—',
            'Company' => $d['company'] ?: '—', 'Service' => $d['service'],
        ] as $k => $v) {
            $rows .= '<tr><td style="padding:6px 12px 6px 0;color:#5A6478;font-weight:bold;vertical-align:top">' . esc($k) . '</td><td style="padding:6px 0">' . esc($v) . '</td></tr>';
        }
        $body = '<table role="presentation" style="font-size:14px;border-collapse:collapse">' . $rows . '</table>'
            . '<div style="margin-top:18px;padding:14px 16px;background:#f5f7fa;border-radius:8px;font-size:14px;white-space:pre-wrap">' . esc($d['message']) . '</div>'
            . '<p style="margin-top:18px"><a href="' . esc(base_url()) . '/admin.php?p=inquiries" style="background:#C9A84C;color:#061528;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:bold;display:inline-block">View in admin</a></p>';
        $ok = $mailer->send($to, 'New inquiry from ' . $d['name'], email_template('New website inquiry', $body), $d['email']);
        if (!$ok && function_exists('log_activity')) {
            log_activity('email_error', 'Inquiry notification: ' . $mailer->lastError());
        }
    }

    // 2) Acknowledge the customer.
    if (setting('mail_autoreply', '0') === '1' && filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $body = '<p style="font-size:14px;line-height:1.6">Hi ' . esc($d['name']) . ',</p>'
            . '<p style="font-size:14px;line-height:1.6">Thank you for reaching out to ' . esc(setting('site_name', 'Galilea Global Logistics')) . '. We have received your request regarding <strong>' . esc($d['service']) . '</strong> and a member of our team will be in touch shortly.</p>'
            . '<p style="font-size:14px;line-height:1.6">For anything urgent, call us on ' . esc(setting('phone_rw', '')) . '.</p>'
            . '<p style="font-size:14px;line-height:1.6">Warm regards,<br>The ' . esc(setting('site_name', 'Galilea')) . ' Team</p>';
        $mailer->send($d['email'], 'We received your request — ' . setting('site_name', 'Galilea Global Logistics'), email_template('Thanks for getting in touch', $body));
    }
}
