<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace { 
    abstract class Crypto {      
        
        public static function Encrypt($data, $cipher = "", $pepper = "") : string {
            $cipher = $cipher !== '' ? $cipher : 'AES-256-CBC';
            $pepper = (string)$pepper;
            $serialized = serialize($data);
            $ivlen = \openssl_cipher_iv_length($cipher);
            if ($ivlen === false) {
                throw new \RuntimeException("Unsupported cipher '{$cipher}'.");
            }

            $iv = \random_bytes($ivlen);
            $ciphertext_raw = \openssl_encrypt($serialized, $cipher, $pepper, OPENSSL_RAW_DATA, $iv);
            if (!\is_string($ciphertext_raw)) {
                return '';
            }

            $hmac = \hash_hmac('sha256', $ciphertext_raw, $pepper, true);
            $ciphertext = \base64_encode($iv.$hmac.$ciphertext_raw);

            return \base64_encode($pepper . \gzcompress($ciphertext));
        }

        public static function Decrypt($data, $cipher = "", $pepper = "") {
            $cipher = $cipher !== '' ? $cipher : 'AES-256-CBC';
            $pepper = (string)$pepper;

            if ($data === '=') {
                return '';
            }

            $dataDecode = \base64_decode((string)$data, true);
            if (!\is_string($dataDecode) || !str_starts_with($dataDecode, $pepper)) {
                return false;
            }
            
            $ciphertext = \gzuncompress(substr($dataDecode, strlen($pepper)));
            if (!\is_string($ciphertext)) {
                return false;
            }

            $decodedCipher = \base64_decode($ciphertext, true);
            if (!\is_string($decodedCipher)) {
                return false;
            }

            $ivlen = \openssl_cipher_iv_length($cipher);
            if ($ivlen === false) {
                return false;
            }

            $iv = substr($decodedCipher, 0, $ivlen);
            $hmac = substr($decodedCipher, $ivlen, 32);
            $ciphertext_raw = substr($decodedCipher, $ivlen + 32);
            $original_plaintext = \openssl_decrypt($ciphertext_raw, $cipher, $pepper, OPENSSL_RAW_DATA, $iv);

            if (!\is_string($original_plaintext)) {
                return false;
            }

            $calcmac = \hash_hmac('sha256', $ciphertext_raw, $pepper, true);
            if (\hash_equals($hmac, $calcmac)) {
                return \unserialize($original_plaintext, ['allowed_classes' => true]);
            }

            return false;
        }
        
        public static function NoSSLEncrypt($array, $salt = "", $pepper = "") {
            return self::Encrypt($array, 'AES-256-CBC', hash('sha256', (string)$salt . (string)$pepper));
        }

        public static function NoSSLDecrypt($data, $salt = "", $pepper = "") {
            return self::Decrypt($data, 'AES-256-CBC', hash('sha256', (string)$salt . (string)$pepper));
        }
        
    }
}
