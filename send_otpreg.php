<?php
session_start();
include 'db_connect.php';
require 'vendor/autoload.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $check_email_sql = "SELECT * FROM recipients WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "This email is already registered.";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sravssravs922@gmail.com';
        $mail->Password = 'epxi drbs affe kxoh';
        $mail->Port = 587;

        $mail->setFrom('sravssravs922@gmail.com', 'Donation For Good Cause');
        $mail->addAddress($email);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP code is: $otp";

        if ($mail->send()) {
            header("Location: RegisterAsRecipient.html?showOtp=1");
            exit();
        } else {
            $error = 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
    $stmt->close();
    $conn->close();
}

if (!empty($error)) {
    include 'RegisterAsRecipient.html';
}
?>
