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

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? '';

    // Validate rating input
    if (empty($rating) || !in_array($rating, [1, 2, 3, 4, 5])) {
        $error_message = "Please provide a valid rating between 1 and 5.";
    } else {
        try {
            // Get current date and time
            $current_date = date('Y-m-d');
            $current_time = date('H:i:s');

            // Insert the rating into the ratings table
            $stmt = $conn->prepare("INSERT INTO reviews (restaurant_id, customer_id, rating, Date, Time) VALUES (:restaurant_id, :customer_id, :rating, :current_date, :current_time)");
            $stmt->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt->bindParam(':current_date', $current_date, PDO::PARAM_STR);
            $stmt->bindParam(':current_time', $current_time, PDO::PARAM_STR);
            $stmt->execute();

            // Set success message
            $success_message = "Your review has been successfully submitted!";
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
    <title>Write a Review</title>
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
        select {
            padding: 10px;
            width: 100%;
            margin-top: 5px;
        }
        button {
            padding: 10px 20px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            border: 1px solid lightgray;
            background-color: #fff;
        }

        /* Updated button styling */
        .submit-review-btn {
            width: 85%;
            margin: 0 auto;
            display: block;
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

    <div class="subheading">Write a Review</div>

    <!-- Display Error Message -->
    <?php if ($error_message): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Display Success Message -->
    <?php if ($success_message): ?>
        <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <!-- Review Form -->
    <form method="post" action="review.php?customer_id=<?php echo $customer_id; ?>&restaurant_id=<?php echo $restaurant_id; ?>">
        <div class="form-group">
            <label for="rating">Rating (1 to 5):</label>
            <select id="rating" name="rating" required>
                <option value="">Select Rating</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>

        <!-- Submit Review Button -->
        <button type="submit" class="submit-review-btn">Submit Review</button>
    </form>
</body>
</html>

