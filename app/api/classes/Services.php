<?php

class Services {
    private $sqlConn;
    private $users;
    private $packages;
    private $transactions;
    private $active_plans;
    private $config;

    public function __construct($conn) {
        $this->sqlConn = $conn;
        $this->users = 'users';
        $this->packages = 'packages';
        $this->transactions = 'transactions';
        $this->active_plans = 'active_plans';
        $this->config = 'config';
    }

    function getCurrentGitBranch() {
        $output = shell_exec('git rev-parse --abbrev-ref HEAD');
        return trim($output);
    }

    public function withdrawal($id, $network, $amount, $wallet) {
        $checkuser = $this->validateUserid();
        if (!$checkuser) {
            return [
                'statuscode' => -1,
                'message' => 'Unauthorized user',
            ];
            die;
        }
        $userid = intval($id);
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $balance = $row['balance'];
            if ($row['status'] == 'Active') {
                if ($amount <= $balance) {
                    $newAmount = $balance - $amount;
                    $updateStmt = $this->sqlConn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $newAmount, $userid);
                    if ($updateStmt->execute()) {
                        $status = 'Pending';
                        $type = 'Withdrawal';
                        $stmt2 = $this->sqlConn->prepare("INSERT INTO $this->transactions (userid, amount, status, type, network, wallet) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt2->bind_param("isssss", $userid, $amount, $status, $type, $network, $wallet);
                        if ($stmt2->execute()) {
                            return [
                                'statuscode' => 0,
                                'message' => 'Withdrawal Successful',
                            ];
                            die;
                        }
                    }
                } else {
                    return [
                        'statuscode' => -1,
                        'message' => 'Insufficient fund',
                    ];
                    die;
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
                'message' => 'Unauthorized',
            ];
            die;
        }
    }

