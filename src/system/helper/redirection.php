<?php

/*
# 200 OK 
# 301 Moved Permanently 
# 302 Found 
# 304 Not Modified 
# 307 Temporary Redirect 
# 400 Bad Request 
# 401 Unauthorized 
# 403 Forbidden 
# 404 Not Found 
# 500 Internal Server Error 
# 501 Not Implemented
 */

/**
 * Description of CIM
 *
 * @author MikeCorner
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
