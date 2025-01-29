<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in as a donor or recipient
if (!isset($_SESSION['email']) || ($_SESSION['user_type'] !== 'donor' && $_SESSION['user_type'] !== 'recipient')) {
    die("Please log in to view this page.");
}

// Get logged-in user's email from session
$user_email = $_SESSION['email'];
$user_type = $_SESSION['user_type'];

// Fetch user details from the appropriate table
if ($user_type === 'recipient') {
    $user_sql = "SELECT name, contact_number FROM recipients WHERE email = ?";
} else {
    $user_sql = "SELECT name, contact_number FROM donors WHERE email = ?";
}
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $user_name = $user['name'];
    $user_contact = $user['contact_number'];
} else {
    die("User details not found.");
}

// Check if claim button is clicked
if ($user_type === 'recipient' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donation_id'])) {
    $donation_id = $_POST['donation_id'];

    // Update the donation status and recipient details in the database
    $update_sql = "UPDATE donations SET status = 'claimed', recipient_name = ?, recipient_contact = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $user_name, $user_contact, $donation_id);
    $stmt->execute();
}

// Fetch all available and claimed donations
$donations_sql = "
    SELECT donations.*, 
            fund_donations.amount,
           food_donations.food_occasion, food_donations.food_type, food_donations.quantity AS food_quantity,
           clothes_donations.quantity AS clothes_quantity, clothes_donations.for_old_people, clothes_donations.for_children, clothes_donations.pairs_old, clothes_donations.pairs_children,
           groceries_donations.grocery_description,
           stationary_donations.stationary_description
    FROM donations
    LEFT JOIN fund_donations ON donations.id = fund_donations.donation_id
    LEFT JOIN food_donations ON donations.id = food_donations.donation_id
    LEFT JOIN clothes_donations ON donations.id = clothes_donations.donation_id
    LEFT JOIN groceries_donations ON donations.id = groceries_donations.donation_id
    LEFT JOIN stationary_donations ON donations.id = stationary_donations.donation_id
    WHERE donations.status = 'available' OR donations.status = 'claimed'
";
$donations_result = $conn->query($donations_sql);

$donations = [];
if ($donations_result->num_rows > 0) {
    while ($row = $donations_result->fetch_assoc()) {
        $donations[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Donations</title>
    <link rel="stylesheet" href="AvailableDonations.css">
</head>
<body class="body-bg">
    <div>
        <nav class="navbar">
            <h1>Donation For Good Cause</h1>
            <ul class="navbar-items">
                <li><a href="home.html">Home</a></li>
                <li><a href="About.html">About</a></li>
                <li><a href="contact.html">Contact us</a></li>
                <li><a href="logout.php">Logout</a><li>
            </ul>
        </nav>
    </div>

    <div class="wrapper wrapper-AD">
    <h1>Available Donations</h1>
    <div id="donationsList">
        <?php if ($donations): ?>
            <?php foreach ($donations as $donation): ?>
                <div class='donation-item'>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($donation['donor_name']); ?></p>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($donation['donation_type']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($donation['address']); ?></p>

                    <!-- Display contact number or 'Contact Admin' based on donation type -->
                    <?php if ($donation['donation_type'] === 'Fund'): ?>
                        <p><strong>Donor Contact Number:</strong> Contact Admin</p>
                    <?php else: ?>
                        <p><strong>Donor Contact Number:</strong> <?php echo htmlspecialchars($donation['contact_number']); ?></p>
                    <?php endif; ?>

                    <p><strong>Donation Date:</strong> <?php echo htmlspecialchars($donation['donation_date']); ?></p>

                    <?php if ($donation['donation_type'] === 'Food'): ?>
                        <p><strong>Food Occasion:</strong> <?php echo htmlspecialchars($donation['food_occasion']); ?></p>
                        <p><strong>Food Type:</strong> <?php echo htmlspecialchars($donation['food_type']); ?></p>
                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($donation['food_quantity']); ?></p>
                    <?php elseif ($donation['donation_type'] === 'Clothes'): ?>
                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($donation['clothes_quantity']); ?></p>
                        <p><strong>For Old People:</strong> <?php echo $donation['for_old_people'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>For Children:</strong> <?php echo $donation['for_children'] ? 'Yes' : 'No'; ?></p>
                        <p><strong>Pairs for Old People:</strong> <?php echo htmlspecialchars($donation['pairs_old']); ?></p>
                        <p><strong>Pairs for Children:</strong> <?php echo htmlspecialchars($donation['pairs_children']); ?></p>
                    <?php elseif ($donation['donation_type'] === 'Groceries'): ?>
                        <p><strong>Grocery Description:</strong> <?php echo htmlspecialchars($donation['grocery_description']); ?></p>
                    <?php elseif ($donation['donation_type'] === 'Stationary'): ?>
                        <p><strong>Stationary Description:</strong> <?php echo htmlspecialchars($donation['stationary_description']); ?></p>
                    <?php elseif ($donation['donation_type'] === 'Fund'): ?>
                        <p><strong>Amount:</strong> <?php echo htmlspecialchars($donation['amount']); ?></p>
                    <?php endif; ?>

                    <!-- Show claim button only if the donation is available and user is a recipient -->
                    <?php if ($donation['status'] === 'available' && $user_type === 'recipient'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="donation_id" value="<?php echo htmlspecialchars($donation['id']); ?>">
                            <button type="submit">Claim Donation</button>
                        </form>
                    <?php elseif ($donation['status'] === 'claimed'): ?>
                        <p><strong>Status:</strong> Donation claimed</p>
                        <?php if (!empty($donation['recipient_name'])): ?>
                            <p><strong>Recipient Name:</strong> <?php echo htmlspecialchars($donation['recipient_name']); ?></p>

                            <!-- Check donation type and display recipient contact accordingly -->
                            <?php if ($donation['donation_type'] === 'Fund'): ?>
                                <p><strong>Recipient Contact:</strong> Contact Admin</p>
                            <?php else: ?>
                                <p><strong>Recipient Contact:</strong> <?php echo htmlspecialchars($donation['recipient_contact']); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <hr>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No donations available.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>