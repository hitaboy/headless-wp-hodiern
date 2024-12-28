<?php
/**
 * Allow GET requests from * origin
 * https://joshpress.net/access-control-headers-for-the-wordpress-rest-api/
 */
$cors = get_field('cors', 'option');

if ($cors == true) {
  add_action('rest_api_init', function () {

    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    
    add_filter('rest_pre_serve_request', function ($value) {
      
      $frontend_url = get_field('frontend_url', 'option');
      $allowed_origins = get_field('allowed_origins', 'option');
      $origin = get_http_origin();
      if (in_array($origin, array_column($allowed_origins, 'url'))) {
        header('Access-Control-Allow-Origin: ' . $origin);
      } else {
        header('Access-Control-Allow-Origin: ' . 'null');
      }
      header('Access-Control-Allow-Methods: GET');
      header('Access-Control-Allow-Credentials: true');

      return $value;

    });

  }, 15);
}


