<?php
// Start session to access manager session if needed
session_start();

// Include database connection
include 'dp_connection.php';

// Initialize variables
$restaurant_id = null;
$restaurant_name = null;
$reservation_results = [];
$order_results = [];
$all_orders_results = [];

// Fetch restaurants for dropdown
$restaurants = [];
try {
    $stmt = $conn->prepare("SELECT Restaurant_ID, Name FROM restaurant");
    $stmt->execute();
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching restaurants: " . $e->getMessage();
}

// Handle restaurant selection and queries
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restaurant_select'])) {
        // Set the restaurant ID when selected
        $restaurant_id = $_POST['restaurant_select'];
        // Get restaurant name
        $stmt = $conn->prepare("SELECT Name FROM restaurant WHERE Restaurant_ID = :restaurant_id");
        $stmt->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
        $stmt->execute();
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
        $restaurant_name = $restaurant['Name'] ?? null;
    }

    // Reservation View For a Day (Reservation Query)
    if (isset($_POST['view_reservations'])) {
        $query = "SELECT 
                    r.Restaurant_ID,
                    r.Name AS Restaurant_Name,
                    r.total_tables - IFNULL(SUM(res.tables_reserved), 0) AS Available_Tables
                  FROM 
                    restaurant r
                  LEFT JOIN 
                    reservations res 
                    ON r.Restaurant_ID = res.Restaurant_ID 
                    AND res.Resurvation_Date = '2024-11-14' 
                    AND res.Resurvation_Time = '19:00:00'
                  WHERE 
                    r.Restaurant_ID = :restaurant_id
                  GROUP BY 
                    r.Restaurant_ID, r.Name";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
        $stmt->execute();
        $reservation_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Orders made on a specific date (Orders Query)
    if (isset($_POST['view_orders'])) {
        $query = "SELECT 
                    d.Name AS Dish_Name, 
                    d.Price, 
                    d.Calories, 
                    od.Quantity, 
                    o.orderdate
                  FROM 
                    ordereddishes od
                  JOIN 
                    dish d ON od.Dish_ID = d.Dish_ID
                  JOIN 
                    menu m ON d.Menu_ID = m.Menu_ID
                  JOIN 
                    orders o ON od.Order_ID = o.Order_ID
                  WHERE 
                    m.Restaurant_ID = :restaurant_id
                    AND o.orderdate = '2024-11-11'";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
        $stmt->execute();
        $order_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Display all orders from customers (All Orders Query)
    if (isset($_POST['view_all_orders'])) {
        $query = "SELECT 
                    o.Order_ID, 
                    o.Customer_ID, 
                    c.Name AS Customer_Name, 
                    o.orderdate, 
                    od.Dish_ID, 
                    d.Name AS Dish_Name, 
                    m.Restaurant_ID, 
                    r.Name AS Restaurant_Name, 
                    d.Price
                  FROM 
                    orders o
                  JOIN 
                    ordereddishes od ON o.Order_ID = od.Order_ID
                  JOIN 
                    dish d ON od.Dish_ID = d.Dish_ID
                  JOIN 
                    menu m ON d.Menu_ID = m.Menu_ID
                  JOIN 
                    restaurant r ON m.Restaurant_ID = r.Restaurant_ID
                  JOIN 
                    customers c ON o.Customer_ID = c.Customer_ID
                  WHERE 
                    o.Customer_ID = 2"; // Change the Customer_ID as per requirements

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $all_orders_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <style>
        body {
            background-color: #e0e0e0; /* Same background color */
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #f2f2f2; /* Same header background */
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
            height: 100px; /* Logo size */
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
        select, button {
            padding: 10px;
            margin-top: 5px;
            font-size: 1em;
        }
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>QueryBite <img src="imagefiles/Q2.png" alt="Logo"></h1> <!-- Logo in header -->
        <div class="buttons">
            <button onclick="location.href='login.php'">Logout</button>
        </div>
    </div>

    <h1>Manager Dashboard</h1>

    <form method="POST" action="manager.php">
        <!-- Restaurant Select Dropdown -->
        <div class="form-group">
            <label for="restaurant_select">Select Restaurant:</label>
            <select name="restaurant_select" id="restaurant_select" required>
                <option value="">Select a Restaurant</option>
                <?php foreach ($restaurants as $restaurant): ?>
                    <option value="<?php echo $restaurant['Restaurant_ID']; ?>">
                        <?php echo htmlspecialchars($restaurant['Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Select Restaurant</button>
        </div>
    </form>

    <?php if ($restaurant_name): ?>
        <h2>Restaurant: <?php echo htmlspecialchars($restaurant_name); ?></h2>
    <?php endif; ?>

    <!-- Buttons to execute queries -->
    <?php if ($restaurant_id): ?>
        <form method="POST" action="manager.php">
            <input type="hidden" name="restaurant_select" value="<?php echo $restaurant_id; ?>">
            <button type="submit" name="view_reservations">View Reservation for a Day</button>
            <button type="submit" name="view_orders">View Orders Made on Specific Date</button>
            <button type="submit" name="view_all_orders">View All Orders from Customers</button>
        </form>
    <?php endif; ?>

    <!-- Reservation Results -->
    <?php if (!empty($reservation_results)): ?>
        <h3>Reservation View for a Day</h3>
        <table>
            <thead>
                <tr>
                    <th>Restaurant ID</th>
                    <th>Restaurant Name</th>
                    <th>Available Tables</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservation_results as $row): ?>
                    <tr>
                        <td><?php echo $row['Restaurant_ID']; ?></td>
                        <td><?php echo $row['Restaurant_Name']; ?></td>
                        <td><?php echo $row['Available_Tables']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Orders Results -->
    <?php if (!empty($order_results)): ?>
        <h3>Orders Made on Specific Date</h3>
        <table>
            <thead>
                <tr>
                    <th>Dish Name</th>
                    <th>Price</th>
                    <th>Calories</th>
                    <th>Quantity</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_results as $row): ?>
                    <tr>
                        <td><?php echo $row['Dish_Name']; ?></td>
                        <td><?php echo $row['Price']; ?></td>
                        <td><?php echo $row['Calories']; ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td><?php echo $row['orderdate']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- All Orders from Customers -->
    <?php if (!empty($all_orders_results)): ?>
        <h3>All Orders from Customers</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Dish ID</th>
                    <th>Dish Name</th>
                    <th>Restaurant ID</th>
                    <th>Restaurant Name</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_orders_results as $row): ?>
                    <tr>
                        <td><?php echo $row['Order_ID']; ?></td>
                        <td><?php echo $row['Customer_ID']; ?></td>
                        <td><?php echo $row['Customer_Name']; ?></td>
                        <td><?php echo $row['orderdate']; ?></td>
                        <td><?php echo $row['Dish_ID']; ?></td>
                        <td><?php echo $row['Dish_Name']; ?></td>
                        <td><?php echo $row['Restaurant_ID']; ?></td>
                        <td><?php echo $row['Restaurant_Name']; ?></td>
                        <td><?php echo $row['Price']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>

