<?php
session_start();
include 'db_connect.php'; // Ensure correct DB connection

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact_number = $_POST['mob_no'];
    $email = $_SESSION['email']; // Get the email from the session

    // Validate form data
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the donor data into the database
        $sql = "INSERT INTO donors (name, email, password, contact_number, is_verified) VALUES (?,?,?,?,1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $contact_number);

        if ($stmt->execute()) {
            // Clear session
            unset($_SESSION['otp']);
            unset($_SESSION['email']);

            // Redirect to login page after successful registration
            header("Location: login.html");
            exit();
        } else {
            $error = "Error registering new donor.";
        }
        $stmt->close();
    }
    $conn->close();
}

if (!empty($error)) {
    include 'registerAsDonor.html'; // Show error on form
}
