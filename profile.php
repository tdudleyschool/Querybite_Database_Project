<?php
// Start session to access customer_id
session_start();

// Check if customer_id is set, redirect to login if not
if (!isset($_GET['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_GET['customer_id'];

// Database connection
include 'dp_connection.php';

// Fetch customer details
$customer_query = "SELECT Name, Pass FROM customers WHERE Customer_ID = :customer_id";
$stmt = $conn->prepare($customer_query);
$stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch current reservations
$current_time = date("H:i:s");
$current_date = date("Y-m-d");
$reservations_query = "
SELECT Reservation_ID, Restaurant_ID, Resurvation_Date, Resurvation_Time
FROM reservations
WHERE Customer_ID = :customer_id
  AND (Resurvation_Date > :current_date
       OR (Resurvation_Date = :current_date AND Resurvation_Time >= DATE_SUB(:current_time, INTERVAL 1 HOUR)))
ORDER BY Resurvation_Date, Resurvation_Time;
";
$reservations_stmt = $conn->prepare($reservations_query);
$reservations_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$reservations_stmt->bindParam(':current_date', $current_date, PDO::PARAM_STR);
$reservations_stmt->bindParam(':current_time', $current_time, PDO::PARAM_STR);
$reservations_stmt->execute();
$reservations = $reservations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_username'])) {
        $new_username = $_POST['username'];
        $update_username_query = "UPDATE customers SET Name = :new_username WHERE Customer_ID = :customer_id";
        $stmt = $conn->prepare($update_username_query);
        $stmt->bindParam(':new_username', $new_username, PDO::PARAM_STR);
        $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['edit_password'])) {
        $new_password = $_POST['password'];
        $update_password_query = "UPDATE customers SET Pass = :new_password WHERE Customer_ID = :customer_id";
        $stmt = $conn->prepare($update_password_query);
        $stmt->bindParam(':new_password', $new_password, PDO::PARAM_STR);
        $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Refresh page to display updated data
    header("Location: profile.php?customer_id=$customer_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            background-color: #e0e0e0;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .header {
            display: flex;
            align-items: center;
            padding: 20px;
            background-color: #f2f2f2;
        }
        .header h1 {
            flex: 1;
            text-align: left;
            font-size: 6em;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .header h1 img {
            margin-left: 20px;
            width: 100px;
            height: 100px;
        }
        .header .buttons {
            margin-left: auto;
        }
        .header .buttons button {
            padding: 10px 20px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            border: 1px solid lightgray;
            background-color: #fff;
        }
        .content {
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .content button {
            padding: 10px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            border: 1px solid lightgray;
            background-color: #fff;
        }
        .tables table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }
        .tables th, .tables td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .tables th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>QueryBite <img src="imagefiles/Q2.png" alt="Logo"></h1>
        <div class="buttons">
            <button onclick="location.href='login.php'">Logout</button>
            <button onclick="location.href='customer_home.php?customer_id=<?php echo $customer_id; ?>'">Home</button>
        </div>
    </div>

    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($customer['Name']); ?>!</h1>

        <div class="form-group">
            <label for="username">Username:</label>
            <form method="post">
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($customer['Name']); ?>">
                <button type="submit" name="edit_username">Edit</button>
            </form>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <form method="post">
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($customer['Pass']); ?>">
                <button type="submit" name="edit_password">Edit</button>
            </form>
        </div>

        <div class="tables">
            <h3>Current Reservations</h3>
            <table>
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Restaurant Name</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="4">No current reservations.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['Reservation_ID']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Restaurant_Name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Date']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Time']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>