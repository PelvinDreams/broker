<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
error_reporting(E_ALL);
ini_set('display_errors', 1);

class projectAPI{
    private $sqlConn;
    private $admin;
    private $store;
    private $t_Table;
    private $request;
    private $deposit;
    private $d_board;
    private $users;

    public function __construct($adminTable, $userTable, $storeTable, $t_Table, $withdrawer, $pending, $dashboard, $conn){
        $this->sqlConn = $conn;
        $this->admin = $adminTable;  
        $this->store = $storeTable; 
        $this->users = $userTable;   
        $this->t_Table = $t_Table;   
        $this->request = $withdrawer;
        $this->deposit = $pending;
        $this->d_board = $dashboard;
                    
    }

    // User authentication: Signup
    public function signup($firstname, $lastname, $email, $password, $phone, $country) {

        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $customerid = $this->customerid();
        $account = $this->account();
        $stmt = $this->sqlConn->prepare("INSERT INTO $this->users (firstname, lastname, email, password, phone, country, customerid, account_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstname, $lastname, $email, $password, $phone, $country, $customerid, $account);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            return [
                'userId' => $userId,
                'message' => 'Signup successful',
            ];
        } else {
            return [
                'message' => 'Failed to register this user',
            ];
        }
    }

    public function fetchusers() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users");
        $stmt->execute();
        $result = $stmt->get_result();
        $allUsers = $result->fetch_all(MYSQLI_ASSOC);
        return $allUsers;
        exit;
    }

    public function pendingDeposit() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->deposit");
        $stmt->execute();
        $result = $stmt->get_result();
        $allUsers = $result->fetch_all(MYSQLI_ASSOC);
        return $allUsers;
    }

    public function deleteUsers($id) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $stmt3 = $this->sqlConn->prepare("DELETE FROM $this->users WHERE id = ?");
            $stmt3->bind_param("i", $id);
            if ($stmt3->execute()) {
                return true;
                // $num = 4;
                // $stmt4 = $this->sqlConn->prepare("SELECT * FROM $this->d_board WHERE id = ?");
                // $stmt4->bind_param("i", $num);
                // $stmt4->execute();
                // $result4 = $stmt4->get_result();
                // if ($result4->num_rows > 0) {
                //     $row = $result4->fetch_assoc();
                //     $users = $row['total_users'];
                //     $newUsers = $users - 1;
                //     $update = "UPDATE $this->d_board SET `total_users` = ? WHERE `id` = ?";
                //     $updateStmt = $this->sqlConn->prepare($update);
                //     $updateStmt->bind_param("i", $num);
                //     if ($queryStmt->execute()) {
                //         return true;
                //     }
                // }
            }
        }
    }

    public function fetchrequest() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->request");
        $stmt->execute();
        $result = $stmt->get_result();
        $allUsers = $result->fetch_all(MYSQLI_ASSOC);
        return $allUsers;
    }

    public function approve($admin_id, $password, $t_id) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->admin WHERE id = ? AND password = ?");
        $stmt->bind_param("ss", $admin_id, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->t_Table WHERE id = ?");

            $stmt2->bind_param("i", $t_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($result2) {
                $new_status = 1;
                $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            } else {
                return [
                    'message' => 'No transaction yet',
                ];
            }
        } else {
            return "You're not authorized";
        }

        
    }

    public function getTransactions() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->t_Table");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $row = $result->fetch_all(MYSQLI_ASSOC);
            return $row;
        } else {
            return [
                'message' => 'No transaction yet',
            ];
        }
    }

    public function add_machine($name, $price, $income, $image) {
        $stmt = $this->sqlConn->prepare("INSERT INTO $this->store (machine_name, price, income, img) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $price, $income, $image);
        if ($stmt->execute()) {
            return true;
        } else {
            return [
                'message' => 'Failed to add',
            ];
        }
    }
    
    public function updateDashboard($amount) {
        $dashboardID = 1;
        try {
            $stmt4 = $this->sqlConn->prepare("SELECT * FROM $this->d_board WHERE id = ?");
            $stmt4->bind_param("i", $dashboardID);
            $stmt4->execute();
            $result4 = $stmt4->get_result();
    
            if ($result4->num_rows > 0) {
                $row4 = $result4->fetch_assoc();
                $income = $row4['total_income'];
                $total_balance = $row4['total_balance'];
    
                // Define the percentage as a constant
                define('FLUTTERWAVE_CHARGE_PERCENTAGE', 1.4);
    
                $calculatedValue = $amount * (FLUTTERWAVE_CHARGE_PERCENTAGE / 100);
                $valid_amount = $amount - $calculatedValue;
                $new_balance2 = $total_balance + $valid_amount;
                $new_income = $income + $valid_amount;
    
                $update4 = "UPDATE $this->d_board SET `total_income` = ?, `total_balance` = ? WHERE `id` = ?";
                $query4 = $this->sqlConn->prepare($update4);
                $query4->bind_param("dds", $new_income, $new_balance2, $dashboardID);
    
                if ($query4->execute()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Handle exceptions here (e.g., log or report the error)
            return false;
        }
    }
    
    public function time() {
        $timezone = new DateTimeZone('GMT');
        $currentDateTime = new DateTime('now', $timezone);
        $currentDateTime->modify('+1 hour');

        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

        return $formattedDateTime;
    }
    public function randomCCCToken($length = 6) {
        $characters = '1234567890';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }
    
    public function customerid($length = 6) {
        $characters = '1234567890';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }

    public function account($length = 12) {
        $characters = '1234567890';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }

    public function randomToken($length = 16) {
        $characters = '1234567890';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }

    public function recoredTransaction($amount, $user_id, $description) {
        $role = 'user';
        $status = 1;
        $transaction_id = $this->randomToken();
        $date = $this->time();
        $stmt1 = $this->sqlConn->prepare("INSERT INTO $this->t_Table (user_id, amount, role, description, status, ref_no, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssssss", $user_id, $amount, $role, $description, $status, $transaction_id, $date);
        if ($stmt1->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function getMachineDetails() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->store");
        $stmt->execute();
        $result = $stmt->get_result();
        $machineDetails = $result->fetch_all(MYSQLI_ASSOC);
        return $machineDetails;
    }

    public function handleRequest($method, $endpoint, $data){
        switch ($endpoint) {
            case '/signup':
                if ($method === 'POST') {
                    if (isset($data['firstname']) && isset($data['lastname']) && isset($data['email']) && isset($data['password']) && isset($data['country'])) {
                        $email = $data['email'];
                        $password = $data['password'];
                        $country = $data['country'];
    
                        // Checking if email already exists
                        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
    
                        if ($result->num_rows > 0) {
                            $existingData = $result->fetch_assoc();
                            if ($existingData['email'] === $email) {
                                return [
                                    'message' => 'Email already exists',
                                ];
                            }
                            return;
                        }
                        $firstname = $data['firstname'];
                        $lastname = $data['lastname'];
                        $email = $data['email'];
                        $password = $data['password'];
                        // $phone = isset($data['phone']) ? $data['phone'] : '';
                        $phone = '+9********';
                        $details = $this->signup($firstname, $lastname, $email, $password, $phone, $country);
                        if ($details['userId']) {
                            $user_id = $details['userId'];
                            return [
                                'message' => 'Registration was successful',
                            ];
                        } else {
                            return 'Failed to register';
                        }
                    } else {
                        return 'Missing parameters';
                    }
                } else {
                    return "Invalid request methods.";
                }
                break;
            case '/add_machine':
                    if ($method === 'POST') {
                        
                        if (!isset($_SESSION['phone'])) {
                            header("Location: ../index.html");
                            exit();
                        }
                        if (isset($data['machine_name']) && isset($data['price']) && isset($data['income']) && isset($_FILES['img'])) {
                            $name = $data['machine_name'];
                            $price = $data['price'];
                            $income = $data['income'];
                            $uploadDir = '../assets/images';  // Change this to your actual upload directory
                            $uploadedFile = $_FILES['img']['tmp_name'][0];
                            $filename = $_FILES['img']['name'][0];
                            $targetFile = $uploadDir . $filename;
                            if (move_uploaded_file($uploadedFile, $targetFile)) { 
                                $details = $this->add_machine($name, $price, $income, $filename);

                                if ($details) {
                                    return [
                                        'message' => 'Machine added',
                                    ];
                                } else {
                                    // Request not found or unable to approve
                                    return 'Failed to add';
                                }
                            }
                            
                        } else {
                            return 'Missing parameters';
                        }
                    } else {
                        return "Invalid request methods.";
                    }
                    break;    
            case '/login':
                if ($method === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true); // Get POST data
                    if (isset($data['phone']) && isset($data['password'])) {
                        $phone = $data['phone'];
                        $password = $data['password'];
                        
                        $userId = $this->isLoginValid($phone, $password);
                        if ($userId) {
                            $newSessionID = bin2hex(random_bytes(16));
            
                            $updateStmt = $this->sqlConn->prepare("UPDATE admin SET session_id = ? WHERE user_id = ?");
                            if ($updateStmt) {
                                $updateStmt->bind_param("si", $newSessionID, $userId);
                                $updateStmt->execute();
            
                                // Set session ID and user ID cookies
                                setcookie('sessionID', $newSessionID, time() + 7 * 24 * 60 * 60, '/'); // Expires in 7 days
                                setcookie('userID', $userId, time() + 7 * 24 * 60 * 60, '/'); // Expires in 7 days
            
                                $response = [
                                    'message' => 'Login was successful',
                                ];
                            } else {
                                $response = ['message' => 'Database error'];
                            }
                        } else {
                            $response = ['message' => 'Login Failed'];
                        }
                    } else {
                        $response = ['message' => 'Missing parameters'];
                    }
            
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit; // Add this line to terminate further processing
                }
                break;
            case '/forgotPassword':
                if ($method === 'POST') {
                    if (isset($data['email'])){ 
                        $email = $data['email'];
                        $p_email = $this->forgotPassword($email);
                        if ($p_email) {
                            return 'Forgot password token was sent to your email';
                        } else {
                            return 'Failed to send token';
                        }
                    } else {
                        return 'Missing parameters'; 
                    }
                }
                break;
            case "/getMachineDetails":
                if ($method === 'GET') {
                    
                    if (!isset($_SESSION['phone'])) {
                        header("Location: ../index.html");
                        exit();
                    }
                    $machineDetails = $this->getMachineDetails();
                    return $machineDetails;
                } else {
                    return "Invalid request method.";
                }
                break;
            case "/fetchusers":
                if ($method === 'GET') {
                    $allUsers = $this->fetchusers();
                    return $allUsers;
                    exit;
                } else {
                    return "Invalid request method.";
                }
                break;
            case "/pending":
                if ($method === 'GET') {
                    
                    if (!isset($_SESSION['phone'])) {
                        header("Location: ../index.html");
                        exit();
                    }
                    $allUsers = $this->pendingDeposit();
                    return $allUsers;
                } else {
                    return "Invalid request method.";
                }
                break;
            case "/approve":
                if ($method === 'GET') {
                    
                    if (isset($_SESSION['phone'])) {
                        if (isset($_GET['ref_no'])) {
                            $ref_no = $_GET['ref_no'];
                            $selectStmt = $this->sqlConn->prepare("SELECT * FROM $this->request WHERE ref_no = ?");
                            $selectStmt->bind_param("s", $ref_no);
                            
                            if ($selectStmt->execute()) {
                                $result = $selectStmt->get_result();
                                if ($result->num_rows > 0) {
                                    $row0 = $result->fetch_assoc();
                                    $amount = $row0['amount'];
                                    $deleteStmt = $this->sqlConn->prepare("DELETE FROM $this->request WHERE ref_no = ?");
                                    $deleteStmt->bind_param("s", $ref_no);
                        
                                    if ($deleteStmt->execute()) {
                                        $status = 1;
                                        $update = "UPDATE $this->t_Table SET `status` = ? WHERE `ref_no` = ?";
                                        $updateStmt = $this->sqlConn->prepare($update);
                                        $updateStmt->bind_param("is", $status, $ref_no);
                        
                                        if ($updateStmt->execute()) {
                                            $d_id = 1;
                                            $stmt4 = $this->sqlConn->prepare("SELECT * FROM $this->d_board WHERE id = ?");
                                            $stmt4->bind_param("i", $d_id);
                                            $stmt4->execute();
                                            $result4 = $stmt4->get_result();
                                    
                                            if ($result4->num_rows > 0) {
                                                $row4 = $result4->fetch_assoc();
                                                $paidout = $row4['total_paidout'];
                                                $new_paid = $paidout + $amount;
                                                $update7 = "UPDATE $this->d_board SET `total_paidout` = ? WHERE `id` = ?";
                                                $updateStmt2 = $this->sqlConn->prepare($update7);
                                                $updateStmt2->bind_param("di", $new_paid, $d_id);
                                                if ($updateStmt2->execute()) {
                                                    header("Location: ../../request.php?success=true");
                                                    exit();
                                                }
                                            }
                                        } else {
                                            return [
                                                'message' => 'Failed to update status',
                                            ];
                                        }
                                    } else {
                                        return [
                                            'message' => 'Failed to delete',
                                        ];
                                    }
                                } else {
                                    return [
                                        'message' => 'Invalid reference number',
                                    ];
                                }
                            } else {
                                return [
                                    'message' => 'Error executing SELECT query',
                                ];
                            }
                        } else {
                            return [
                                'message' => 'No reference number',
                            ];
                        }                                             
                    }
                    else {
                        header("Location: ../index.html");
                        exit();
                    }
                } 
                else {
                    return [
                    'message' => 'Invalid request method',
                    ];
                }
                break;
            case "/rejectwithdrawer":
                if ($method === 'GET') {
                    
                    if (isset($_SESSION['phone'])) {
                        if (isset($_GET['ref_no'])) {
                            $ref_no = $_GET['ref_no'];
                            $selectStmt = $this->sqlConn->prepare("SELECT * FROM $this->request WHERE ref_no = ?");
                            $selectStmt->bind_param("s", $ref_no);
                            
                            if ($selectStmt->execute()) {
                                $result = $selectStmt->get_result();
                                if ($result->num_rows > 0) {
                                    $row0 = $result->fetch_assoc();
                                    $amount = $row0['amount'];
                                    $user_id = $row0['email'];
                                    $deleteStmt = $this->sqlConn->prepare("DELETE FROM $this->request WHERE ref_no = ?");
                                    $deleteStmt->bind_param("s", $ref_no);
                        
                                    if ($deleteStmt->execute()) {
                                        $status = 2;
                                        $update = "UPDATE $this->t_Table SET `status` = ? WHERE `ref_no` = ?";
                                        $updateStmt = $this->sqlConn->prepare($update);
                                        $updateStmt->bind_param("is", $status, $ref_no);
                        
                                        if ($updateStmt->execute()) {
                                            $stmt4 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
                                            $stmt4->bind_param("s", $user_id);
                                            $stmt4->execute();
                                            $result4 = $stmt4->get_result();
                                    
                                            if ($result4->num_rows > 0) {
                                                $user = $user_id;
                                                $row4 = $result4->fetch_assoc();
                                                $userBalance = $row4['balance'];
                                                $userTotalWithdrawer = $row4['total_withdrawer'];
                                                $newBalance = $userBalance + $amount;
                                                $newTotalWithdrawer = $userTotalWithdrawer - $amount;
                                                $update7 = "UPDATE $this->users SET `balance` = ?, `total_withdrawer` = ? WHERE `email` = ?";
                                                $updateStmt2 = $this->sqlConn->prepare($update7);
                                                $updateStmt2->bind_param("dds", $newBalance, $newTotalWithdrawer, $user);
                                                if ($updateStmt2->execute()) {
                                                    header("Location: ../../request.php?success=false");
                                                    exit();
                                                }
                                            }
                                        } else {
                                            return [
                                                'message' => 'Failed to update status',
                                            ];
                                        }
                                    } else {
                                        return [
                                            'message' => 'Failed to delete',
                                        ];
                                    }
                                } else {
                                    return [
                                        'message' => 'Invalid reference number',
                                    ];
                                }
                            } else {
                                return [
                                    'message' => 'Error executing SELECT query',
                                ];
                            }
                        } else {
                            return [
                                'message' => 'No reference number',
                            ];
                        }                                             
                    }
                    else {
                        header("Location: ../index.html");
                        exit();
                    }
                } 
                else {
                    return [
                    'message' => 'Invalid request method',
                    ];
                }
                break;
            case "/fetchrequest":
                if ($method === 'GET') {
                    
                    if (!isset($_SESSION['phone'])) {
                        header("Location: ../index.html");
                        exit();
                    }
                    $allUsers = $this->fetchrequest();
                    return $allUsers;
                } else {
                    return "Invalid request method.";
                }
                break;
            case '/resetPassword':
                if ($method === 'POST') {
                    if ($data['resetToken'] == null) {
                        if (isset($data['email']) && ($data['resetToken']) && ($data['newPassword'])){ 
                        $email = $data['email'];
                        $resetToken = $data['resetToken'];
                        $newPassword = $data['newPassword'];
                        $p_email = $this->resetPassword($email, $resetToken, $newPassword);
                        if ($p_email) {
                            return 'You\'ve successfully reset your password ';
                        } else {
                            return 'Password reset failed. Please confirm your Token';
                        }
                        } else {
                            return 'Missing parameters'; 
                        }
                    } else {
                        return 'Failed. Please go to forgot password to generate reset token.';
                    }
                    
                }
                break;   
            case "/transactions":
                if ($method === 'GET') {
                    // 
                    // if (!isset($_SESSION['id'])) {
                    //     header("Location: https://app.isbrkonline.com");
                    //     exit();
                    // }
                    $response = $this->getTransactions();
                    return $response;
                } else {
                    return [
                        'message' => 'Invalid request method.',
                    ];
                }
                break;
            case "/deleteuser":
                if ($method === 'GET') {
                    if (isset($_GET['id'])) {
                        $id = $_GET['id'];
                        $delete = $this->deleteUsers($id);
                        if ($delete) {
                            header("Location: ../../users.php?success=true");
                            exit();
                        } else {
                            return [
                                'message' => 'Failed to delete this user.',
                            ];
                        }
                    }else {
                        return [
                            'message' => 'Missing ID',
                        ];
                    }
                } else {
                    return [
                        'message' => 'Invalid request',
                    ];
                }
                break;
            case '/funduser':
                if ($method === 'POST') {
                    if (isset($_POST['email']) && isset($_POST['amount'])) {
                        $amount =$_POST['amount'];
                        $email =$_POST['email'];
                        $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
                        $stmt2->bind_param("s", $email);
                        $stmt2->execute();
                        $result = $stmt2->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $balance = $row['balance'];
                            $user_id = $row['id'];
                            $old_count = $row['transaction'];
                            $old_depo = $row['total_deposit'];
                            $new_depo = $old_depo + $amount;
                            $new_balance = $amount + $balance;
                            $count = $old_count + 1;
                            $update = "UPDATE $this->users SET `balance` = ?, `transaction` = ?, `total_deposit` = ?  WHERE `email` = ?";
                            $query = $this->sqlConn->prepare($update);
                            $query->bind_param("diis", $new_balance, $count, $new_depo, $email);
                            if ($query->execute()) {
                                $description = 'Deposit of ';
                                $record = $this->recoredTransaction($amount, $user_id, $description);
                                if ($record) {
                                    return [
                                        'message' => 'Successful',
                                    ];
                                }else {
                                    return [
                                        'message' => 'Failed to record transaction',
                                    ];
                                }  
                            } else {
                                return [
                                    'message' => 'Failed to update user',
                                ];
                            }
                        } else {
                            return [
                                'message' => 'No user with this email address',
                            ];
                        }
                    } else {
                        return [
                            'message' => 'Missing parameters',
                        ];
                    }
                } else {
                    return [
                        'message' => 'Invalid request methods.',
                    ];
                }
                break;
            case '/wallet':
                if ($method === 'POST') {
                    if (isset($_POST['wallet'])) {
                        $wallet = $_POST['wallet'];
                        $email = 'admin@isbrka.com';
                        $update = "UPDATE $this->admin SET `wallet` = ?  WHERE `email` = ?";
                        $query = $this->sqlConn->prepare($update);
                        $query->bind_param("ss", $wallet, $email);
                        if ($query->execute()) {
                            return [
                                'message' => 'Successful',
                            ];
                        } else {
                            return [
                                'message' => 'Failed to update user',
                            ];
                        }
                    } else {
                        return [
                            'message' => 'Missing parameters',
                        ];
                    }
                } else {
                    return [
                        'message' => 'Invalid request methods.',
                    ];
                }
                break;
            case '/debituser':
            if ($method === 'POST') {
                    if (isset($_POST['email']) && isset($_POST['amount'])) {
                        $amount =$_POST['amount'];
                        $email =$_POST['email'];
                        $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
                        $stmt2->bind_param("s", $email);
                        $stmt2->execute();
                        $result = $stmt2->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $balance = $row['balance'];
                            $user_id = $row['id'];
                            $old_count = $row['transaction'];
                            if ($amount <= $balance) {
                                $new_balance = $balance - $amount;
                                $count = $old_count + 1;
                                $update = "UPDATE $this->users SET `balance` = ?, `transaction` = ?  WHERE `email` = ?";
                                $query = $this->sqlConn->prepare($update);
                                $query->bind_param("dis", $new_balance, $count, $email);
                                if ($query->execute()) {
                                    $description = 'Debit of ';
                                    $record = $this->recoredTransaction($amount, $user_id, $description);
                                    if ($record) {
                                        return [
                                            'message' => 'Successful',
                                        ];
                                    }else {
                                        return [
                                            'message' => 'Failed to record transaction',
                                        ];
                                    }
                                        
                                } else {
                                    return [
                                        'message' => 'Failed to update user',
                                    ];
                                }
                            }else {
                                return [
                                    'message' => 'Insufficient',
                                ];
                            }
                            
                        } else {
                            return [
                                'message' => 'No user with this email address',
                            ];
                        }
                    } else {
                        return [
                            'message' => 'Missing parameters',
                        ];
                    }
                // } else {
                //     return [
                //         'message' => 'Unauthorized access',
                //     ];
                // }
            } else {
                return [
                    'message' => 'Invalid request methods.',
                ];
            }
            break;
            
            case '/requestfee':
                if ($method === 'POST') {
                        if (isset($_POST['email']) && isset($_POST['amount'])) {
                            $amount =$_POST['amount'];
                            $email =$_POST['email'];
                            $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
                            $stmt2->bind_param("s", $email);
                            $stmt2->execute();
                            $result = $stmt2->get_result();
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $user_id = $row['id'];
                                $token1 = $this->randomCCCToken();
                                $update = "UPDATE $this->users SET `fees` = ?, `token` = ?  WHERE `email` = ?";
                                $query = $this->sqlConn->prepare($update);
                                $query->bind_param("sis", $amount, $token1, $email);
                                if ($query->execute()) {
                                    return [
                                        'message' => 'Successful',
                                    ];
                                } else {
                                    return [
                                        'message' => 'Failed to update user',
                                    ];
                                }
                            } else {
                                return [
                                    'message' => 'No user with this email address',
                                ];
                            }
                        } else {
                            return [
                                'message' => 'Missing parameters',
                            ];
                        }
                    // } else {
                    //     return [
                    //         'message' => 'Unauthorized access',
                    //     ];
                    // }
                } else {
                    return [
                        'message' => 'Invalid request methods.',
                    ];
                }
                break;

            default:
            return 'Invalid endpoint';
        }
    }
    
}

$adminTable = 'admin';
$storeTable = 'store';
$userTable = 'users';
$t_Table = 'transactions';
$withdrawer = 'withdrawer';
$pending = 'deposit';
$dashboard = 'dashboard';


$api = new projectAPI($adminTable, $userTable, $storeTable, $t_Table, $withdrawer, $pending, $dashboard, $conn);

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = parse_url($_SERVER['PATH_INFO'], PHP_URL_PATH);
$endpoint = rtrim($endpoint, '/');



$data = $_POST; 

// var_dump($data);
$response = $api->handleRequest($method, $endpoint, $data);

// Set the appropriate headers
header('Content-Type: application/json');

// Send the response
echo json_encode($response);

