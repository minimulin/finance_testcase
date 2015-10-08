<?php

namespace AppBundle\Utils;


class JsonpYahooResponseParser implements IYahooResponseParser
{

    public function parse($string)
    {
        return static::jsonpDecode($string, true);
    }

    protected static function jsonpDecode($jsonp, $assoc = false)
    {
        if ($jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return json_decode(trim($jsonp, '();'), $assoc);
    }

    public function getFormat()
    {
        return 'json';
    }
}
