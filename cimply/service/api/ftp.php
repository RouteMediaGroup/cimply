<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Service\Api {
    use \Cimply\Core\View\View;
    class Ftp {
        static function Upload(string $serverAddr, string $fileToTransfer, string $remoteFile, $username = null, $password = null, $type = FTP_ASCII, $port = null):bool {
            $result = false;
            // Verbindung aufbauen
            if(is_file($fileToTransfer)) {
                $connId = ftp_connect($serverAddr);
                // Login mit Benutzername und Passwort
                $loginResult = null;
                // Datei hochladen
                ($username !== '' && $password !== '') ? $loginResult = ftp_login($connId, $username, $password) : null;
                if (ftp_put($connId, $remoteFile, $fileToTransfer, $type)) {
                    $result = true;    
                }
                // Verbindung schließen
                ftp_close($connId);
            }
            return $result;
        }

        static function Download() {}

        static function Read() {}
    }
}