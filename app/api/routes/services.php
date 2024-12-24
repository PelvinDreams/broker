<?php
require_once 'src/config.php';
require_once 'classes/Services.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$get = new Services($conn);