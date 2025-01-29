<?php
include 'db_connect.php'; // Ensure correct DB connection

// Check if connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create donors table
$donors_sql = "CREATE TABLE IF NOT EXISTS donors (
    name VARCHAR(100) NOT NULL,
    userId VARCHAR(8) NOT NULL,
    email VARCHAR(100) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(15),
    register_type VARCHAR(50) DEFAULT 'donor',
    is_verified TINYINT(1) DEFAULT 1,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($donors_sql) === TRUE) {
    echo "Donors table created successfully<br>";
} else {
    echo "Error creating donors table: " . $conn->error . "<br>";
}

// SQL to create recipients table
$recipients_sql = "CREATE TABLE IF NOT EXISTS recipients (
    name VARCHAR(100) NOT NULL,
    userId VARCHAR(8) NOT NULL,
    email VARCHAR(100) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(15),
    registration_number VARCHAR(20),
    register_type VARCHAR(50) DEFAULT 'recipient',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($recipients_sql) === TRUE) {
    echo "Recipients table created successfully<br>";
} else {
    echo "Error creating recipients table: " . $conn->error . "<br>";
}

// SQL to create donations table (general table for all donations)
$donations_sql = "CREATE TABLE IF NOT EXISTS donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(100) NOT NULL,
    donation_type VARCHAR(100) NOT NULL, 
    address TEXT NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    status VARCHAR(50) DEFAULT 'available',
    recipient_name VARCHAR(100) DEFAULT NULL,
    recipient_contact VARCHAR(15) DEFAULT NULL,
    donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($donations_sql) === TRUE) {
    echo "Donations table created successfully<br>";
} else {
    echo "Error creating donations table: " . $conn->error . "<br>";
}

// SQL to create fund_donations table
$fund_donations_sql = "CREATE TABLE IF NOT EXISTS fund_donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donation_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
)";
if ($conn->query($fund_donations_sql) === TRUE) {
    echo "Fund Donations table created successfully<br>";
} else {
    echo "Error creating Fund Donations table: " . $conn->error . "<br>";
}

// SQL to create food_donations table
$food_donations_sql = "CREATE TABLE IF NOT EXISTS food_donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donation_id INT UNSIGNED NOT NULL,
    food_occasion VARCHAR(50) NOT NULL,
    food_type VARCHAR(255) NOT NULL, 
    quantity VARCHAR(50) NOT NULL, 
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
)";
if ($conn->query($food_donations_sql) === TRUE) {
    echo "Food Donations table created successfully<br>";
} else {
    echo "Error creating Food Donations table: " . $conn->error . "<br>";
}

// SQL to create clothes_donations table
$clothes_donations_sql="CREATE TABLE IF NOT EXISTS clothes_donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donation_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    for_old_people TINYINT(1) NOT NULL DEFAULT 0,  
    for_children TINYINT(1) NOT NULL DEFAULT 0,    
    pairs_old INT DEFAULT 0,
    pairs_children INT DEFAULT 0,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
)";

if ($conn->query($clothes_donations_sql) === TRUE) {
    echo "Clothes Donations table created successfully<br>";
} else {
    echo "Error creating Clothes Donations table: " . $conn->error . "<br>";
}

// SQL to create groceries_donations table
$groceries_donations_sql = "CREATE TABLE IF NOT EXISTS groceries_donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donation_id INT UNSIGNED NOT NULL,
    grocery_description VARCHAR(50) NOT NULL, 
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
)";
if ($conn->query($groceries_donations_sql) === TRUE) {
    echo "Groceries Donations table created successfully<br>";
} else {
    echo "Error creating Groceries Donations table: " . $conn->error . "<br>";
}

// SQL to create stationary_donations table
$stationary_donations_sql = "CREATE TABLE IF NOT EXISTS stationary_donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donation_id INT UNSIGNED NOT NULL,
    stationary_description VARCHAR(255) NOT NULL,  
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
)";
if ($conn->query($stationary_donations_sql) === TRUE) {
    echo "Stationary Donations table created successfully<br>";
} else {
    echo "Error creating Stationary Donations table: " . $conn->error . "<br>";
}


$admin_query_sql = "CREATE TABLE IF NOT EXISTS Admin (
    adminId varchar(20),
    password varchar(10)
)";
if ($conn->query($admin_query_sql) === TRUE) {
    echo "admin table created successfully<br>";
    $query="insert into Admin values('donationsystem','admin123')";
    $conn->query($query);
} else {
    echo "Error creating admin table: " . $conn->error . "<br>";
}

// Close the connection
$conn->close();
?>
