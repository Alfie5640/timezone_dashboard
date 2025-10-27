<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class JwtHelper {
    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function verifyJWT($jwt, $secret) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;

        list($header64, $payload64, $signature64) = $parts;

        $signature = hash_hmac('sha256', "$header64.$payload64", $secret, true);
        $expectedSig = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        if (!hash_equals($expectedSig, $signature64)) return false;

        $payload = json_decode(self::base64UrlDecode($payload64), true);
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) return false;

        return $payload;
    }

    public static function getBearerToken() {
        $headers = getallheaders();
        if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
        return null;
    }
}