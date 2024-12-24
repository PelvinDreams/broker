<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';
class Auth {
    private $sqlConn;
    private $users;
    private $active_plans;
    private $packages;

    public function __construct($conn, $users_table) {
        $this->sqlConn = $conn;
        $this->users = $users_table;
        $this->active_plans = 'active_plans';
        $this->packages = 'packages';
    }
    public function validateEmail($email) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $existingData = $result->fetch_assoc();
            if ($existingData["email"] === $email) {
                return false;
            }
        } else {
            return true;
        }
    }

    public function resetPassword($email) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $recipientName = $row["firstname"] . " " . $row["lastname"];
            $token = $this->randomToken();
            $updateStmt = $this->sqlConn->prepare("UPDATE users SET token = ? WHERE email = ?");
            $updateStmt->bind_param("ss", $token, $email);
            if ($updateStmt->execute()) {
                $send = $this->resetPasswordMail($recipientName, $email, $token);
                if ($send == true) {
                    return [
                        'statuscode' => 0,
                        'message' => 'A reset password token was just sent to your email.'
                    ];
                } else {
                    return [
                        'statuscode' => -1,
                        'message' => 'Something went wrong. Please try again later.'
                    ];
                }
            }
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Invalid email',
            ];
        }
    }

    public function newpassword($email, $password, $password2, $ntoken) {
        if ($password != $password2) {
            return [
                'statuscode' => -1,
                'message' => 'Password did not match.'
            ];
            return;
        }
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $token = $row["token"];
            if ($ntoken  == $token) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $this->sqlConn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $hashedPassword, $email);
                if ($updateStmt->execute()) {
                    return [
                        'statuscode' => 0,
                        'message' => 'Password reset was successful.'
                    ];
                }
            } else {
                return [
                    'statuscode' => -1,
                    'message' => 'Invalid token',
                ];
            }
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Invalid email',
            ];
        }
    }

    public function randomToken($length = 5) {
        $characters = '1234567890';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }

    public function resetPasswordMail($recipientName, $email, $token) {
        $mail = new PHPMailer(true);

        try {
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host       = 'mail.jazyen.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply@astrofx.pro';
            $mail->Password   = 'AstroFX20';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465; 

            //Recipients
            $mail->setFrom('noreply@astrofx.pro', 'AstroFX');
            $mail->addAddress($email, $recipientName);
            $mail->addReplyTo('support@astrofx.pro', 'AstroFX');
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';

            $htmlContent = file_get_contents('src/password_reset.html');
            if ($htmlContent === false) {
                throw new Exception('Could not read HTML file');
            }

            $htmlContent = str_replace('{{name}}', $recipientName, $htmlContent);
            $htmlContent = str_replace('{{token}}', $token, $htmlContent);
            $htmlContent = str_replace('{{email}}', $email, $htmlContent);

            $mail->Body    = $htmlContent;
            $mail->AltBody = 'null';

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function validateSessionid($id) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE sessionid = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $existingData = $result->fetch_assoc();
            if ($existingData["sessionid"] === $id) {
                return true;
            }
        } else {
            return false;
        }
    }
    
    public function validateUsername($username) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $existingData = $result->fetch_assoc();
            if ($existingData["username"] === $username) {
                return false;
            }
        } else {
            return true;
        }
    }

    public function login($username, $password) {
        if ($username == 'admin') {
            $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
                $userid = $row['id'];
                if (password_verify($password, $hashed_password)) {
                    $newSessionID = bin2hex(random_bytes(35));
                    $updateStmt = $this->sqlConn->prepare("UPDATE users SET sessionid = ? WHERE username = ?");
                    $updateStmt->bind_param("ss", $newSessionID, $username);
                    if ($updateStmt->execute()) {
                        session_start();
                        $_SESSION['sessionid'] = $newSessionID;
                        $_SESSION['admin'] = 'admin';
                        return [
                            'statuscode' => 5,
                            'message' => 'Login was successful',
                            'user' => $row
                        ];
                    }
                } else {
                    return [
                        'statuscode' => -1,
                        'message' => 'Invalid email or password',
                    ];
                }
            } else {
                return [
                    'statuscode' => -1,
                    'message' => 'Invalid email or password',
                ];
            }
        } else {
            $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
                $userid = $row['id'];
                if (password_verify($password, $hashed_password)) {
                        $newSessionID = bin2hex(random_bytes(35));
                        if ($row['status'] == 'Active') {
                            $updateStmt = $this->sqlConn->prepare("UPDATE users SET sessionid = ? WHERE username = ?");
                            $updateStmt->bind_param("ss", $newSessionID, $username);
                            if ($updateStmt->execute()) {
                                session_start();
                                $_SESSION['sessionid'] = $newSessionID;
                                return [
                                    'statuscode' => 0,
                                    'message' => 'Login was successful',
                                    'user' => $row
                                ];
                            }
                        } else {
                            return [
                                'statuscode' => -1,
                                'message' => 'Your account has been suspended. Please contact support',
                            ];
                        }
                } else {
                    return [
                        'statuscode' => -1,
                        'message' => 'Invalid email or password',
                    ];
                }
            } else {
                return [
                    'statuscode' => -1,
                    'message' => 'Invalid email or password',
                ];
            }
        }
    }

    public function signup($firstname, $lastname, $email, $password, $phone, $username, $country) {
        $customerid = $this->customerid();
        $sessionid = bin2hex(random_bytes(35));
        $checkmail = $this->validateEmail($email);
        if (!$checkmail) {
            return [
                'statuscode' => -1,
                'message' => 'Email already exits',
            ];
            die;
        }
        $checkusername = $this->validateUsername($username);
        if (!$checkusername) {
            return [
                'statuscode' => -1,
                'message' => 'Username already exits',
            ];
            die;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the SQL statement
        $stmt = $this->sqlConn->prepare("INSERT INTO $this->users (firstname, lastname, email, password, phone, username, customerid, sessionid, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $firstname, $lastname, $email, $hashedPassword, $phone, $username, $customerid, $sessionid, $country);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $stmt5 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
            $stmt5->bind_param("i", $userId);
            $stmt5->execute();
            $result5 = $stmt5->get_result();

            if ($result5->num_rows > 0) {
                $row5 = $result5->fetch_assoc();
                $password5 = $row5['password'];
                return [
                    'statuscode' => 0,
                    'userid' => $userId,
                    'sessionid' => $sessionid,
                    'pass' => $password5,
                    'message' => 'Signup successful',
                ];
            }
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Failed to register this user',
            ];
        }
    }

    public function customerid($length = 7) {
        $characters = '1234567890';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }

    public function sessionid($length = 30) {
        $characters = '1234567890abcdefghijklmnopqrstuvwxyz';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }
}
