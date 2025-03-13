<?php
// Start session to access customer_id
session_start();

// Check if customer_id and restaurant_id are set
if (!isset($_GET['customer_id']) || !isset($_GET['restaurant_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_GET['customer_id'];
$restaurant_id = $_GET['restaurant_id'];

// Database connection
include 'dp_connection.php';

// Initialize error message and success message
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_date = $_POST['reservation_date'] ?? '';
    $selected_time = $_POST['reservation_time'] ?? '';

    // Validate inputs
    if (empty($selected_date) || empty($selected_time)) {
        $error_message = "Please select both a date and a time.";
    } else {
        try {
            // Call the stored procedure to make a reservation
            $stmt = $conn->prepare("CALL MakeReservation(:restaurant_id, :customer_id, :reservation_date, :reservation_time)");
            $stmt->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':reservation_date', $selected_date, PDO::PARAM_STR);
            $stmt->bindParam(':reservation_time', $selected_time, PDO::PARAM_STR);
            $stmt->execute();

            // Set success message
            $success_message = "Your reservation has been successfully made!";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Reservation</title>
    <style>
        body {
            background-color: #e0e0e0;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
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
        }
        .subheading {
            text-align: center;
            font-size: 2em;
            margin: 10px 0;
            color: #333;
        }
        .content {
            padding: 20px;
        }
        .content .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .content .actions button,
        .content .actions select,
        .content .actions input {
            padding: 10px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            border: 1px solid lightgray;
            background-color: #fff;
        }
        .content .error {
            color: red;
            margin-bottom: 20px;
        }
        .content .success {
            color: green;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input[type="date"], input[type="time"], button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
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

    <div class="subheading">Make a Reservation</div>

    <!-- Display Error Message -->
    <?php if ($error_message): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Display Success Message -->
    <?php if ($success_message): ?>
        <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <!-- Reservation Form -->
    <form method="post" action="makereservations.php?customer_id=<?php echo $customer_id; ?>&restaurant_id=<?php echo $restaurant_id; ?>">
        <div class="form-group">
            <label for="reservation_date">Select Date:</label>
            <input type="date" id="reservation_date" name="reservation_date" min="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label for="reservation_time">Select Time:</label>
            <input type="time" id="reservation_time" name="reservation_time" required>
        </div>

        <button type="submit">Make Reservation</button>
    </form>
</body>
</html>
