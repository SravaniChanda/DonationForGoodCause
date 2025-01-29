<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];
    
    if ($entered_otp == $_SESSION['otp']) {
        echo "OTP verified";
    } else {
        echo "Incorrect OTP. Please try again.";
    }
}
?>
