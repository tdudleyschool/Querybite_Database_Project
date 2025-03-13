<?php
session_start();

// Check if restaurant_id and customer_id are set
if (!isset($_GET['restaurant_id']) && !isset($_GET['customer_id'])) {
    header("Location: login.php");
    exit();
}

$restaurant_id = $_GET['restaurant_id'];
$customer_id = $_GET['customer_id'];

// Database connection
include 'dp_connection.php';

// Query to get restaurant details
$restaurant_query = $conn->prepare("SELECT * FROM restaurant WHERE restaurant_id = :restaurant_id");
$restaurant_query->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);

// Execute the query
$restaurant_query->execute();

// Fetch restaurant data
$restaurant = $restaurant_query->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    header("Location: customer_home.php");
    exit();
}

// Get the next available reservation time
$next_available_time_query = $conn->prepare("SELECT GetNextAvailableTime(1, CURDATE(), CURTIME()) AS NextAvailableTime");
$next_available_time_query->execute();
$next_available_time = $next_available_time_query->fetch(PDO::FETCH_ASSOC)['NextAvailableTime'];

// Handle View Menu
$menu = [];
if (isset($_POST['view_menu'])) {
    $menu_query = $conn->prepare("SELECT 
    r.Name AS Restaurant_Name,
    d.Name AS Dish_Name,
    d.Price,
    d.Calories,
    d.Description,
    d.Dish_ID
FROM 
    restaurant r
JOIN 
    menu m ON r.Restaurant_ID = m.Restaurant_ID
JOIN 
    dish d ON m.Menu_ID = d.Menu_ID
WHERE 
    r.Restaurant_ID = :restaurant_id");
    $menu_query->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
    $menu_query->execute();
    $menu = $menu_query->fetchAll(PDO::FETCH_ASSOC);
}

$ingredients = [];
$ingredient_error = '';
if (isset($_POST['find_dish'])) {
    $input_name = htmlspecialchars($_POST['ingredient_name']);
    $ingredient_query = $conn->prepare("SELECT 
    m.Restaurant_ID,
    d.Dish_ID,
    d.Name AS Dish_Name,
    m.Menu_Name,
    i.name AS Ingredient_Name
FROM 
    menu m
JOIN 
    dish d ON m.Menu_ID = d.Menu_ID
JOIN 
    dishingredients di ON d.Dish_ID = di.Dish_ID
JOIN 
    ingredients i ON di.Ingredient_ID = i.ingredient_ID
WHERE 
    m.Restaurant_ID = :restaurant_id
    AND i.name = :name
ORDER BY 
    d.Name;");
    $ingredient_query->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
    $ingredient_query->bindParam(':name', $input_name, PDO::PARAM_STR);
    $ingredient_query->execute();
    $ingredients = $ingredient_query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($ingredients)) {
        $ingredient_error = "No matching ingredient found.";
    }
}

// Recommended Dish Section
$user_error = '';
if (isset($_POST['find_recommended_dish'])) {
    if ($customer_id) {
        $recommended_dish_query = $conn->prepare("SELECT *
        FROM (
            SELECT 
                m.Restaurant_ID,
                m.Menu_ID,
                d.Dish_ID,
                d.Name,
                d.Description,
                d.Price,
                c.Customer_ID,
                c.Name AS Customer_Name,
                i.Name AS Ingredient_Name
            FROM 
                menu m
            JOIN 
                dish d ON m.Menu_ID = d.Menu_ID
            JOIN 
                dishingredients di ON d.Dish_ID = di.Dish_ID
            JOIN 
                ingredients i ON di.Ingredient_ID = i.Ingredient_ID
            JOIN 
                likedingredients cp ON i.Ingredient_ID = cp.Ingredient_ID
            JOIN 
                customers c ON cp.Customer_ID = c.Customer_ID
            ORDER BY 
                c.Customer_ID, d.Dish_ID
        ) AS subquery
        WHERE 
            subquery.Customer_ID = :customer_id 
            AND subquery.Restaurant_ID = :restaurant_id;");
        
        $recommended_dish_query->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $recommended_dish_query->bindParam(':restaurant_id', $restaurant_id, PDO::PARAM_INT);
        $recommended_dish_query->execute();
        
        $recommended_dishes = $recommended_dish_query->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($recommended_dishes)) {
            $user_error = "No recommended dishes found for this user.";
        }
    } else {
        $user_error = "User not found.";
    }
}

// Reservation Section (if needed, for future use)
// You can add logic for making a reservation, etc.

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($restaurant['name']); ?></title>
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
            <form method="get" action="customer_home.php">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <button type="submit">Home</button>
            </form>
            <form method="get" action="profile.php">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <button type="submit">Profile</button>
            </form>
        </div>
    </div>

    <div class="subheading"><?php echo htmlspecialchars($restaurant['Name']); ?></div>

    <div class="content">
        <div class="stats">
            <div>
                <p><strong>Number of Tables:</strong> <?php echo htmlspecialchars($restaurant['total_tables']); ?></p>
                <p><strong>Restaurant Hours:</strong> <?php echo htmlspecialchars($restaurant['open_time']); ?> - <?php echo htmlspecialchars($restaurant['close_time']); ?></p>
            </div>
            <div>
                <p><strong>Next Available Reservation Time:</strong> <?php echo $next_available_time; ?></p>
                
                <form method="get" action="makereservation.php">
                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_id; ?>">
                    <button type="submit">Make Reservation</button>
                </form>

                <form method="get" action="review.php">
                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_id; ?>">
                    <button type="submit">Leave a Review</button>
                </form>
            </div>
        </div>

        <div class="actions">
            <form method="post">
                <button type="submit" name="view_menu">View Menu</button>
            </form>
            <form method="post">
                <input type="text" name="ingredient_name" required placeholder="Enter ingredient name">
                <button type="submit" name="find_dish">Find Dish</button>
            </form>
        </div>

        <?php if ($ingredient_error) { echo "<p class='error'>$ingredient_error</p>"; } ?>
        
        <?php if (!empty($menu)): ?>
            <div class="tables">
                <table>
                    <thead>
                        <tr>
                            <th>Dish ID</th>
                            <th>Dish Name</th>
                            <th>Description</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menu as $dish): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dish['Dish_ID']); ?></td>
                                <td><?php echo htmlspecialchars($dish['Dish_Name']); ?></td>
                                <td><?php echo htmlspecialchars($dish['Description']); ?></td>
                                <td><?php echo htmlspecialchars($dish['Price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!empty($ingredients)): ?>
            <div class="tables">
                <table>
                    <thead>
                        <tr>
                            <th>Dish ID</th>
                            <th>Dish Name</th>
                            <th>Ingredient Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ingredient['Dish_ID']); ?></td>
                                <td><?php echo htmlspecialchars($ingredient['Dish_Name']); ?></td>
                                <td><?php echo htmlspecialchars($ingredient['Ingredient_Name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="actions">
            <form method="post">
                <button type="submit" name="find_recommended_dish">Find Recommended Dish</button>
            </form>
        </div>

        <?php if ($user_error) { echo "<p class='error'>$user_error</p>"; } ?>

        <?php if (!empty($recommended_dishes)): ?>
            <div class="tables">
                <table>
                    <thead>
                        <tr>
                            <th>Dish ID</th>
                            <th>Dish Name</th>
                            <th>Description</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recommended_dishes as $dish): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dish['Dish_ID']); ?></td>
                                <td><?php echo htmlspecialchars($dish['Name']); ?></td>
                                <td><?php echo htmlspecialchars($dish['Description']); ?></td>
                                <td><?php echo htmlspecialchars($dish['Price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
