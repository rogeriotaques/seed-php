<?php

/** 
 * Seed-PHP Microframework
 * @copyright Abtz Labs
 * @license MIT
 * @see http://github.com/abtzco/seed-php
 */

namespace SeedPHP\Helper;

class Http
{

  // HTTP Status Codes
    const _CONTINUE = 100;
    const _SWITCHING_PROTOCOLS = 101;
    const _OK = 200;
    const _CREATED = 201;
    const _ACCEPTED = 202;
    const _NON_AUTHORITATIVE_INFORMATION = 203;
    const _NO_CONTENT = 204;
    const _RESET_CONTENT = 205;
    const _PARTIAL_CONTENT = 206;
    const _MULTIPLE_CHOICES = 300;
    const _MOVED_PERMANENTLY = 301;
    const _MOVED_TEMPORARILY = 302;
    const _SEE_OTHER = 303;
    const _NOT_MODIFIED = 304;
    const _USE_PROXY = 305;
    const _BAD_REQUEST = 400;
    const _UNAUTHORIZED = 401;
    const _PAYMENT_REQUIRED = 402;
    const _FORBIDDEN = 403;
    const _NOT_FOUND = 404;
    const _METHOD_NOT_ALLOWED = 405;
    const _NOT_ACCEPTABLE = 406;
    const _PROXY_AUTHENTICATION_REQUIRED = 407;
    const _REQUEST_TIMEOUT = 408;
    const _CONFLICT = 409;
    const _GONE = 410;
    const _LENGTH_REQUIRED = 411;
    const _PRECONDITION_FAILED = 412;
    const _REQUEST_ENTITY_TOO_LARGE = 413;
    const _REQUEST_URI_TOO_LARGE = 414;
    const _UNSUPPORTED_MEDIA_TYPE = 415;
    const _INTERNAL_SERVER_ERROR = 500;
    const _NOT_IMPLEMENTED = 501;
    const _BAD_GATEWAY = 502;
    const _SERVICE_UNAVAILABLE = 503;
    const _GATEWAY_TIMEOUT = 504;
    const _HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Retrieve the right http text string according to given code.
     * @param integer $code - Default is 200.
     * @return array
     * @throws ErrorException
     */
    public static function getHTTPStatus($code = 200)
    {
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.');

        switch ($code) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                throw new \ErrorException('Unknown http status code "' . htmlentities($code) . '".', $code);
            }

        return ['protocol' => $protocol, 'code' => $code, 'message' => $text];
    } // getHTTPStatus

    /**
     * Returns the project base URL.
     * @param variant [$protocol] - Default is false. Should be 'http', 'https' or false.
     * @return string
     */
    public static function getBaseUrl($protocol = false)
    {
        $_base = str_replace(array('\\', ''), array('/', '%2'), dirname($_SERVER['SCRIPT_NAME']));

        // tries to figure out what is the right protocol if it's not given
        if (false === $protocol) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443)
                ? 'https'
                : 'http';
        }

        $_base = sprintf(
            "%s://%s%s",
            $protocol,
            $_SERVER['SERVER_NAME'],
            $_base
        );

        // remove trailing slash (if any) and return
        return preg_replace('/\/$/', '', $_base);
    } // getBaseUrl

    /**
     * Return the client IP address.
     * @return string
     */
    public static function getClientIP()
    {
        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_I'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_I'];
        } else {
            if (isset($_SERVER['HTTP_X_FORWARDED_FO'])) {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FO'];
            } else {
                if (isset($_SERVER['HTTP_X_FORWARDED'])) {
                    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
                } else {
                    if (isset($_SERVER['HTTP_FORWARDED_FO'])) {
                        $ipaddress = $_SERVER['HTTP_FORWARDED_FO'];
                    } else {
                        if (isset($_SERVER['HTTP_FORWARDED'])) {
                            $ipaddress = $_SERVER['HTTP_FORWARDED'];
                        } else {
                            if (isset($_SERVER['REMOTE_ADDR'])) {
                                $ipaddress = $_SERVER['REMOTE_ADDR'];
                            } else {
                                $ipaddress = 'UNKNOWN';
                            }
                        }
                    }
                }
            }
        }

        return $ipaddress;
    } // getClientIP
} // class
