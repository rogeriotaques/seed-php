<?php

/**
 * Seed-PHP Microframework
 * @copyright Rogerio Taques
 * @license MIT
 * @see http://github.com/rogeriotaques/seed-php
 */

namespace SeedPHP\Helper;

use ErrorException;
use SeedPHP\Helper\Http;

/**
 * A simple class to help managing and throttling calls to an API.
 */
class RateLimit
{
    const SESSION_NAME = 'rate-limit';
    const SESSION_THROTTLING_BOUNDARY = 5; # times

    private $_limit_per_second;
    private $_limit_per_minute;
    private $_limit_per_hour;
    private $_seconds_banned;
    private $_seconds_delay;

    /**
     * Initialize the class with the initial config details.
     *
     * @param array $config 
     * An array<key => value>
     *   - int limit_per_second - Define how many calls are accepted per second
     *   - int limit_per_minute - Define how many calls are accepted per minute
     *   - int limit_per_hour   - Define how many calls are accepted per hour
     *   - int seconds_banned   - Define how many seconds the API should be unavailable
     *   - int seconds_delay    - Define how many seconds the API should throttling the response, when boundaries are reached
     */
    public function __construct(array $config = [])
    {
        // Attempt to start a session
        if (!@session_id()) {
            if (!@session_start()) {
                throw new ErrorException("SeedPHP :: RateLimit : Impossible to start the session.", Http::_INTERNAL_SERVER_ERROR);
            }
        }

        $this->_limit_per_second = (int) ($config['limit_per_second'] ?? 2);
        $this->_limit_per_minute = (int) ($config['limit_per_minute'] ?? round($this->_limit_per_second * 60 * 0.7));
        $this->_limit_per_hour   = (int) ($config['limit_per_hour'] ?? round($this->_limit_per_minute * 60 * 0.5));
        $this->_seconds_banned   = (int) ($config['seconds_banned'] ?? 600); # 10 minutes
        $this->_seconds_delay    = (int) ($config['seconds_delay'] ?? 3); # 3 seconds

        if (!isset($_SESSION[RateLimit::SESSION_NAME])) {
            $_SESSION[RateLimit::SESSION_NAME] = [];
        }
    } // __construct

    /**
     * Test the call, throttling or denying it depending on the circunstances.
     *
     * @return void
     */
    public function test(): void
    {
        $clientIP = Http::getClientIP();
        $callTime = date('Y-m-d H:i:s', time());

        if (isset($_SESSION[RateLimit::SESSION_NAME][ $clientIP ])) {
            $lastCall = strtotime($_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['last-call']);
            $thisCall = strtotime($callTime);

            $_notBefore = $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['not-before'];
            $_seconds   = $thisCall - $lastCall;
            $_minutes   = round($_seconds / 60);
            $_hours     = round($_minutes / 60);

            if ($_seconds <= 1) {
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['sec'] += 1;
            } else {
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['sec'] = 0;
            }

            if ($_minutes <= 1) {
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['min'] += 1;
            } else {
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['min'] = 0;
            }

            if ($_hours <= 1) {
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['hour'] += 1;
            } else {
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['hour'] = 0;
            }

            if (
              $_notBefore && $_notBefore > $thisCall ||
              $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['sec'] > $this->_limit_per_second ||
              $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['min'] > $this->_limit_per_minute ||
              $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['hour'] > $this->_limit_per_hour
            ) {
                $_banCount   = (int) $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['ban-count'] + 1;
                $_retryAfter = $this->_seconds_banned * $_banCount;

                // Increase the punishment time, depending on how many times the rate was exceeded.
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['ban-count']  = $_banCount;
                $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['not-before'] = time() + $_retryAfter;

                header("HTTP/1.1 429 Too Many Requests", Http::_TOO_MANY_REQUESTS);
                header("Retry-After: " . $_retryAfter);

                exit;
            }

            $_hoursCap  = $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['hour'] / $this->_limit_per_hour;
            $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['not-before'] = null;

            echo "CAP: ", $_hoursCap, "<br >";

            // Throttle the response if client has been banned for several times already or
            // if client reaches 80% (or more) of the hourly rate limit.
            if (
              $_hoursCap > 0.8 ||
              $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['ban-count'] > RateLimit::SESSION_THROTTLING_BOUNDARY
            ) {
                sleep($this->_seconds_delay);
            }
        }

        $_SESSION[RateLimit::SESSION_NAME][ $clientIP ] = [
            'last-call'  => $callTime,
            'sec'        => $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['sec'] ?? 0,
            'min'        => $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['min'] ?? 0,
            'hour'       => $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['hour'] ??  0,
            'not-before' => $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['not-before'] ?? null,
            'ban-count'  => $_SESSION[RateLimit::SESSION_NAME][ $clientIP ]['ban-count'] ?? 0,
        ];
    } // test
} // RateLimit
