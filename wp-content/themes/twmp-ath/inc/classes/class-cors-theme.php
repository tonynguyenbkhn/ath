<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Cors_Theme
{
    use Singleton;

    protected function __construct()
    {
        $this->setup_hooks();
    }

    protected function setup_hooks()
    {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', [$this, 'allow_rest_cors'], 10, 4);
    }

    public function allow_rest_cors($served, $result, $request, $server)
    {
        $origin = get_http_origin();

        if ($origin && $this->is_allowed_origin($origin)) {
            header('Access-Control-Allow-Origin: ' . esc_url_raw($origin));
            header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, nonce, X-WP-Nonce');
            header('Vary: Origin', false);
        } elseif (!headers_sent() && 'GET' === ($_SERVER['REQUEST_METHOD'] ?? '') && !is_user_logged_in()) {
            header('Vary: Origin', false);
        }

        return $served;
    }

    protected function is_allowed_origin($origin)
    {
        $origin_host = $this->normalize_origin($origin);

        if ($origin_host === '') {
            return false;
        }

        foreach ($this->get_allowed_origins() as $allowed_origin) {
            if ($origin_host === $this->normalize_origin($allowed_origin)) {
                return true;
            }
        }

        return false;
    }

    protected function get_allowed_origins()
    {
        $origins = [
            home_url(),
            site_url(),
            admin_url(),
        ];

        if (defined('TWMP_ALLOWED_REST_ORIGINS')) {
            $custom_origins = is_array(TWMP_ALLOWED_REST_ORIGINS)
                ? TWMP_ALLOWED_REST_ORIGINS
                : explode(',', (string) TWMP_ALLOWED_REST_ORIGINS);

            $origins = array_merge($origins, $custom_origins);
        }

        return array_filter(array_map('trim', $origins));
    }

    protected function normalize_origin($origin)
    {
        $parts = wp_parse_url((string) $origin);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $port = !empty($parts['port']) ? ':' . (int) $parts['port'] : '';

        return strtolower($parts['scheme'] . '://' . $parts['host'] . $port);
    }
}
