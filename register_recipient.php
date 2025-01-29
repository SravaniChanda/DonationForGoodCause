<?php
session_start(); // Ensure session is started

include 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $userId = $_POST['userId'];
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : null; // Retrieve email from session
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact_number = $_POST['mob_no'];
    $registration_number = $_POST['regno'];

    // Verify email is set
    if (!$email) {
        $error = "Email not found. Please restart the registration process.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check_email_sql = "SELECT * FROM recipients WHERE email = ?";
        $stmt = $conn->prepare($check_email_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "You are already registered with this email.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if (empty($error)) {
                $sql = "INSERT INTO recipients (name, userId, email, password, contact_number, registration_number) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $name, $userId, $email, $hashed_password, $contact_number, $registration_number);

                if ($stmt->execute()) {
                    header("Location: login.html");
                    exit();
                } else {
                    $error = "Error registering new recipient.";
                }
            }
        }
        $stmt->close();
    }
    $conn->close();

    // If there was an error, include the registration page and show the error
    if (!empty($error)) {
        include 'registerAsRecipient.html';
    }
}
?>
