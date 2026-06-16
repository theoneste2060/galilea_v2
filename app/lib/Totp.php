<?php
declare(strict_types=1);

/**
 * Minimal, dependency-free TOTP (RFC 6238) for admin two-factor auth.
 * Compatible with Google Authenticator, Authy, 1Password, etc.
 */
final class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** Generate a new random base32 secret. */
    public static function secret(int $length = 20): string
    {
        $bytes = random_bytes($length);
        return self::base32Encode($bytes);
    }

    /** Verify a user-supplied code against the secret, allowing ±1 time step. */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6) {
            return false;
        }
        $time = (int) floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::code($secret, $time + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    /** Compute the 6-digit code for a given time counter. */
    public static function code(string $secret, int $counter): string
    {
        $key = self::base32Decode($secret);
        $bin = pack('N*', 0) . pack('N*', $counter); // 64-bit big-endian counter
        $hash = hash_hmac('sha1', $bin, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $part = (ord($hash[$offset]) & 0x7f) << 24
            | (ord($hash[$offset + 1]) & 0xff) << 16
            | (ord($hash[$offset + 2]) & 0xff) << 8
            | (ord($hash[$offset + 3]) & 0xff);
        return str_pad((string) ($part % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /** Build the otpauth:// provisioning URI (for QR codes / manual entry). */
    public static function uri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
            . '?secret=' . $secret
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    public static function base32Encode(string $data): string
    {
        $bits = '';
        foreach (str_split($data) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }
        return $out;
    }

    public static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
        $bits = '';
        foreach (str_split($b32) as $c) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $c)), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }
        return $out;
    }
}
