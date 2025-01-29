<?php
session_start();
include 'db_connect.php'; // Ensure correct DB connection
require 'vendor/autoload.php'; // PHPMailer for sending emails

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email already exists in the database
    $check_email_sql = "SELECT * FROM donors WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "This email is already registered.";
    } else {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // Send OTP via email using PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Adjust if necessary
        $mail->SMTPAuth = true;
        $mail->Username = 'sravssravs922@gmail.com'; // Replace with your email
        $mail->Password = 'epxi drbs affe kxoh'; // Use app-specific password
        $mail->Port = 587;
        
        $mail->setFrom('sravssravs922@gmail.com', 'Donation For Good Cause');
        $mail->addAddress($email);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP code is: $otp";

        if ($mail->send()) {
            // Redirect to the registration page and show OTP section
            header("Location: registerAsDonor.html?showOtp=1");
            exit();
        } else {
            $error = 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
    $stmt->close();
    $conn->close();
}

if (!empty($error)) {
    include 'registerAsDonor.html'; // Show error on form
}
?>
