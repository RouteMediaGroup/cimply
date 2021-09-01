<?php
namespace { 
    abstract class Crypto {      
        
        public static function Encrypt($data, $cipher = "", $pepper = "") : string {
            $serialized = serialize($data);
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($serialized, $cipher, $pepper, $options=OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $pepper, $as_binary = true);
            $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
            return base64_encode($pepper . gzcompress($ciphertext));
        }

        public static function Decrypt($data, $cipher = "", $pepper = "") {
            if($data !== '=') {
                $dataDecode = base64_decode($data);
                if (substr($dataDecode, 0, strlen($pepper)) != $pepper)
                    return false;
                
                $ciphertext = gzuncompress(substr($dataDecode, strlen($pepper)));
                $c = base64_decode($ciphertext);
                $ivlen = openssl_cipher_iv_length($cipher);
                $iv = substr($c, 0, $ivlen);
                $hmac = substr($c, $ivlen, $sha2len=32);
                $ciphertext_raw = substr($c, $ivlen+$sha2len);
                $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $pepper, $options=OPENSSL_RAW_DATA, $iv);
                $calcmac = hash_hmac('sha256', $ciphertext_raw, $pepper, $as_binary = true);
                if (hash_equals($hmac, $calcmac))
                {
                    return unserialize($original_plaintext);
                }
            }
            return '';
        }
        
        public static function NoSSLEncrypt($array, $salt = "", $pepper = "") {
            $serialized = serialize($array);
            $crypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $serialized, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
            return \base64_encode($pepper . gzcompress($crypted));
        }

        public static function NoSSLDecrypt($data, $salt = "", $pepper = "") {
            $data = \base64_decode($data);
            if (substr($data, 0, strlen($pepper)) != $pepper)
                return false;
            $data = substr($data, strlen($pepper));
            $uncompressed = gzuncompress($data);
            $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, $uncompressed, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
            return unserialize($decrypted);
        }
        
    }
}