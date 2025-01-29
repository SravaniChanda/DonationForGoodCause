<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: login.html");
    exit();
}

$donor_contact = $_SESSION['contact_number']; // Use contact number to fetch donations

// Create a database connection
$mysqli = new mysqli('localhost', 'root', '', 'donationsystem');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch donations for the logged-in donor based on contact number
$query = "
    SELECT d.id, d.donation_type, d.address, d.contact_number, d.status, 
           d.recipient_name, d.recipient_contact, d.donation_date
    FROM donations d
    JOIN donors don ON don.contact_number = d.contact_number
    WHERE don.contact_number = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $donor_contact);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: rgb(194, 241, 241);
        }
        header {
            text-align: center;
            padding: 15px 20px;
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }
        h3 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .donation {
            background: #f5f5f5; /* Light ash color */
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .donation p {
            margin: 5px 0;
        }
        .highlight {
            font-weight: bold;
        }
        .no-donations {
            text-align: center;
            color: #777;
        }
        .back-button {
            display: block;
            width: 100px;
            margin: 10px auto 20px;
            padding: 10px 20px;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            color: black;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f1f1f1;
            cursor: pointer;
        }
        .back-button:hover {
            background: #e0e0e0;
        }
        footer {
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header style="padding-top:10px;">
       <h2> Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>! </h2>
    </header>
    
    <div class="container" style="margin-top:-20px;">
    <h2 style="text-align: center;">Your Donations</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="donation">
                    <p><span class="highlight">Donation Type:</span> <?php echo htmlspecialchars($row['donation_type']); ?></p>
                    <p><span class="highlight">Status:</span> <?php echo htmlspecialchars($row['status']); ?></p>
                    <?php 
                    // Fetch type-specific details
                    $type_details = null;
                    if ($row['donation_type'] == 'Clothes') {
                        $type_query = "SELECT quantity, pairs_old, pairs_children FROM clothes_donations WHERE donation_id = ?";
                    } elseif ($row['donation_type'] == 'Food') {
                        $type_query = "SELECT food_occasion, food_type, quantity FROM food_donations WHERE donation_id = ?";
                    } elseif ($row['donation_type'] == 'Groceries') {
                        $type_query = "SELECT grocery_description FROM groceries_donations WHERE donation_id = ?";
                    } elseif ($row['donation_type'] == 'Stationary') {
                        $type_query = "SELECT stationary_description FROM stationary_donations WHERE donation_id = ?";
                    } elseif ($row['donation_type'] == 'Fund') {
                        $type_query = "SELECT amount, account_number, pan_card_number FROM fund_donations WHERE donation_id = ?";
                    }

                    if (isset($type_query)) {
                        $type_stmt = $mysqli->prepare($type_query);
                        $type_stmt->bind_param('i', $row['id']);
                        $type_stmt->execute();
                        $type_result = $type_stmt->get_result();
                        $type_details = $type_result->fetch_assoc();
                    }
                    ?>
                    <!-- Display type-specific details -->
                    <?php if ($row['donation_type'] == 'Clothes' && $type_details): ?>
                        <p><span class="highlight">Pairs for Old People:</span> <?php echo htmlspecialchars($type_details['pairs_old']); ?></p>
                        <p><span class="highlight">Pairs for Children:</span> <?php echo htmlspecialchars($type_details['pairs_children']); ?></p>
                    <?php elseif ($row['donation_type'] == 'Food' && $type_details): ?>
                        <p><span class="highlight">Food Occasion:</span> <?php echo htmlspecialchars($type_details['food_occasion']); ?></p>
                        <p><span class="highlight">Food Type:</span> <?php echo htmlspecialchars($type_details['food_type']); ?></p>
                        <p><span class="highlight">Quantity:</span> <?php echo htmlspecialchars($type_details['quantity']); ?></p>
                    <?php elseif ($row['donation_type'] == 'Groceries' && $type_details): ?>
                        <p><span class="highlight">Grocery Description:</span> <?php echo htmlspecialchars($type_details['grocery_description']); ?></p>
                    <?php elseif ($row['donation_type'] == 'Stationary' && $type_details): ?>
                        <p><span class="highlight">Stationary Description:</span> <?php echo htmlspecialchars($type_details['stationary_description']); ?></p>
                    <?php elseif ($row['donation_type'] == 'Fund' && $type_details): ?>
                        <p><span class="highlight">Amount:</span> <?php echo htmlspecialchars($type_details['amount']); ?></p>
                        <p><span class="highlight">Account Number:</span> <?php echo htmlspecialchars($type_details['account_number']); ?></p>
                        <p><span class="highlight">PAN Card:</span> <?php echo htmlspecialchars($type_details['pan_card_number']); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($row['recipient_name'])): ?>
                        <p><span class="highlight">Recipient:</span> <?php echo htmlspecialchars($row['recipient_name']); ?></p>
                        <p><span class="highlight">Recipient Contact:</span> <?php echo htmlspecialchars($row['recipient_contact']); ?></p>
                    <?php endif; ?>
                    <p><span class="highlight">Date:</span> <?php echo htmlspecialchars($row['donation_date']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-donations">No donations found.</p>
        <?php endif; ?>
    </div>
    <a href="javascript:history.back()" class="back-button">Back</a>
   
</body>
</html>
<?php
$stmt->close();
$mysqli->close();
?>
