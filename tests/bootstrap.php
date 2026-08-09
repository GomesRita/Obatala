<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/..');
}

if (!class_exists('WP_REST_Controller')) {
    abstract class WP_REST_Controller {}
}

if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {}
}
