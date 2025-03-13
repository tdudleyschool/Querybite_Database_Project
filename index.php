<?php
// Database credentials
include 'dp_connection.php';

// Function to display table contents with a nice format
function displayTable($conn, $tableName) {
    // Prepare the SQL query to select a maximum of 10 rows
    $sql = "SELECT * FROM $tableName LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Get the column names
    $columns = array();
    $columnCount = $stmt->columnCount();
    for ($i = 0; $i < $columnCount; $i++) {
        $column = $stmt->getColumnMeta($i);
        $columns[] = $column['name'];
    }

    // Display the table header
    echo "<h3>Displaying Table: $tableName</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; margin-bottom: 20px;'>";
    echo "<tr>";
    foreach ($columns as $column) {
        echo "<th>$column</th>";
    }
    echo "</tr>";

    // Display the table data
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<td>" . htmlspecialchars($row[$column]) . "</td>";
        }
        echo "</tr>";
    }

    // Display record count
    echo "</table>";
    echo "<p>" . $stmt->rowCount() . " records found in the $tableName table.</p><br>";
}

// Display the contents of each table
$tables = [
    'customers',
    'dish',
    'dishingredients',
    'favoritemeal',
    'ingredients',
    'likedingredients',
    'likedrestaurants',
    'menu',
    'ordereddishes',
    'orders',
    'reservations',
    'restaurant',
    'reviews'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Bite Databases</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        h1 {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            margin: 0;
        }
        h3 {
            margin-top: 20px;
        }
        table {
            margin: 0 auto;
            background-color: white;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>Query Bite Databases</h1>

<?php
// Loop through each table and display its contents
foreach ($tables as $table) {
    displayTable($conn, $table);
}
?>

</body>
</html>
