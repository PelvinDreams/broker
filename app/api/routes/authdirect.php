<?php
require_once 'src/config.php';
require_once 'classes/Auth.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$auth = new Auth($conn, "users");