<?php

namespace Mobilo\WpTheme\Utils;

use InvalidArgumentException;
use RuntimeException;

defined('ABSPATH') || exit;

/**
 * Class JWTUtil
 * @since 0.0.1
 * @version 1.0.0
 * @package Mobilo\WpTheme\Utils
 * @author S.Aziz Khan <hi@justaziz.com>
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function decode($jwt)
    {
        // Split the JWT into its three parts
        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new InvalidArgumentException('Invalid JWT format');
        }

        list($header, $payload, $signature) = $parts;

        // Decode the header and payload
        $decodedHeader = json_decode(self::base64UrlDecode($header), true);
        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON in decoded header or payload');
        }

        return [
            'header' => $decodedHeader,
            'payload' => $decodedPayload,
            'signature' => $signature
        ];
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
        $computedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($computedSignature));

        // Compare the computed signature with the provided signature
        return hash_equals($computedSignature, $signature);
    }
}
