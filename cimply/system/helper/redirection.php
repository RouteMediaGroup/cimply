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
    abstract class Redirection {

        /**
         * 
         * @param type $path
         * @param type $redirect
         * @param type $tries
         * @param type $lastRoute
         * @return type
         * 
         */
        public static function Redirect($path = '/index', $redirect = true, $tries = 10, $lastRoute = null) {
            isset($lastRoute) ? \Session::SetSession('LastRoute', $lastRoute) : null;
            if($redirect) {
                header("Location: ".$path); 
                exit;
            } else {
                return "Location: ".$path;
            }
        }
        
        /**
         * 
         * @param type $path
         * @param type $status
         * @param type $text
         * @param type $lastRoute
         * 
         */
        public static function PageNotFound($path = '/404', $status = '404', $text = 'Page Not Found', $lastRoute = false) {
            //\Session::SetSession('LastRoute', $lastRoute);
            header("HTTP/1.0 ".$status." ".$text);
            //die($text);
        }
        
        /**
         * 
         * @param type $path
         * @param type $status
         * @param type $text
         * @param type $lastRoute
         * 
         */
        public static function NoCaching() {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
            header("Cache-Control: no-cache"); 
            header("Pragma: no-cache");  
        }
    }
}
