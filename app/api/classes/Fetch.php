<?php

class Fetch {
    private $sqlConn;
    private $users;
    private $packages; 
    private $active_plans;  
    private $transactions;
    private $config;

    public function __construct($conn) {
        $this->sqlConn = $conn;
        $this->users = "users";
        $this->packages = "packages";
        $this->active_plans = "active_plans";
        $this->transactions = "transactions";
        $this->config = "config";
    }

    public function verifyUser() {
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

    public function getpackages() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->packages");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_all(MYSQLI_ASSOC)) {
            return [
                'statuscode' => 0,
                'plans' => $row
            ];
            die;
        };
        
    }

    public function getallusers() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['username'] !== 'admin') {
                $users[] = $row;
            }
        };
        return [
            'statuscode' => 0,
            'users' => $users
        ];
        die;
        
    }

    public function allTransactions() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->transactions");
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $userid = $row['userid'];
            $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
            $stmt2->bind_param("i", $userid);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();
            $name = $row2['firstname'] . " " . $row2['lastname'];
            $row['name'] = $name;
            $transactions[] = $row;
        };
        return [
            'statuscode' => 0,
            'transactions' => $transactions
        ];
        die;
    }

    public function depositRequest() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->transactions");
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['type'] === 'Deposit' && $row['status'] === 'Pending') {
                $userid = $row['userid'];
                $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
                $stmt2->bind_param("i", $userid);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $row2 = $result2->fetch_assoc();
                $name = $row2['firstname'] . " " . $row2['lastname'];
                $row['name'] = $name;
                $transactions[] = $row;
            }
        };
        return [
            'statuscode' => 0,
            'transactions' => $transactions
        ];
        die;
    }

    public function withdrawalRequest() {
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->transactions");
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['type'] === 'Withdrawal' && $row['status'] === 'Pending') {
                $userid = $row['userid'];
                $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
                $stmt2->bind_param("i", $userid);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $row2 = $result2->fetch_assoc();
                $name = $row2['firstname'] . " " . $row2['lastname'];
                $row['name'] = $name;
                $transactions[] = $row;
            }
        };
        return [
            'statuscode' => 0,
            'transactions' => $transactions
        ];
        die;
    }

    public function statistics() {
        // $userid = $this->verifyUser();
        $userid = true;
        if ($userid) {
            //Active users
            $stmt = $this->sqlConn->prepare("SELECT COUNT(*) as total FROM $this->users WHERE username != 'admin' AND status = 'Active'");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            //Suspended users
            $stmt2 = $this->sqlConn->prepare("SELECT COUNT(*) as total FROM $this->users WHERE username != 'admin' AND status = 'Suspended'");
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();
            //Wallet Details
            $id = 1;
            $stmt3 = $this->sqlConn->prepare("SELECT * FROM $this->config WHERE id = ?");
            $stmt3->bind_param("i", $id);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $row3 = $result3->fetch_assoc();
            return [
                'statuscode' => 0,
                'active_users' => $row['total'],
                'suspended_users' => $row2['total'],
                'wallet' => $row3['wallet'],
                'wallet_network' => $row3['wallet_network']
            ];
            die;
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Unauthorized user'
            ];
            exit();
        }
    }

    public function checkInvestment($id) {
        $userid = intval($id);
        $stmt = $this->sqlConn->prepare("SELECT * FROM $this->active_plans WHERE percentage = ?");
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row["status"] === 'Active') {
                    $percentage = intval($row["percentage"]);
                    $sid =  intval($row["id"]);
                    $amount =  intval($row["invested_amount"]);
                    $today = new DateTime();
                    $due_date = new DateTime($row["due_date"]);
                    if ($today >= $due_date) {
                        $returns = $amount * $percentage / 100;
                        $profit = $amount + $returns;
                        $updateStmt2 = $this->sqlConn->prepare("UPDATE active_plans SET due_amount = ? WHERE id = ?");
                        $updateStmt2->bind_param("si", $profit, $sid);
                        if ($updateStmt2->execute()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    public function getMyServices() {
        $userid = $this->verifyUser();
        if ($userid) {
            $run = $this->checkInvestment($userid);
            $stmt = $this->sqlConn->prepare("SELECT * FROM $this->active_plans");
            $stmt->execute();
            $result = $stmt->get_result();
            $myServices = [];
            while ($row = $result->fetch_assoc()) {
                if ($row['userid'] === $userid) {
                    if ($row['status'] === 'Active') {
                        $myServices[] = $row;
                    }
                }
            };
            return [
                'statuscode' => 0,
                'services' => $myServices
            ];
            die;
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Unauthorized user'
            ];
            exit();
        }
    }

    public function getMyTransactions() {
        $userid = $this->verifyUser();
        if ($userid) {
            $stmt = $this->sqlConn->prepare("SELECT * FROM $this->transactions");
            $stmt->execute();
            $result = $stmt->get_result();
            $myServices = [];
            while ($row = $result->fetch_assoc()) {
                if ($row['userid'] === $userid) {
                    $myServices[] = $row;
                }
            };
            return [
                'statuscode' => 0,
                'history' => $myServices
            ];
            die;
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Unauthorized user'
            ];
            exit();
        }
    }

    public function getMyDetails() {
        $userid = $this->verifyUser();
        if ($userid) {
            $stmt = $this->sqlConn->prepare("SELECT * FROM $this->users WHERE id = ?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $role = 1;
                $stmt2 = $this->sqlConn->prepare("SELECT * FROM $this->config WHERE id = ?");
                $stmt2->bind_param("i", $role);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                if ($result2->num_rows > 0) {
                    $row2 = $result2->fetch_assoc();
                    $wallet = $row2['wallet'];
                    $wallet_network = $row2['wallet_network'];
                    return [
                        'statuscode' => 0,
                        'details' => $row,
                        'wallet' => $wallet,
                        'wallet_network' => $wallet_network
                    ];
                    die;
                }
            } else {
                return [
                    'statuscode' => -1,
                    'message' => 'Unauthorized user'
                ];
                exit();
            }
        } else {
            return [
                'statuscode' => -1,
                'message' => 'Unauthorized user'
            ];
            exit();
        }
    }
}
