<?php
namespace Cimply\Service\Api {
    use \Cimply\Core\View\View;
    class Https {
        static function Request(string $serviceAddr, $method = 'POST', $protocol = 'https', $port = null, $username = '', $passwort = '') {
            $protocol = \strtolower($protocol);
            $data = "";
            $opts = [
                'http' => [
                    'method'  => $method,
                    'header' => empty($username) ? "Content-type: application/x-www-form-urlencoded" : "Authorization: Basic " . base64_encode("{$username}:{$passwort}"),
                    'content' => \http_build_query(View::GetVars())
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ];
            $url = (strpos($serviceAddr, "://") === false ? "{$protocol}://" : "").self::UrlConvert($serviceAddr).($port ? ":{$port}" : "");
            
            if(($data = \file_get_contents($url, false, stream_context_create($opts))) === false) {
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                curl_close($ch);
            }
            $returnValue = (is_array($data)) ? \json_decode($data) : $data;

            return ($returnValue);
        }
        static function Service(string $serviceAddr, $method = 'POST', $protocol = 'https', $port = null) {
            $protocol = \strtolower($protocol);
            $opts = [
                'http' => [
                    'method'  => $method,
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query(View::GetVars())
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ];
            return View::Show(\file_get_contents((!empty($protocol) ? $protocol."://" : $protocol).self::UrlConvert($serviceAddr).($port ? ":{$port}" : ""), false, stream_context_create($opts)), true);
        }
        static function UrlConvert($string) {
			$entities = [' ', '!', '*', "'", "(", ")", ";", ":", "$", ",", "[", "]"];
			$replacements = ['%20', '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%24', '%2C', '%5B', '%5D'];
			return str_replace($entities, $replacements, $string);
		}
    }
}