<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

//keeps current user data
final class WOOCS_STORAGE {

    public $type = 'session'; //session, transient, woo_session, cookie
    private $user_ip = null;
    private $transient_key = null;

    public function __construct($type = '') {
        if (!empty($type)) {
            $this->type = $type;
        }
        if ($this->type == 'woo_session') {
            $this->type = 'session';
        }
        if ($this->type == 'session') {
            if (!session_id()) {
                @session_start();
            }
        }


        $this->user_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        $this->transient_key = substr(md5($this->user_ip), 7, 23);
    }

    public function set_val($key, $value) {

        $value = sanitize_text_field(esc_html($value));
        //***

        switch ($this->type) {

            case 'woo_session':

                if (WC()->session) {
                    $cookie = WC()->session->get_session_cookie();
                    if (!$cookie) {
                        WC()->session->set_customer_session_cookie(true);
                    }

                    WC()->session->set($key, $value);
                }

                break;
            case 'session':
                $_SESSION[$key] = $value;
                break;
            case 'memcached':
                if (class_exists('Memcached')) {
                    $mem = new Memcached();
                    $mem->addServer(WOOCS_MEMCACHED_SERVER, WOOCS_MEMCACHED_PORT);
                    $mem->set($this->transient_key . '_' . $key, $value);
                }
                break;
            case 'redis':
                if (class_exists('Redis')) {
                    $red = new Redis();
                    try {
                        @$red->connect(WOOCS_REDIS_SERVER, WOOCS_REDIS_PORT);
                        $red->set($this->transient_key . '_' . $key, $value);
                    } catch (RedisException $e) {
                        //***
                    }
                }
                break;
            case 'transient':
                $data = get_transient($this->transient_key);
                if (!is_array($data)) {
                    $data = array();
                }
                $data[$key] = $value;
                set_transient($this->transient_key, $data, 1 * 24 * 3600); //1 day
                break;
            case 'cookie':
                setcookie($key, $value, time() + 1 * 24 * 3600); //1 day
                // wc_setcookie( $key, $value, time() + 1 * 24 * 3600 );
                break;

            default:
                break;
        }
    }

    public function get_val($key) {
        $value = NULL;

        switch ($this->type) {
            case 'woo_session':
                if (WC()->session) {
                    $value = WC()->session->get($key);
                }

                break;
            case 'session':
                if ($this->is_isset($key)) {
                    $value = $_SESSION[$key];
                }
                break;
            case 'memcached':
                if ($this->is_isset($key) AND class_exists('Memcached')) {
                    $mem = new Memcached();
                    $mem->addServer(WOOCS_MEMCACHED_SERVER, WOOCS_MEMCACHED_PORT);
                    $value = $mem->get($this->transient_key . '_' . $key);
                }
                break;
            case 'redis':
                if ($this->is_isset($key) AND class_exists('Redis')) {
                    $red = new Redis();
                    try {
                        @$red->connect(WOOCS_REDIS_SERVER, WOOCS_REDIS_PORT);
                        $value = $red->get($this->transient_key . '_' . $key);
                    } catch (RedisException $e) {
                        //***
                    }
                }
                break;
            case 'transient':
                $data = get_transient($this->transient_key);
                if (!is_array($data)) {
                    $data = array();
                }
                if (isset($data[$key])) {
                    $value = $data[$key];
                }
                break;
            case 'cookie':
                if ($this->is_isset($key)) {
                    $value = $_COOKIE[$key];
                }
                break;

            default:
                break;
        }

        return sanitize_text_field(esc_html($value));
    }

    public function is_isset($key) {
        $isset = false;
        switch ($this->type) {
            case 'woo_session':
                if (WC()->session) {
                    $isset = (bool) WC()->session->get($key);
                }
                break;
            case 'session':
                $isset = isset($_SESSION[$key]);
                break;
            case 'memcached':
                $isset = false;
                if (class_exists('Memcached')) {
                    $mem = new Memcached();
                    $mem->addServer(WOOCS_MEMCACHED_SERVER, WOOCS_MEMCACHED_PORT);
                    $isset = boolval($mem->get($this->transient_key . '_' . $key) !== false && $mem->getResultCode() !== Memcached::RES_NOTFOUND);
                }
                break;
            case 'redis':
                $isset = false;
                if (class_exists('Redis')) {
                    $red = new Redis();
                    try {
                        @$red->connect(WOOCS_REDIS_SERVER, WOOCS_REDIS_PORT);
                        $isset = boolval($red->exists($this->transient_key . '_' . $key));
                    } catch (RedisException $e) {
                        //***
                    }
                }
                break;
            case 'transient':
                $isset = (bool) $this->get_val($key);
                break;
            case 'cookie':
                $isset = isset($_COOKIE[$key]);
                break;
        }

        return $isset;
    }

}
