<?php
require_once 'src/config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');
$response = array();

switch ($method) {
    case 'POST':
        $request_uri = $_SERVER['REQUEST_URI'];
        $endpoint = rtrim($request_uri, '/');
        $endpoint = explode('/', $endpoint);
        $endpoint = end($endpoint);

        // Route the request based on the endpoint
        switch ($endpoint) {
            case 'auth':
                require_once 'routes/auth.php';
                break;
            case 'withdrawal':
                require_once 'routes/services.php';
                $userid = isset($_POST['userid']) ? $_POST['userid'] : null;
                $network = isset($_POST['network']) ? $_POST['network'] : null;
                $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
                $wallet = isset($_POST['wallet']) ? $_POST['wallet'] : null;
                $response = $get->withdrawal($userid, $network, $amount, $wallet);
                break;
            case 'deposit':
                require_once 'routes/services.php';
                $uid = isset($_POST['userid']) ? $_POST['userid'] : null;
                $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
                $response = $get->deposit($amount, $uid);
                break;
            case 'reset_password':
                require_once 'routes/authdirect.php';
                $email = isset($_POST['email']) ? $_POST['email'] : null;
                $response = $auth->resetPassword($email);
                break;
            case 'newpassword':
                require_once 'routes/authdirect.php';
                $email = isset($_POST['email']) ? $_POST['email'] : null;
                $token = isset($_POST['token']) ? $_POST['token'] : null;
                $password = isset($_POST['password']) ? $_POST['password'] : null;
                $password2 = isset($_POST['password2']) ? $_POST['password2'] : null;
                $response = $auth->newpassword($email, $password, $password2, $token);
                break;
            case 'invest':
                require_once 'routes/services.php';
                $userid = isset($_POST['userid']) ? $_POST['userid'] : null;
                $pid = isset($_POST['package_id']) ? $_POST['package_id'] : null;
                $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
                $response = $get->invest($userid, $pid, $amount);
                break;
            case 'fundAccount':
                require_once 'routes/services.php';
                $username = isset($_POST['fusername']) ? $_POST['fusername'] : null;
                $amount = isset($_POST['famount']) ? $_POST['famount'] : null;
                $response = $get->fundAccount($username, $amount);
                break;
            case 'updateWallet':
                require_once 'routes/services.php';
                $wallet = isset($_POST['walletd']) ? $_POST['walletd'] : null;
                $network = isset($_POST['networkd']) ? $_POST['networkd'] : null;
                $response = $get->updateWallet($wallet, $network);
                break;
            case 'updatePackage':
                require_once 'routes/services.php';
                $pname = isset($_POST['pname']) ? $_POST['pname'] : null;
                $pid = isset($_POST['pid']) ? $_POST['pid'] : null;
                $pduration = isset($_POST['pduration']) ? $_POST['pduration'] : null;
                $pminimum = isset($_POST['pminimum']) ? $_POST['pminimum'] : null;
                $pmaximum = isset($_POST['pmaximum']) ? $_POST['pmaximum'] : null;
                $percentage = isset($_POST['ppercentage']) ? $_POST['ppercentage'] : null;
                $response = $get->updatePackage($pid, $pname, $pduration, $pminimum, $pmaximum, $percentage);
                break;
            case 'updateUserDetails':
                require_once 'routes/services.php';
                $ufname = isset($_POST['ufname']) ? $_POST['ufname'] : null;
                $ulname = isset($_POST['ulname']) ? $_POST['ulname'] : null;
                $uphone = isset($_POST['uphone']) ? $_POST['uphone'] : null;
                $ucountry = isset($_POST['ucountry']) ? $_POST['ucountry'] : null;
                $uemail = isset($_POST['uemail']) ? $_POST['uemail'] : null;
                $uid = isset($_POST['u_id']) ? $_POST['u_id'] : null;
                $response = $get->updateUserDetails($uid, $ufname, $ulname, $uphone, $ucountry, $uemail);
                break;
            case 'addPackage':
                require_once 'routes/services.php';
                $pname = isset($_POST['pname']) ? $_POST['pname'] : null;
                $pduration = isset($_POST['pduration']) ? $_POST['pduration'] : null;
                $pminimum = isset($_POST['pminimum']) ? $_POST['pminimum'] : null;
                $pmaximum = isset($_POST['pmaximum']) ? $_POST['pmaximum'] : null;
                $percentage = isset($_POST['ppercentage']) ? $_POST['ppercentage'] : null;
                $response = $get->addPackage($pname, $pduration, $pminimum, $pmaximum, $percentage);
                break;
            case 'suspendUser':
                require_once 'routes/services.php';
                $uid = isset($_POST['userid']) ? $_POST['userid'] : null;
                $response = $get->suspendUser($uid);
                break;
            case 'unSuspendUser':
                require_once 'routes/services.php';
                $uid = isset($_POST['userid']) ? $_POST['userid'] : null;
                $response = $get->unSuspendUser($uid);
                break;
            case 'deleteUser':
                require_once 'routes/services.php';
                $uid = isset($_POST['userid']) ? $_POST['userid'] : null;
                $response = $get->deleteUser($uid);
                break;
            case 'approveTrans':
                require_once 'routes/services.php';
                $tid = isset($_POST['tid']) ? $_POST['tid'] : null;
                $response = $get->approveTrans($tid);
                break;
            case 'declineTrans':
                require_once 'routes/services.php';
                $tid = isset($_POST['tid']) ? $_POST['tid'] : null;
                $response = $get->declineTrans($tid);
                break;
            case 'deletePackage':
                require_once 'routes/services.php';
                $dpid = isset($_POST['dpid']) ? $_POST['dpid'] : null;
                $response = $get->deletePackage($dpid);
                break;
            case 'claimReward':
                require_once 'routes/services.php';
                $userid = isset($_POST['userid']) ? $_POST['userid'] : null;
                $sid = isset($_POST['pid']) ? $_POST['pid'] : null;
                $response = $get->claimReward($userid, $sid);
                break;
            default:
                $response['message'] = "Unauthorized access";
                http_response_code(401);
                break;
        }
        break;
    case 'GET':
        $endpoint = parse_url($_SERVER['PATH_INFO'], PHP_URL_PATH);
        $endpoint = rtrim($endpoint, '/');
        switch ($endpoint) {
            case '/mail':
                require_once 'routes/authdirect.php';
                $response = $auth->resetPasswordMail('Dino', 'essen653@gmail.com', '063354');
                break;
            case '/checkin':
                require_once 'routes/authdirect.php';
                $response = $auth->checkInvestment('2');
                break;
            case '/getpackages':
                require_once 'routes/fetch.php';
                $response = $get->getpackages();
                break;
            case '/withdrawalRequest':
                require_once 'routes/fetch.php';
                $response = $get->withdrawalRequest();
                break;
            case '/depositRequest':
                require_once 'routes/fetch.php';
                $response = $get->depositRequest();
                break;
            case '/allTransactions':
                require_once 'routes/fetch.php';
                $response = $get->allTransactions();
                break;
            case '/getallusers':
                require_once 'routes/fetch.php';
                $response = $get->getallusers();
                break;
            case '/createtable':
                require_once 'routes/sql.php';
                break;
            case '/transactions':
                require_once 'routes/fetch.php';
                $response = $get->getMyTransactions();
                break;
            case '/statistics':
                require_once 'routes/fetch.php';
                $response = $get->statistics();
                break;
            case '/validatepay':
                require_once 'routes/flutter.php';
                break;
            case '/getMyServices':
                require_once 'routes/fetch.php';
                $response = $get->getMyServices();
                break;
            case '/getMyDetails':
                require_once 'routes/fetch.php';
                $response = $get->getMyDetails();
                break;
            default:
                $response['message'] = "Unauthorized access";
                http_response_code(401);
                break;
        }
        break;
    default:
        $response['message'] = "Method not allowed.";
        http_response_code(405);
        break;
}
echo json_encode($response);
