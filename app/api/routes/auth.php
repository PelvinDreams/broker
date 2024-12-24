<?php
require_once 'src/config.php';
require_once 'classes/Auth.php';

$auth = new Auth($conn, "users");
$action = $_POST['action'];
if ($action === 'register') {
    $expectedParameters = ['firstname', 'lastname', 'email', 'password', 'phone', 'username', 'country'];
    $missingParameters = [];
    foreach ($expectedParameters as $param) {
        if (!isset($_POST[$param])) {
            $missingParameters[] = $param;
        }
    }

    if (!empty($missingParameters)) {
        echo json_encode(['message' => 'Missing parameters: ' . implode(', ', $missingParameters)]);
        exit;
    } else {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        $username = $_POST['username'];
        $country = $_POST['country'];
        $response = $auth->signup($firstname, $lastname, $email, $password, $phone, $username, $country);
        echo json_encode($response);
        exit;
    }
} else if ($action === 'login') {
    $expectedParameters = ['username', 'password'];
    $missingParameters = [];
    foreach ($expectedParameters as $param) {
        if (!isset($_POST[$param])) {
            $missingParameters[] = $param;
        }
    }

    if (!empty($missingParameters)) {
        echo json_encode(['message' => 'Missing parameters: ' . implode(', ', $missingParameters)]);
        exit;
    } else {
        $email = $_POST['username'];
        $password = $_POST['password'];

        $response = $auth->login($email, $password);
        echo json_encode($response);
        exit;
    }
} else {
    echo json_encode(['message' => 'Invalid action']);
    die;
}
die;