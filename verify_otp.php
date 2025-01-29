<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];
    
    if ($entered_otp == $_SESSION['otp']) {
        // OTP is correct
        exit();
    } else {
        // OTP is incorrect
        $error = "Incorrect OTP. Please try again.";
        
    }
}
?>
