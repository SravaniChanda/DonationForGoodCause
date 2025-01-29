<?php
include 'db_connect.php'; 

// Check if connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$name = $_POST['name'];
$donation_type = $_POST['donation_type'];
$address = $_POST['address'];
$contact_number = $_POST['contact_number'];


$insert_donations_sql = "INSERT INTO donations (donor_name, donation_type, address, contact_number) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_donations_sql);
$stmt->bind_param("ssss", $name, $donation_type, $address, $contact_number);

if ($stmt->execute()) {
    $donation_id = $stmt->insert_id; 

    // Handle specific donation types
    if ($donation_type === 'Fund') {
       // $account_number = $_POST['account_number'];
        $amount = $_POST['amount'];
       // $pan_card_number = $_POST['pan_card_number'];

        $insert_fund_donations_sql = "INSERT INTO fund_donations (donation_id, amount) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_fund_donations_sql);
        $stmt->bind_param("ii", $donation_id, $amount);
        $stmt->execute();
    } elseif ($donation_type === 'Food') {
        $food_occasion =$_POST['food_occasion'];
        $food_items = $_POST['food_items'];
        $food_quantity = $_POST['food_quantity'];

        $insert_food_donations_sql = "INSERT INTO food_donations (donation_id, food_occasion,food_type, quantity) VALUES (?, ?, ?,?)";
        $stmt = $conn->prepare($insert_food_donations_sql);
        $stmt->bind_param("issi", $donation_id, $food_occasion,$food_items, $food_quantity);
        $stmt->execute();
    } elseif ($donation_type === 'Clothes') {
        $for_old_people = isset($_POST['for_old_people']) ? 1 : 0;
        $for_children = isset($_POST['for_children']) ? 1 : 0;
        $pairs_old = isset($_POST['pairs_old']) ? $_POST['pairs_old'] : 0;
        $pairs_children = isset($_POST['pairs_children']) ? $_POST['pairs_children'] : 0;

        $insert_clothes_donations_sql = "INSERT INTO clothes_donations (donation_id, for_old_people, for_children, pairs_old, pairs_children) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_clothes_donations_sql);
        $stmt->bind_param("iiiii", $donation_id, $for_old_people, $for_children, $pairs_old, $pairs_children);
        $stmt->execute();

        
    } elseif ($donation_type === 'Groceries') {
        $groceries_description = $_POST['groceries_description'];

        $insert_groceries_donations_sql = "INSERT INTO groceries_donations (donation_id, grocery_description ) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_groceries_donations_sql);
        $stmt->bind_param("is", $donation_id, $groceries_description);
        $stmt->execute();
    } elseif ($donation_type === 'Stationary') {
        $stationary_description = $_POST['stationary_description'];
        $insert_stationary_donations_sql = "INSERT INTO stationary_donations (donation_id, stationary_description) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_stationary_donations_sql);
        $stmt->bind_param("is", $donation_id, $stationary_description);
        $stmt->execute();
    }

    header("Location: AvailableDonations.php");
    exit();

} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
} else {
    echo "Invalid request method";
}


// Close the connection
$conn->close();
?>
