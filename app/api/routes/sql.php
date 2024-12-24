<?php
require_once 'src/config.php';
require_once 'classes/Sql.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql = new Sql($conn);

$response1 = $sql->packagesTable();
$response2 = $sql->usersTable();
$response3 = $sql->transactionsTable();
$response4 = $sql->servicesTable();

$response = array(
    'packagesTable' => $response1,
    'usersTable' => $response2,
    'transactionsTable' => $response3,
    'servicesTable' => $response4
);
echo json_encode($response);
die;