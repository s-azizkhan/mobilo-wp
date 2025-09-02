<?php

namespace MobiloAuth\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * JWT Utility Class
 * 
 * @since 1.0.0
 */
class JWTUtil
{
    /**
     * Base64 URL decode a string
     *
     * @param string $input
     * @return string
     */
    private static function base64UrlDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Decode a JWT
     *
     * @param string $jwt
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function decode($jwt)
    {
        // Split the JWT into its three parts
        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new \InvalidArgumentException('Invalid JWT format');
        }

        list($header, $payload, $signature) = $parts;

        // Decode the header and payload
        $decodedHeader = json_decode(self::base64UrlDecode($header), true);
        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in decoded header or payload');
        }

        return array(
            'header' => $decodedHeader,
            'payload' => $decodedPayload,
            'signature' => $signature
        );
    }

    /**
     * Verify a JWT signature
     *
     * @param string $jwt
     * @param string $secretKey
     * @return bool
     */
    public static function verify($jwt, $secretKey)
    {
        list($header, $payload, $signature) = explode('.', $jwt);

        // Base64 URL decode the header and payload
        $decodedHeader = self::base64UrlDecode($header);
        $decodedPayload = self::base64UrlDecode($payload);

        // Re-encode the header and payload using base64 URL encoding
        $headerAndPayload = $header . '.' . $payload;

        // Compute the signature using the HMAC SHA256 algorithm and the secret key
        $computedSignature = hash_hmac('sha256', $headerAndPayload, $secretKey, true);
        $computedSignature = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($computedSignature));

        // Compare the computed signature with the provided signature
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Check if JWT is expired
     *
     * @param string $jwt
     * @return bool
     */
    public static function isExpired($jwt)
    {
        try {
            $decoded = self::decode($jwt);
            $exp = isset($decoded['payload']['exp']) ? $decoded['payload']['exp'] : null;

            if (!$exp) {
                return false; // No expiration claim
            }

            return $exp < time();
        } catch (Throwable $e) {
            return true; // Consider invalid tokens as expired
        }
    }

    /**
     * Get JWT payload data
     *
     * @param string $jwt
     * @return array|null
     */
    public static function getPayload($jwt)
    {
        try {
            $decoded = self::decode($jwt);
            return $decoded['payload'];
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Get JWT header data
     *
     * @param string $jwt
     * @return array|null
     */
    public static function getHeader($jwt)
    {
        try {
            $decoded = self::decode($jwt);
            return $decoded['header'];
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Extract user ID from JWT payload
     *
     * @param string $jwt
     * @return string|null
     */
    public static function getUserId($jwt)
    {
        $payload = self::getPayload($jwt);

        if (!$payload) {
            return null;
        }

        // Check for common user ID claims
        $user_id_claims = array('user_id', 'uid', 'sub', 'id');

        foreach ($user_id_claims as $claim) {
            if (isset($payload[$claim])) {
                return $payload[$claim];
            }
        }

        return null;
    }

    /**
     * Check if JWT is valid (not expired and has required claims)
     *
     * @param string $jwt
     * @param array $requiredClaims
     * @return bool
     */
    public static function isValid($jwt, $requiredClaims = array())
    {
        try {
            $decoded = self::decode($jwt);
            $payload = $decoded['payload'];

            // Check if expired
            if (self::isExpired($jwt)) {
                return false;
            }

            // Check required claims
            foreach ($requiredClaims as $claim) {
                if (!isset($payload[$claim])) {
                    return false;
                }
            }

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Create a simple JWT token
     *
     * @param array $payload
     * @param string $secretKey
     * @param int $expiration
     * @return string
     */
    public static function create($payload, $secretKey, $expiration = 3600)
    {
        $header = array(
            'alg' => 'HS256',
            'typ' => 'JWT'
        );

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiration;

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $data = $headerEncoded . '.' . $payloadEncoded;
        $signature = hash_hmac('sha256', $data, $secretKey, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return $data . '.' . $signatureEncoded;
    }

    /**
     * Base64 URL encode a string
     *
     * @param string $input
     * @return string
     */
    private static function base64UrlEncode($input)
    {
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($input));
    }
}
