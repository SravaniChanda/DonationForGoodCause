<?php
session_start();
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emailOrId = $_POST['emailOrId'];
    $password = $_POST['password'];
    if($emailOrId=='donationsystem' && $password=='admin123') {
        header("Location: http://localhost/phpmyadmin/index.php?route=/database/structure&db=donationsystem");
        exit();
    }else{
    // First, check if the user is a donor
    $sql = "SELECT * FROM donors WHERE email = ? OR userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $emailOrId, $emailOrId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_type = 'donor';
    } else {
        // Check if the user is a recipient
        $sql = "SELECT * FROM recipients WHERE email = ? OR userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $emailOrId, $emailOrId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_type = 'recipient';
        } else {
            echo "No user found with that email.";
            $conn->close();
            exit;
        }
    }
    // Verify the password
    if (password_verify($password, $user['password'])) {
        // Store user data in the session
        $_SESSION['user_type'] = $user_type;
        $_SESSION['email'] = $user['email']; // Correctly use $user['email']
        $_SESSION['name'] = $user['name'];
        $_SESSION['contact_number'] = $user['contact_number']; 

        // Redirect based on user type
        if ($user_type === 'donor') {
            header("Location: donate.html");
        } else {
            header("Location: AvailableDonations.php");
        }
        exit();
    } else {
        echo "Invalid password.";
    }
}
    $stmt->close();
    $conn->close();
}
?>
