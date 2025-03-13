<?php
session_start();

if (!isset($_GET['customer_id'])) {
    header("Location: customer_home.php");
    exit();
}
$customer_id = $_GET['customer_id'];

// Database connection
include 'dp_connection.php'; 

// Fetch customer name
$customer_query = $conn->prepare("SELECT Name FROM customers WHERE Customer_ID = :customer_id");
$customer_query->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$customer_query->execute();
$customer = $customer_query->fetch(PDO::FETCH_ASSOC);
$customer_name = $customer['Name'] ?? 'Guest';

// Query to get restaurant names
$restaurant_query = $conn->prepare("SELECT Restaurant_ID, name FROM restaurant");
$restaurant_query->execute();
$restaurant_names = $restaurant_query->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['view_restaurant'])) {
        $restaurant_id = $_POST['restaurant_id']; // Get the selected restaurant ID
        header("Location: restaurant.php?restaurant_id=$restaurant_id&customer_id=$customer_id"); // Redirect to restaurant.php
        exit();
    }
}

// If customer ID is set, fetch reservations and recommendations
$reservations = [];
$ingredients = [];

if ($customer_id) {
    // Query to get closest restaurants
    $reservation_query = $conn->prepare("SELECT c.Name AS Customer_Name, r.Restaurant_ID, r.Name AS Restaurant_Name, 
        SQRT(POWER(c.Longitude - r.Longitude, 2) + POWER(c.Latitude - r.Latitude, 2)) AS Distance 
        FROM customers AS c 
        JOIN restaurant AS r ON (SELECT COUNT(*) FROM restaurant AS r2 
        WHERE SQRT(POWER(c.Longitude - r2.Longitude, 2) + POWER(c.Latitude - r2.Latitude, 2)) 
        < SQRT(POWER(c.Longitude - r.Longitude, 2) + POWER(c.Latitude - r.Latitude, 2)) AND c.Customer_ID = c.Customer_ID) < 3 
        && c.Customer_ID = :customer_id ORDER BY c.Customer_ID, Distance;");
    $reservation_query->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    $reservation_query->execute();
    $reservations = $reservation_query->fetchAll(PDO::FETCH_ASSOC);

    // Query for recommendations
    $ingredient_query = $conn->prepare("SELECT f.Restaurant_Name, Max(Total_Ingredient_Count) 
        FROM ( SELECT t1.Restaurant_Name, COUNT(t1.Ingredient_Count) AS Total_Ingredient_Count 
        FROM (SELECT r.Restaurant_ID, r.Name AS Restaurant_Name, i.Name AS Ingredient_Name, i.ingredient_ID, COUNT(di.ingredient_ID) AS Ingredient_Count 
        FROM restaurant r 
        JOIN menu m ON r.Restaurant_ID = m.Restaurant_ID 
        JOIN dish d ON m.Menu_ID = d.Menu_ID 
        JOIN dishingredients di ON d.Dish_ID = di.Dish_ID 
        JOIN ingredients i ON di.ingredient_ID = i.ingredient_ID 
        GROUP BY r.Restaurant_ID, r.Name, i.Name, i.ingredient_ID) t1 
        JOIN (SELECT c.Customer_ID, i.ingredient_ID 
        FROM customers c 
        JOIN favoritemeal fm ON c.Customer_ID = fm.Customer_ID 
        JOIN dishingredients di ON fm.Dish_ID = di.Dish_ID 
        JOIN ingredients i ON di.ingredient_ID = i.ingredient_ID) t2 
        ON t1.ingredient_ID = t2.ingredient_ID 
        WHERE t2.Customer_ID = :customer_id 
        GROUP BY t1.Restaurant_Name 
        ORDER BY Total_Ingredient_Count DESC) f;");
    $ingredient_query->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    $ingredient_query->execute();
    $ingredients = $ingredient_query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Home</title>
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
        .tables {
            display: block;
            gap: 20px;
        }
        .tables table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
        }
        .tables th,
        .tables td {
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
            <button onclick="location.href='profile.php?customer_id=<?php echo $customer_id; ?>'">Profile</button>
        </div>
    </div>
    <div class="subheading">Welcome <?php echo htmlspecialchars($customer_name); ?>!</div>

    <div class="content">
        <div class="actions">
            <form method="post">
                <select name="restaurant_id">
                    <?php foreach ($restaurant_names as $restaurant): ?>
                        <option value="<?php echo $restaurant['Restaurant_ID']; ?>">
                            <?php echo htmlspecialchars($restaurant['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="view_restaurant">View Restaurant</button>
            </form>
        </div>
        
        <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
        
        <?php if ($customer_id): ?>
            <div class="tables">
    <h2 style="text-align: center; font-weight: bold; margin-bottom: 10px;">Restaurants Closest To You!</h2>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Restaurant Name</th>
                <th>Distance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['Customer_Name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['Restaurant_Name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['Distance'] ?? 'N/A'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2 style="text-align: center; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">Reccomented Nearest Restaurant Based On Your Intrest</h2>
    <table>
        <thead>
            <tr>
                <th>Restaurant Name</th>
                <th>Total Ingredient Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ingredients as $ingredient): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ingredient['Restaurant_Name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ingredient['Max(Total_Ingredient_Count)'] ?? 'N/A'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

        <?php endif; ?>
    </div>
</body>
</html>
