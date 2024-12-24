<?php
    include '../api/src/config.php';
    session_start();
    if (!isset($_SESSION['sessionid'])) {
        header("Location: ../auth/sign-in.html"); 
        exit();
    } else {
        $id = $_SESSION['sessionid'];
        $sql1 = "SELECT * FROM `users` WHERE sessionid = ?";
        $stmt = $conn->prepare($sql1);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $existingData = $result->fetch_assoc();
            if ($existingData["sessionid"] === $id && $existingData["username"] === $_SESSION['admin']) {
            
            } else {
                header("Location: ../auth/sign-in.html");
                exit();
            }
        }
    }
?>