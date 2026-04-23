<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Traits {
    trait Secure {
        /**
         * Add
         * @param array $secureFiles
         */
        public static function Add(array $secureFiles = []): void {
            foreach($secureFiles as $files) {
                if (is_file($files)) {
                    self::DeCryptFile($files);
                } else {
                    self::AddFiles($files);
                }
            }
        }

        /**
         * AddFiles
         * @param string $secureDirectory
         */
        private static function AddFiles(string $secureDirectory = ''): void {
            if (!empty($secureDirectory)) {
                $secureFiles = array_diff(scandir($secureDirectory), ['..', '.']);
                foreach($secureFiles as $file) {
                    if (strpos($file, '.php') !== false || strpos($file, '.') === false) {
                        $filePath = $secureDirectory . DIRECTORY_SEPARATOR . $file;
                        self::Add([$filePath]);
                    }
                }
            }
        }
    }
}
