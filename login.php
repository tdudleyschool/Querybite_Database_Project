<?php
session_start();
include 'dp_connection.php';  // Replace with your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);

        // Query to verify username and password
        $login_query = $conn->prepare("SELECT Customer_ID FROM customers WHERE username = :username AND Pass = :password");
        $login_query->bindParam(':username', $username, PDO::PARAM_STR);
        $login_query->bindParam(':password', $password, PDO::PARAM_STR);
        $login_query->execute();

        $result = $login_query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Store the Customer_ID in a session variable
            $customer_id = $result['Customer_ID'];

            // Redirect to customer_home.php
            header("Location: customer_home.php?customer_id=$customer_id");
            exit();
        } else {
            $error = "Invalid username or password. Please try again.";
        }
    }
}

// Redirect to manager login page when manager button is clicked
if (isset($_POST['manager_login'])) {
    header("Location: manager.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            display: flex;
            background-color: #e0e0e0; /* Lighter gray */
            font-family: Arial, sans-serif;
            margin: 0;
            height: 100vh;
        }
        .left-half, .right-half {
            flex: 1;
            height: 100%;
        }
        .left-half {
            display: flex;
            flex-direction: column;
            padding: 40px 60px;
        }
        .right-half {
            background: url('imagefiles/QueryBite_Image.jpg') no-repeat center center;
            background-size: cover;
        }
        h1.cursive {
            font-family: Arial, sans-serif;
            font-size: 6em; /* Larger heading */
            margin: 0;
        }
        .logo {
            width: 150px; /* Larger logo */
            height: 150px;
            margin-left: 20px;
        }
        .welcome-message {
            font-size: 1.5em;
            margin-top: 20px;
        }
        .welcome-message h2, .welcome-message p {
            margin: 0;
        }
        .login-form {
            margin-top: 40px;
            width: 100%;
        }
        .login-form .buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px; /* More space between button rows */
        }
        .login-form button {
            padding: 15px; /* Larger buttons */
            border: 1px solid lightgray; /* Light gray outline */
            font-weight: bold;
            text-transform: uppercase;
            font-size: 1.2em; /* Larger text */
            cursor: pointer;
            width: 100%; /* Full width */
            margin-top: 20px;
        }
        .login-form .view-button {
            background-color: lightgray; /* Slightly darker gray */
            color: black;
        }
        .login-form .register-button {
            background-color: #FFDDC1; /* Lighter orange */
            color: black;
        }
        .login-form .manager-button {
            background-color: rgba(0, 0, 255, 0.3); /* Transparent blue */
            color: black;
        }
    </style>
</head>
<body>
    <div class="left-half">
        <div style="display: flex; align-items: center;">
            <h1 class="cursive">QueryBite</h1>
            <img src="imagefiles/Q2.png" alt="Large Logo" class="logo">
        </div>
        <div class="welcome-message">
            <h2>Welcome to QueryBite!</h2>
            <p>A onestop solution for both customers and managers.</p>
        </div>
        <form class="login-form" action="login.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <div class="buttons">
                <button type="submit" class="login-button">Log In</button>
                <button type="button" class="register-button">Register</button>
            </div>
            <button type="submit" name="manager_login" class="manager-button">Manager Login</button>
        </form>
    </div>
    <div class="right-half"></div>
</body>
</html>
