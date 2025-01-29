<?php
session_start();
include 'db_connect.php'; // Include your DB configuration
require 'vendor/autoload.php'; // PHPMailer for sending emails

$mailHost = 'smtp.gmail.com';
$mailUsername = 'sravssravs922@gmail.com'; // Replace with your email
$mailPassword = 'epxi drbs affe kxoh'; // Replace with your email password
$mailPort = 587;
$mailFrom = 'sravssravs922@gmail.com';
$mailFromName = 'Donation For Good Cause';

$otp = "";
$emailSent = false;
$otpVerified = false;
$resetSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_otp'])) {
        $email = $_POST['email'];

        // Check if email exists in either donors or recipients
        $query = "SELECT email FROM donors WHERE email = ? UNION SELECT email FROM recipients WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generate a random OTP
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['email'] = $email;

            // Send OTP via email
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = $mailHost;
            $mail->SMTPAuth = true;
            $mail->Username = $mailUsername;
            $mail->Password = $mailPassword;
            $mail->Port = $mailPort;

            $mail->setFrom($mailFrom, $mailFromName);
            $mail->addAddress($email);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP code is: $otp";

            if ($mail->send()) {
                $emailSent = true;
            } else {
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            }
        } else {
            echo "Email not found.";
        }
    } elseif (isset($_POST['verify_otp'])) {
        if ($_POST['otp'] == $_SESSION['otp']) {
            $otpVerified = true;
        } else {
            echo "Invalid OTP.";
        }
    } elseif (isset($_POST['reset_password'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Ensure passwords match
        if ($newPassword === $confirmPassword) {
            $email = $_SESSION['email'];
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password in both donors and recipients tables
            $sql_donor = "UPDATE donors SET password=? WHERE email=?";
            $sql_recipient = "UPDATE recipients SET password=? WHERE email=?";

            $stmt_donor = $conn->prepare($sql_donor);
            $stmt_recipient = $conn->prepare($sql_recipient);

            $stmt_donor->bind_param("ss", $hashedPassword, $email);
            $stmt_recipient->bind_param("ss", $hashedPassword, $email);

            if ($stmt_donor->execute() || $stmt_recipient->execute()) {
                $resetSuccess = true;
                unset($_SESSION['otp']);
                unset($_SESSION['email']);
                
                // Redirect to login page after successful password reset
                header("Location: login.html");
                exit(); // Stop script execution after redirection
            } else {
                echo "Error updating password.";
            }
        } else {
            echo "Passwords do not match.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" href="forgetPass.css"> <!-- Keeping original styles -->
</head>
<body>
    <div>
        <nav class="navbar">
            <h1 class="heading">Donation For Good Cause</h1>
            <ul class="navbar-items">
                <li><a href="home.html">Home</a></li>
                <li><a href="About.html">About</a></li>
                <li><a href="contact.html">Contact us</a></li>
            </ul>
        </nav>
    </div>

    <div class="flex body-bg">
        <div>
            <img class="image" src="forgot.png">  
        </div>

        <div class="wrapper">
            <?php if (!isset($_SESSION['otp'])): ?>
                <!-- Step 1: Enter email to send OTP -->
                <form method="post" action="">
                    <h1>Forgot Your Password?</h1>
                    <div class="input-box mobnum">
                        <input id="email" type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="button otp">
                        <button type="submit" name="send_otp" class="btn" id="sendOTP" style="color:white;font-weight: bold;">Send OTP</button>
                    </div>
                </form>
            <?php elseif (!$otpVerified): ?>
                <!-- Step 2: Enter OTP -->
                <form method="post" action="">
                    <div class="input-box">
                        <input type="text" name="otp" placeholder="Enter OTP" required>
                    </div>
                    <div class="button">
                        <button type="submit" name="verify_otp" class="btn" style="color:white;font-weight: bold;">Verify</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- Step 3: Reset Password -->
                <form method="post" action="">
                    <h1>Reset Your Password</h1>
                    <div class="input-box newpass">
                        <input type="password" name="new_password" placeholder="Create new password" required>
                    </div>
                    <div class="input-box">
                        <input type="password" name="confirm_password" placeholder="Confirm password" required>
                    </div>
                    <div class="button">
                        <button type="submit" name="reset_password" class="btn" style="color:white;font-weight: bold;">Reset</button>
                    </div>
                </form>
                <?php if ($resetSuccess): ?>
                    <div class="success">Password has been reset successfully!</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>Copyright@2025 Donation For Cause</p>
        <p>All rights reserved</p>
    </div>
</body>
</html>
