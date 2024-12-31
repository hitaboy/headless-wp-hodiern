<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// BASE
require_once 'inc/base.php';

// CORS handling
require_once 'inc/cors.php';

// Admin area modifications
require_once 'inc/admin.php';

// Admin area modifications
require_once 'inc/encrypt.php';
