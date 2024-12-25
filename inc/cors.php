<?php
/**
 * Allow GET requests from * origin
 * https://joshpress.net/access-control-headers-for-the-wordpress-rest-api/
 */
if ($headless_wp_config_options['enforce_cors'] == true) {
  add_action('rest_api_init', function () {

    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');

    add_filter('rest_pre_serve_request', function ($value) {

      global $headless_wp_config_options;

      header('Access-Control-Allow-Origin: '.$headless_wp_config_options['front_end']);
      header('Access-Control-Allow-Methods: GET');
      header('Access-Control-Allow-Credentials: true');

      return $value;

    });

  }, 15);
}
