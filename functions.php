<?php

if (file_exists(__DIR__.'/config.ini')) {
  $headless_wp_config_options = parse_ini_file('config.ini');
} else {
  throw new Exception('No config.ini file found in Simple Headless Wordpress Plugin theme directory. Please copy config.ini.example to config.ini. See the README for more information.');
}

// CORS handling
require_once 'inc/cors.php';

// Admin area modifications
require_once 'inc/admin.php';
