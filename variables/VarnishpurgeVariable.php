<?php
namespace Craft;

class VarnishpurgeVariable
{

    /**
     * Gets the client IP, accounting for request being routed through Varnish (HTTP_X_FORWARDED_FOR header set)
     * 
     * @return string
     */
    public function clientip()
    {
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        // X-forwarded-for could be a comma-delimited list of all ip's the request was routed through.
        // The last ip in the list is expected to be the users originating ip.
        if (strpos($ip, ',') !== false) {
            $arr = explode(',', $ip);
            $ip = trim(array_pop($arr), " ");
        }

        return $ip;
    }
    
}