    public function updateUserDetails($uid, $ufname, $ulname, $uphone, $ucountry, $uemail) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        if ($uid == null || $ufname == null || $ulname == null || $uphone == null || $ucountry == null || $uemail == null) {
            return [
                'statuscode' => -1,
                'message' => 'Please fill up the form',
            ];
            die; 
        }
        $updateStmt = $this->sqlConn->prepare("UPDATE users SET firstname = ?, lastname = ?, phone = ?, country = ?, email = ? WHERE id = ?");
        $updateStmt->bind_param("sssssi", $ufname, $ulname, $uphone, $ucountry, $uemail, $uid);
        if ($updateStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Updated',
            ];
            die;
        }
    }

    public function suspendUser($id) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        $status = 'Suspended';
        $userid = intval($id);
        $updateStmt = $this->sqlConn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $userid);
        if ($updateStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'User suspended',
            ];
            die;
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Error suspending this user',
            ];
        }
    }

    public function unSuspendUser($id) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        $status = 'Active';
        $userid = intval($id);
        $updateStmt = $this->sqlConn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $userid);
        if ($updateStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'User is now Active',
            ];
            die;
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Error suspending this user',
            ];
        }
    }

    public function approveTrans($tid) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        $status = 'Successful';
        $id = intval($tid);
        $updateStmt = $this->sqlConn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $id);
        if ($updateStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Approved',
            ];
            die;
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Error suspending this user',
            ];
        }
    }

    public function declineTrans($tid) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        $status = 'Failed';
        $id = intval($tid);
        $updateStmt = $this->sqlConn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $id);
        if ($updateStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Sucessfully declined',
            ];
            die;
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Error suspending this user',
            ];
        }
    }

    public function updatePackage($pid, $pname, $pduration, $pminimum, $pmaximum, $percentage) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        if ($pid == null || $pname == null || $pduration == null || $pminimum == null || $pmaximum == null || $percentage == null) {
            return [
                'statuscode' => -1,
                'message' => 'Please fill up the form',
            ];
            die; 
        }
        $updateStmt = $this->sqlConn->prepare("UPDATE packages SET name = ?, percentage = ?, minimum = ?, maximum = ?, duration = ? WHERE package_id = ?");
        $updateStmt->bind_param("siiiii", $pname, $percentage, $pminimum, $pmaximum, $pduration, $pid);
        if ($updateStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Updated',
            ];
            die;
        }
    }

    public function addPackage($pname, $pduration, $pminimum, $pmaximum, $percentage) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        if ($pname == null || $pduration == null || $pminimum == null || $pmaximum == null || $percentage == null) {
            return [
                'statuscode' => -1,
                'message' => 'Please fill up the form',
            ];
            die; 
        }
        $stmt = $this->sqlConn->prepare("INSERT INTO $this->packages (name, percentage, minimum, maximum, duration) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $pname, $percentage, $pminimum, $pmaximum, $pduration);
        if ($stmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Updated',
            ];
            die;
        }
    }

    public function updateWallet($wallet, $network) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        $wid = 1;
        $updateStmt = $this->sqlConn->prepare("UPDATE config SET wallet = ?, wallet_network = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $wallet, $network, $wid);
        if ($updateStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Successful',
            ];
            die;
        }
    }

    public function fundAccount($username, $amount) {
        $admin = $this->verifyAdmin();
        if (!$admin) {
            return [
                'statuscode' => -1,
                'message' => 'You dont have the right to take this action.',
            ];
            die;
        }
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $balance = $row['balance'];
            $userid = $row['id'];
            $newAmount = $balance + $amount;
            $updateStmt = $this->sqlConn->prepare("UPDATE users SET balance = ? WHERE username = ?");
            $updateStmt->bind_param("ss", $newAmount, $username);
            if ($updateStmt->execute()) {
                $status = 'Successful';
                $type = 'Deposit';
                $stmt2 = $this->sqlConn->prepare("INSERT INTO $this->transactions (userid, amount, status, type) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("isss", $userid, $amount, $status, $type);
                if ($stmt2->execute()) {
                    return [
                        'statuscode' => 0,
                        'message' => 'Account funded.',
                    ];
                    die;
                }
            }
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Invalid username',
            ];
            die;
        }
    }

    public function deposit($amount, $uid) {
        $checkuser = $this->validateUserid();
        if (!$checkuser) {
            return [
                'statuscode' => -1,
                'message' => 'Unauthorized user',
            ];
            die;
        }
        $userid = intval($uid);
        $status = 'Pending';
        $type = 'Deposit';
        $stmt = $this->sqlConn->prepare("INSERT INTO $this->transactions (userid, amount, status, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $userid, $amount, $status, $type);
        if ($stmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Please wait for approval.',
            ];
            die;
        }
    }

    public function getUserDetails($userid) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $data;
        }
    }

    public function claimReward($userid, $sid) {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->active_plans WHERE id = ?");
        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $amount = $row["due_amount"];
            $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
            $stmt2->bind_param("i", $userid);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($result2->num_rows > 0) {
                $row2 = $result2->fetch_assoc();
                $oldAmount = $row2["balance"];
                $newAmount = $amount + $oldAmount;
                $updateStmt = $this->sqlConn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $updateStmt->bind_param("si", $newAmount, $userid);
                if ($updateStmt->execute()) {
                    $newStatus = 'Ended';
                    $updateStmt2 = $this->sqlConn->prepare("UPDATE active_plans SET status = ? WHERE id = ?");
                    $updateStmt2->bind_param("si", $newStatus, $sid);
                    if ($updateStmt2->execute()) {
                        $status = 'Successful';
                        $type = 'Returns';
                        $stmt3 = $this->sqlConn->prepare("INSERT INTO $this->transactions (userid, amount, status, type) VALUES (?, ?, ?, ?)");
                        $stmt3->bind_param("isss", $userid, $amount, $status, $type);
                        if ($stmt3->execute()) {
                            return [
                                'statuscode' => 0,
                                'message' => 'Successful',
                            ];
                            die;
                        }
                    }
                }
            }
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Invalid plan',
            ];
            die;
        }
    }

    public function verifyAdmin() {
        session_start();
        if (isset($_SESSION['sessionid'])) {
            $id = $_SESSION['sessionid'];
            $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE sessionid = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row["sessionid"] === $id) {
                    if ($row['username'] === 'admin') {
                        return $row['id'];
                    }
                }
            }
        }
        return false;
    }

    public function validateUserid() {
        session_start();
        if (isset($_SESSION['sessionid'])) {
            $id = $_SESSION['sessionid'];
            $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE sessionid = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row["sessionid"] === $id) {
                 return $row['id'];
                }
            }
        }
        return false;
    }

    public function invest($id, $pid, $amoun) {
        $checkuser = $this->validateUserid();
        if (!$checkuser) {
            return [
                'statuscode' => -1,
                'message' => 'Unauthorized user',
            ];
            die;
        }
        $userid = intval($id);
        $package_id = intval($pid);
        $amount = intval($amoun);
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->packages WHERE package_id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $minimum = $row['minimum'];
            $maximum = $row['maximum'];
            $duration = $row['duration'];
            $package_name = $row['name'];
            $percentage = $row['percentage'];
            if ($amount >= $minimum && $amount <= $maximum) {
                //Update user balance
                $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
                $stmt2->bind_param("i", $userid);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                if ($result2->num_rows > 0) {
                    $row2 = $result2->fetch_assoc();
                    $balance = $row2['balance'];
                    if ($amount <= $balance) {
                        $newAmount = $balance - $amount;
                        $updateStmt = $this->sqlConn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                        $updateStmt->bind_param("si", $newAmount, $userid);
                        if ($updateStmt->execute()) {
                            $status = 'Successful';
                            $type = 'Investment';
                            $stmt3 = $this->sqlConn->prepare("INSERT INTO $this->transactions (userid, amount, status, type) VALUES (?, ?, ?, ?)");
                            $stmt3->bind_param("isss", $userid, $amount, $status, $type);
                            if ($stmt3->execute()) {
                                $t_id = $stmt3->insert_id;
                                $status_set = 'Active';
                                $purchase_date = date('Y-m-d H:i:s');
                                $due_date = date('Y-m-d H:i:s', strtotime('+'.$duration . 'day'));
                                $stmt4 = $this->sqlConn->prepare("INSERT INTO $this->active_plans (userid, transaction_id, percentage, invested_amount, status, due_date, purchase_date, name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt4->bind_param("iiisssss", $userid, $t_id, $percentage, $amount, $status_set, $due_date, $purchase_date, $package_name);
                                if ($stmt4->execute()) {
                                    return [
                                        'statuscode' => 0,
                                        'message' => 'Investment Successful',
                                    ];
                                    die;
                                }
                            }
                        }
                    } else {
                        return [
                            'statuscode' => -1,
                            'message' => 'Insufficient fund. Please fund your wallet.',
                        ];
                        die;
                    }
                } else {
                    return [
                        'statuscode' => -1,
                        'message' => 'Unauthorized',
                    ];
                    die;
                }
            } else {
                return [
                    'statuscode' => -1,
                    'message' => 'The amount you entered is below or exceed the actual price range for this plan',
                ];
                die;
            }

        } else {
            return [
                'statuscode' => -1,
                'message' => 'Invalid Plan',
            ];
            die;
        }
    }

    public function deleteUser($uid) {
        $id = intval($uid);
        //Delete Plans
        $deletePlansStmt = $this->sqlConn->prepare("DELETE FROM active_plans WHERE userid = ?");
        $deletePlansStmt->bind_param("i", $id);
        $deletePlansStmt->execute();
        
        //Delete Trans..
        $deleteTransStmt = $this->sqlConn->prepare("DELETE FROM transactions WHERE userid = ?");
        $deleteTransStmt->bind_param("i", $id);
        $deleteTransStmt->execute();
        
        $deleteStmt = $this->sqlConn->prepare("DELETE FROM $this->users WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        if ($deleteStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'User deleted',
            ];
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Failed to delete',
            ];
        }
    }

    public function deletePackage($pid) {
        $id = intval($pid);
        $deleteStmt = $this->sqlConn->prepare("DELETE FROM $this->packages WHERE package_id = ?");
        $deleteStmt->bind_param("i", $id);
        if ($deleteStmt->execute()) {
            return [
                'statuscode' => 0,
                'message' => 'Deleted',
            ];
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Failed to delete',
            ];
        }
    }
}
