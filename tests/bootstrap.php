<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/..');
}

Brain\Monkey\setUp();

// Registra a limpeza após os testes
register_shutdown_function(function () {
    Brain\Monkey\tearDown();
});

if (!class_exists('WP_REST_Controller')) {
    class WP_REST_Controller {
        protected $namespace = '';
        protected $rest_base = '';
    }
}


if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        private mixed $data;
        private int $status;
        private array $headers;

        public function __construct($data = null, $status = 200, $headers = []) {
            $this->data = $data;
            $this->status = $status;
            $this->headers = $headers;
        }

        public function get_data() {
            return $this->data;
        }

        public function set_data($data) {
            $this->data = $data;
        }

        public function get_status() {
            return $this->status;
        }

        public function set_status($status) {
            $this->status = $status;
        }

        public function get_headers() {
            return $this->headers;
        }
    }
}


if (!class_exists('WP_REST_Request')) {
    #[\AllowDynamicProperties]
    class WP_REST_Request implements \ArrayAccess {
        private array $params = [];
        private string $method;
        private string $route;

        public function __construct($method = '', $route = '') {
            $this->method = $method;
            $this->route = $route;
        }

        public function get_method() {
            return $this->method;
        }

        public function get_route() {
            return $this->route;
        }

        public function get_param($key) {
            return $this->params[$key] ?? null;
        }

        public function set_param($key, $value) {
            $this->params[$key] = $value;
        }

        public function get_params() {
            return $this->params;
        }

        public function offsetExists($offset): bool {
            return isset($this->params[$offset]);
        }

        public function offsetGet($offset): mixed {
            return $this->params[$offset] ?? null;
        }

        public function offsetSet($offset, $value): void {
            $this->params[$offset] = $value;
        }

        public function offsetUnset($offset): void {
            unset($this->params[$offset]);
        }
    }
}


if (!class_exists('WP_Error')) {
    class WP_Error {
        private array $errors = [];
        private array $error_data = [];

        public function __construct($code = '', $message = '', $data = '') {
            if ($code) {
                $this->errors[$code][] = $message;
                if ($data) {
                    $this->error_data[$code] = $data;
                }
            }
        }

        public function get_error_code() {
            $codes = array_keys($this->errors);
            return $codes[0] ?? '';
        }

        public function get_error_message($code = '') {
            if (!$code) {
                $code = $this->get_error_code();
            }
            return $this->errors[$code][0] ?? '';
        }

        public function get_error_data($code = '') {
            if (!$code) {
                $code = $this->get_error_code();
            }
            return $this->error_data[$code] ?? null;
        }
    }
}