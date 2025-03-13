-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2025 at 06:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `querybite`
--
create database querybite;
use querybite;
DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `MakeReservation` (IN `p_restaurant_id` INT, IN `p_customer_id` INT, IN `p_reservation_date` DATE, IN `p_reservation_time` TIME)   BEGIN
    DECLARE v_open_time TIME;
    DECLARE v_close_time TIME;
    DECLARE v_total_tables INT;
    DECLARE v_reservations_count INT DEFAULT 0;
    DECLARE v_hourly_time TIME;
    DECLARE v_exists INT;


    -- Round reservation time to the hour for consistency
    SET v_hourly_time = MAKETIME(HOUR(p_reservation_time), 0, 0);

    -- Retrieve restaurant's operating hours and total tables
    SELECT open_time, close_time, total_tables
    INTO v_open_time, v_close_time, v_total_tables
    FROM restaurant
    WHERE restaurant_id = p_restaurant_id;

    -- Check if reservation time is within operating hours
    IF v_hourly_time < v_open_time OR v_hourly_time >= v_close_time THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reservation time is outside restaurant operating hours';
    END IF;

SELECT COUNT(*)
INTO v_exists
FROM reservations
WHERE resurvation_time = v_hourly_time
  AND resurvation_date = p_reservation_date
  AND customer_id = p_customer_id;

IF v_exists > 0 THEN
    -- Row exists; handle accordingly, e.g., raise an error or perform an action
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'A reservation with this reservation_id, reservation_date, and customer_id already exists';
END IF;

    -- Count the existing reservations for the requested hourly time slot
    SELECT COUNT(*)
    INTO v_reservations_count
    FROM reservations
    WHERE restaurant_id = p_restaurant_id
      AND resurvation_date = p_reservation_date
      AND resurvation_time = v_hourly_time;

    -- Check if tables are available within the requested hour
    IF v_reservations_count >= v_total_tables THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No tables available for the requested time slot';
    ELSE
        INSERT INTO reservations (restaurant_id, customer_id, resurvation_date, resurvation_time, tables_reserved)
        VALUES (p_restaurant_id, p_customer_id,  p_reservation_date, v_hourly_time, 1);
    END IF;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetNextAvailableTime` (`p_restaurant_id` INT, `p_reservation_date` DATE, `p_requested_time` TIME) RETURNS DATETIME DETERMINISTIC BEGIN
    DECLARE v_open_time TIME;
    DECLARE v_close_time TIME;
    DECLARE v_total_tables INT;
    DECLARE v_check_date DATE;
    DECLARE v_check_hour TIME;
    DECLARE v_next_available DATETIME DEFAULT NULL;

    -- Retrieve the restaurant's operating hours and total tables
    SELECT open_time, close_time, total_tables
    INTO v_open_time, v_close_time, v_total_tables
    FROM restaurant
    WHERE restaurant_id = p_restaurant_id;

    -- Determine if the requested time is past closing; if so, start from the next date
    SET v_check_date = IF(p_requested_time >= v_close_time, DATE_ADD(p_reservation_date, INTERVAL 1 DAY), p_reservation_date);
    SET v_check_hour = IF(p_requested_time >= v_close_time, v_open_time, MAKETIME(HOUR(p_requested_time), 0, 0));

    -- Loop to find the next available hour
    WHILE v_next_available IS NULL DO
        -- Check if the current hour is within the restaurant's open hours
        IF v_check_hour >= v_open_time AND v_check_hour < v_close_time THEN
            -- Count reservations for this date, hour, and restaurant
            IF (
                SELECT COUNT(*)
                FROM reservations
                WHERE restaurant_id = p_restaurant_id
                  AND resurvation_date = v_check_date
                  AND resurvation_time = v_check_hour
            ) < v_total_tables THEN
                -- Available slot found
                SET v_next_available = TIMESTAMP(v_check_date, v_check_hour);
            END IF;
        END IF;

        -- Move to the next hour if no slot was found in the current hour
        SET v_check_hour = ADDTIME(v_check_hour, '01:00:00');
        
        -- If the hour exceeds closing time, go to the next date and reset to opening time
        IF v_check_hour >= v_close_time THEN
            SET v_check_date = DATE_ADD(v_check_date, INTERVAL 1 DAY);
            SET v_check_hour = v_open_time;
        END IF;
    END WHILE;

    RETURN v_next_available;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `Customer_ID` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Longitude` float NOT NULL,
  `Latitude` float NOT NULL,
  `username` varchar(25) DEFAULT NULL,
  `pass` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`Customer_ID`, `Name`, `Longitude`, `Latitude`, `username`, `pass`) VALUES
(1, 'John Smith', 32.7767, -96.797, 'jsmith1', 'tafari'),
(2, 'Emily Johnson', 32.7792, -96.8089, 'ejohnson2', 'pass123'),
(3, 'Michael Brown', 29.7604, -95.3698, 'mbrown3', 'pass123'),
(4, 'Sophia Martinez', 29.9865, -95.6815, 'smartinez4', 'pass123'),
(5, 'Oliver Garcia', 30.3847, -95.9897, 'ogarcia5', 'pass123'),
(6, 'Amy Jones', 29.9403, -95.4916, 'ajones6', 'pass123'),
(7, 'Jhon Walter', 32.7942, -95.561, 'jwalter7', 'pass123');

-- --------------------------------------------------------

--
-- Table structure for table `dish`
--

CREATE TABLE `dish` (
  `Dish_ID` int(11) NOT NULL,
  `Menu_ID` int(11) DEFAULT NULL,
  `Name` varchar(50) NOT NULL,
  `Price` decimal(5,2) NOT NULL,
  `Calories` float DEFAULT NULL,
  `Description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dish`
--

INSERT INTO `dish` (`Dish_ID`, `Menu_ID`, `Name`, `Price`, `Calories`, `Description`) VALUES
(1, 1, 'Green Chile Shrimp Ceviche', 16.00, 250, 'Spicy shrimp with fresh guacamole and tortilla chips.'),
(3, 2, 'The Local', 17.00, 800, 'Classic burger with bacon, avocado, and chipotle sauce.'),
(4, 2, 'Cheeseburger Americana', 16.00, 750, 'American cheese burger with tomato, pickle, and mayo.'),
(6, 3, 'Mama’s Meatloaf', 18.00, 650, 'Classic meatloaf with mushroom cream sauce and green beans.'),
(7, 4, 'Margherita', 15.00, 600, 'Fresh tomatoes, mozzarella, and basil pesto on crispy crust.'),
(9, 5, 'Cobb Salad', 15.00, 450, 'Classic Cobb with bacon, feta, and jalapeno ranch.'),
(10, 5, 'Honey Ginger-Roasted Brussels Sprouts Salad', 15.00, 400, 'Mixed greens with goat cheese, walnuts, and vinaigrette.'),
(12, 6, 'Philly Cheesesteak on Ciabatta', 17.00, 700, 'Steak with provolone, mushrooms, and peppers on ciabatta.'),
(14, 7, 'Beef Tenderloin Wrap', 16.00, 650, 'Tender beef wrap with avocado, black beans, and chipotle.'),
(16, 8, 'Crispy Chicken & Eggs', 16.00, 550, 'Crispy chicken breast with Anaheim chile sauce.'),
(18, 9, 'The Nutritious Bowl', 16.00, 500, 'Veggie patties with lentil rice, guacamole, and tahini.'),
(19, 10, 'Salade Maison', 17.00, 250, '.'),
(20, 10, 'Gulf Crab Begnets', 20.00, 300, '.'),
(21, 11, 'Gulf Snapper Crudo', 19.00, 800, ''),
(22, 11, 'Steak Tartare', 29.00, 900, '.'),
(23, 12, 'Warm Cinammon Roll', 12.00, 800, ''),
(24, 12, 'Delivered Eggs', 11.00, 750, '.'),
(25, 13, 'Edamame', 5.00, 120, 'Served warm and sprinkled with sea salt.'),
(26, 13, 'Spicy Edamame', 6.00, 130, 'Sautéed with spicy tsuyu no moto sauce.'),
(27, 13, 'Tempura', 12.00, 250, 'Shrimp or vegetable tempura served with dipping sauce.'),
(28, 13, 'Tuna Tartare', 15.00, 300, 'Tuna, avocado, green onions, tempura bits, tartare sauce, ceviche mayo; topped with crispy onions, m'),
(29, 13, 'Shrimp Lover\'s Roll', 14.00, 350, 'Shrimp tempura, shrimp, krab, avocado, cucumber.'),
(30, 13, 'Spicy Lotus Tempura Roll', 16.00, 370, 'Krab and cream cheese, lightly tempura battered, topped with spicy tuna, krab mix, sliced lotus root'),
(31, 14, 'Lazy Breakfast', 18.00, 450, 'Indulge in a breakfast masterpiece: two eggs your way with two options like mushrooms, onions, chedd'),
(34, 14, 'Breakfast Burrito', 18.00, 550, 'Indulge in a breakfast burrito masterpiece, featuring scrambled eggs, cheddar, black beans, peppers,'),
(35, 14, 'Guilt-Free Omelet', 9.00, 300, 'Sautéed mushrooms, spinach, onions, peppers, tomatoes, and Swiss cheese.'),
(36, 14, 'Illegal Chocolate Pancakes', 18.00, 650, 'Savor a short stack of rich chocolate chip pancakes, finished with spiced pecans, caramelized banana'),
(38, 14, 'Max French Toast', 19.00, 600, 'Toasted Brioche toast stuffed with cookie butter and topped with fresh vanilla cream.'),
(39, 14, 'French Onion Soup', 11.00, 300, 'A classic French onion soup with caramelized onions, rich broth, and a melted cheese topping.'),
(40, 14, 'Caesar Salad', 15.00, 400, 'House-made classic dressing, rustic croutons, grana padano, garlic ciabatta baguette. Add grilled ch'),
(41, 14, 'Mushroom Zen Bowl', 24.00, 500, 'Crispy shiitake protein, wild mushrooms, fresh sautéed vegetables, cashews, soy ginger glaze, and ja'),
(42, 14, 'Blackened Chicken Burger', 20.00, 600, 'Chicken breast, crisp pancetta bacon, cheddar, roasted garlic mayo, shredded lettuce & tomato served'),
(43, 14, 'Grilled New York Steak', 52.00, 700, '12 oz USDA Prime New York steak grilled to perfection, served with mashed potatoes, lemon quinoa or '),
(44, 14, 'Blackened Mahi Mahi', 30.00, 550, 'Spiced mahi mahi with chorizo & corn hash, creamy mashed potatoes & salsacado.'),
(45, 16, 'Honey Glazed Salmon', 18.00, 450, 'Tender salmon glazed with a sweet honey sauce, perfect with a side of vegetables or rice.'),
(46, 16, 'Cheesy Baked Chicken Tacos', 16.00, 550, 'Several cheesy baked chicken tacos stacked together in a white casserole dish topped with chopped fr'),
(47, 16, 'Beef Enchiladas', 14.00, 600, 'Two ground beef enchiladas on a white plate topped with enchilada sauce, melted cheese, and chopped '),
(48, 16, 'Creamy Cajun Chicken Pasta', 17.00, 700, 'Creamy Cajun chicken pasta in a stainless steel skillet topped with shaved Parmesan cheese and fresh'),
(49, 16, 'Caprese Chicken', 20.00, 500, 'Caprese chicken and cherry tomatoes in a skillet smothered in a balsamic reduction topped with fresh'),
(50, 16, 'Easy Air Fryer Salmon', 19.00, 400, 'Easy air fryer salmon topped with fresh chopped parsley in an air fryer basket.'),
(51, 16, 'Homemade Sloppy Joes', 15.00, 650, 'A hand holding a hamburger bun generously stuffed with homemade Sloppy Joes, perfect for a casual me'),
(52, 16, 'Creamy Beef and Shells', 16.00, 600, 'A generous serving of creamy beef and shells on a spoon, with two bowls of it in the background.');

-- --------------------------------------------------------

--
-- Table structure for table `dishingredients`
--

CREATE TABLE `dishingredients` (
  `Dish_ID` int(11) NOT NULL,
  `Ingredient_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dishingredients`
--

INSERT INTO `dishingredients` (`Dish_ID`, `Ingredient_ID`) VALUES
(1, 1),
(1, 4),
(1, 5),
(1, 30),
(3, 3),
(3, 9),
(3, 10),
(3, 14),
(4, 5),
(4, 10),
(4, 17),
(4, 18),
(6, 10),
(6, 13),
(6, 14),
(6, 33),
(7, 5),
(7, 6),
(7, 7),
(7, 10),
(9, 6),
(9, 15),
(9, 16),
(9, 22),
(10, 33),
(10, 34),
(10, 35),
(12, 10),
(12, 13),
(12, 18),
(12, 37),
(14, 4),
(14, 10),
(14, 46),
(14, 65),
(16, 12),
(16, 20),
(16, 45),
(18, 4),
(18, 27),
(18, 43),
(19, 15),
(19, 16),
(19, 70),
(20, 22),
(20, 29),
(20, 61),
(21, 4),
(21, 25),
(21, 40),
(22, 6),
(22, 10),
(22, 38),
(23, 11),
(23, 37),
(23, 39),
(24, 20),
(24, 49),
(25, 1),
(25, 35),
(25, 36),
(26, 1),
(26, 5),
(26, 52),
(27, 1),
(27, 13),
(27, 44),
(28, 2),
(28, 4),
(28, 69),
(29, 1),
(29, 2),
(29, 37),
(29, 63),
(30, 2),
(30, 36),
(30, 53),
(31, 20),
(31, 49),
(34, 20),
(34, 64),
(35, 20),
(35, 62),
(36, 20),
(36, 66),
(38, 20),
(38, 56),
(39, 13),
(39, 55),
(40, 16),
(40, 20),
(40, 44),
(41, 13),
(41, 45),
(42, 12),
(42, 43),
(43, 9),
(43, 74),
(44, 12),
(44, 31),
(45, 25),
(45, 71),
(46, 12),
(46, 72),
(47, 10),
(47, 68),
(48, 12),
(48, 73),
(49, 12),
(49, 74),
(50, 1),
(50, 33),
(51, 10),
(51, 55),
(52, 10),
(52, 61);

-- --------------------------------------------------------

--
-- Table structure for table `favoritemeal`
--

CREATE TABLE `favoritemeal` (
  `Customer_ID` int(11) NOT NULL,
  `Dish_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favoritemeal`
--

INSERT INTO `favoritemeal` (`Customer_ID`, `Dish_ID`) VALUES
(1, 12),
(1, 35),
(2, 16),
(2, 31),
(3, 22),
(3, 40),
(4, 9),
(4, 38),
(5, 18),
(5, 45);

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `Ingredient_ID` int(11) NOT NULL,
  `Name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`Ingredient_ID`, `Name`) VALUES
(1, 'Shrimp'),
(2, 'Tuna'),
(3, 'Bacon'),
(4, 'Avocado'),
(5, 'Tomato'),
(6, 'Mozzarella'),
(7, 'Basil'),
(8, 'Pineapple'),
(9, 'Lamb'),
(10, 'Beef'),
(11, 'Ground Beef'),
(12, 'Chicken'),
(13, 'Mushroom'),
(14, 'Green Beans'),
(15, 'Cilantro'),
(16, 'Lettuce'),
(17, 'Pickle'),
(18, 'Provolone Cheese'),
(19, 'Pita Bread'),
(20, 'Eggs'),
(21, 'Feta Cheese'),
(22, 'Cucumber'),
(23, 'Olives'),
(24, 'Capers'),
(25, 'Salmon'),
(26, 'Phyllo Dough'),
(27, 'Rice'),
(28, 'Spinach'),
(29, 'Crab'),
(30, 'Tempura Shrimp'),
(31, 'Eggplant'),
(32, 'Zucchini'),
(33, 'Brussels Sprouts'),
(34, 'Goat Cheese'),
(35, 'Walnuts'),
(36, 'Red Pepper'),
(37, 'Peppers'),
(38, 'Ciabatta Bread'),
(39, 'Chives'),
(40, 'Carrots'),
(41, 'Pasta'),
(42, 'Quinoa'),
(43, 'Lentils'),
(44, 'Chorizo'),
(45, 'Sausage'),
(46, 'Chicken Breast'),
(47, 'Cheddar Cheese'),
(48, 'Mango'),
(49, 'Pineapple'),
(50, 'Sweet Potato'),
(51, 'Mashed Potatoes'),
(52, 'Brioche Bread'),
(53, 'Pancetta'),
(54, 'Cabbage'),
(55, 'Asparagus'),
(56, 'Crispy Onions'),
(57, 'Potatoes'),
(58, 'Mustard'),
(59, 'Sour Cream'),
(60, 'Hummus'),
(61, 'Chia Seeds'),
(62, 'Tomato Sauce'),
(63, 'Balsamic Reduction'),
(64, 'Tahini'),
(65, 'Black Beans'),
(66, 'Crispy Tofu'),
(67, 'Vinegar'),
(68, 'Lemon'),
(69, 'Parmesan Cheese'),
(70, 'Pine Nuts'),
(71, 'Garlic'),
(72, 'Soy Sauce'),
(73, 'Cashews'),
(74, 'Rice Noodles'),
(75, 'Bulgur Wheat');

-- --------------------------------------------------------

--
-- Table structure for table `likedingredients`
--

CREATE TABLE `likedingredients` (
  `Customer_ID` int(11) NOT NULL,
  `Ingredient_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likedingredients`
--

INSERT INTO `likedingredients` (`Customer_ID`, `Ingredient_ID`) VALUES
(1, 12),
(1, 20),
(1, 28),
(1, 32),
(1, 47),
(2, 4),
(2, 10),
(2, 15),
(2, 25),
(2, 33),
(3, 6),
(3, 11),
(3, 22),
(3, 42),
(3, 57),
(4, 8),
(4, 13),
(4, 23),
(4, 34),
(4, 65),
(5, 7),
(5, 9),
(5, 21),
(5, 35),
(5, 55);

-- --------------------------------------------------------

--
-- Table structure for table `likedrestaurants`
--

CREATE TABLE `likedrestaurants` (
  `Customer_ID` int(11) NOT NULL,
  `Restaurant_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likedrestaurants`
--

INSERT INTO `likedrestaurants` (`Customer_ID`, `Restaurant_ID`) VALUES
(1, 4),
(2, 1),
(3, 2),
(4, 3),
(5, 4);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `Menu_ID` int(11) NOT NULL,
  `Restaurant_ID` int(11) DEFAULT NULL,
  `Menu_Name` varchar(100) NOT NULL,
  `Category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`Menu_ID`, `Restaurant_ID`, `Menu_Name`, `Category`) VALUES
(1, 1, 'Starters', 'Appetizer'),
(2, 1, 'Burgers with Fresh Hand-Cut Fries', 'Main Course'),
(3, 1, 'Entrées', 'Main Course'),
(4, 1, 'Woodstone Pizzas', 'Main Course'),
(5, 1, 'Salads', 'Side Dish'),
(6, 1, 'Sandwiches & Pitas', 'Main Course'),
(7, 1, 'Wraps & Tacos', 'Main Course'),
(8, 1, 'Brunch For Lunch', 'Brunch'),
(9, 1, 'Vegan For All', 'Vegan'),
(10, 2, 'Dinner', 'Main Course'),
(11, 2, 'Lunch', 'Main Course'),
(12, 2, 'Brunch', 'Main Course'),
(13, 3, 'Dining', 'Main Course'),
(14, 4, 'SAVORY MENU', 'Main Course'),
(15, 5, 'Food Menu', 'Main Course'),
(16, 6, 'Main Dishes', 'Main Course');

-- --------------------------------------------------------

--
-- Table structure for table `ordereddishes`
--

CREATE TABLE `ordereddishes` (
  `Dish_ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordereddishes`
--

INSERT INTO `ordereddishes` (`Dish_ID`, `Order_ID`, `Quantity`) VALUES
(1, 1, 2),
(7, 3, 1),
(14, 4, 5),
(20, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `Order_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `orderdate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`Order_ID`, `Customer_ID`, `orderdate`) VALUES
(1, 2, '2024-11-11'),
(2, 1, '2024-11-11'),
(3, 3, '2024-11-12'),
(4, 2, '2024-11-11');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `Restaurant_ID` int(11) DEFAULT NULL,
  `Customer_ID` int(11) DEFAULT NULL,
  `Resurvation_Date` date DEFAULT NULL,
  `Resurvation_Time` time DEFAULT NULL,
  `tables_reserved` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `Restaurant_ID`, `Customer_ID`, `Resurvation_Date`, `Resurvation_Time`, `tables_reserved`) VALUES
(16, 1, 1, '2024-11-14', '18:00:00', 1),
(17, 1, 2, '2024-11-14', '18:00:00', 1),
(18, 1, 3, '2024-11-14', '18:00:00', 1),
(19, 1, 4, '2024-11-14', '18:00:00', 1),
(20, 1, 5, '2024-11-14', '18:00:00', 1),
(22, 1, 1, '2024-11-14', '19:00:00', 1),
(25, 1, 6, '2024-11-14', '18:00:00', 1),
(26, 3, 3, '2024-11-17', '10:00:00', 1),
(27, 2, 3, '2024-11-17', '11:00:00', 1),
(28, 2, 1, '2024-11-19', '16:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE `restaurant` (
  `Restaurant_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Longitude` float DEFAULT NULL,
  `Latitude` float DEFAULT NULL,
  `Rating` decimal(2,1) DEFAULT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `total_tables` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant`
--

INSERT INTO `restaurant` (`Restaurant_ID`, `Name`, `Longitude`, `Latitude`, `Rating`, `open_time`, `close_time`, `total_tables`) VALUES
(1, 'Local Table', 29.9403, -95.2301, 4.4, '08:00:00', '21:00:00', 6),
(2, 'State of Grace', 29.7429, -94.4253, 4.4, '07:30:00', '19:00:00', 6),
(3, 'Benihana', 29.7798, -95.561, 3.0, '09:00:00', '20:00:00', 6),
(4, 'Max Brenner', 40.7345, -73.9916, 3.7, '10:00:00', '17:00:00', 6),
(5, 'Dallas - Uptown', 32.7942, -96.8048, 4.1, '07:00:00', '16:00:00', 6),
(6, 'Hibachi Steak\r\n', 29.9927, -95.4916, 3.8, '10:00:00', '20:00:00', 6);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `Review_ID` int(11) NOT NULL,
  `Customer_ID` int(11) DEFAULT NULL,
  `Restaurant_ID` int(11) DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`Review_ID`, `Customer_ID`, `Restaurant_ID`, `Rating`, `Date`, `Time`) VALUES
(1, 1, 2, 1, '2024-11-19', '22:18:14'),
(2, 1, 2, 3, '2025-03-13', '16:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `seatingtables`
--

CREATE TABLE `seatingtables` (
  `Table_Num` int(11) NOT NULL,
  `Restaurant_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seatingtables`
--

INSERT INTO `seatingtables` (`Table_Num`, `Restaurant_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(5, 5),
(5, 6),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(6, 5),
(6, 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`Customer_ID`);

--
-- Indexes for table `dish`
--
ALTER TABLE `dish`
  ADD PRIMARY KEY (`Dish_ID`),
  ADD KEY `Menu_ID` (`Menu_ID`);

--
-- Indexes for table `dishingredients`
--
ALTER TABLE `dishingredients`
  ADD PRIMARY KEY (`Dish_ID`,`Ingredient_ID`),
  ADD KEY `Ingredient_ID` (`Ingredient_ID`);

--
-- Indexes for table `favoritemeal`
--
ALTER TABLE `favoritemeal`
  ADD PRIMARY KEY (`Customer_ID`,`Dish_ID`),
  ADD KEY `Dish_ID` (`Dish_ID`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`Ingredient_ID`);

--
-- Indexes for table `likedingredients`
--
ALTER TABLE `likedingredients`
  ADD PRIMARY KEY (`Customer_ID`,`Ingredient_ID`),
  ADD KEY `Ingredient_ID` (`Ingredient_ID`);

--
-- Indexes for table `likedrestaurants`
--
ALTER TABLE `likedrestaurants`
  ADD PRIMARY KEY (`Customer_ID`,`Restaurant_ID`),
  ADD KEY `Restaurant_ID` (`Restaurant_ID`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`Menu_ID`),
  ADD KEY `Restaurant_ID` (`Restaurant_ID`);

--
-- Indexes for table `ordereddishes`
--
ALTER TABLE `ordereddishes`
  ADD PRIMARY KEY (`Dish_ID`,`Order_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`Order_ID`,`Customer_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `Restaurant_ID` (`Restaurant_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`);

--
-- Indexes for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`Restaurant_ID`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`Review_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Restaurant_ID` (`Restaurant_ID`);

--
-- Indexes for table `seatingtables`
--
ALTER TABLE `seatingtables`
  ADD PRIMARY KEY (`Table_Num`,`Restaurant_ID`),
  ADD KEY `Restaurant_ID` (`Restaurant_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `Review_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dish`
--
ALTER TABLE `dish`
  ADD CONSTRAINT `dish_ibfk_1` FOREIGN KEY (`Menu_ID`) REFERENCES `menu` (`Menu_ID`) ON DELETE CASCADE;

--
-- Constraints for table `dishingredients`
--
ALTER TABLE `dishingredients`
  ADD CONSTRAINT `dishingredients_ibfk_1` FOREIGN KEY (`Dish_ID`) REFERENCES `dish` (`Dish_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dishingredients_ibfk_2` FOREIGN KEY (`Ingredient_ID`) REFERENCES `ingredients` (`Ingredient_ID`);

--
-- Constraints for table `favoritemeal`
--
ALTER TABLE `favoritemeal`
  ADD CONSTRAINT `favoritemeal_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customers` (`Customer_ID`),
  ADD CONSTRAINT `favoritemeal_ibfk_2` FOREIGN KEY (`Dish_ID`) REFERENCES `dish` (`Dish_ID`);

--
-- Constraints for table `likedingredients`
--
ALTER TABLE `likedingredients`
  ADD CONSTRAINT `likedingredients_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customers` (`Customer_ID`),
  ADD CONSTRAINT `likedingredients_ibfk_2` FOREIGN KEY (`Ingredient_ID`) REFERENCES `ingredients` (`Ingredient_ID`);

--
-- Constraints for table `likedrestaurants`
--
ALTER TABLE `likedrestaurants`
  ADD CONSTRAINT `likedrestaurants_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customers` (`Customer_ID`),
  ADD CONSTRAINT `likedrestaurants_ibfk_2` FOREIGN KEY (`Restaurant_ID`) REFERENCES `restaurant` (`Restaurant_ID`);

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`Restaurant_ID`) REFERENCES `restaurant` (`Restaurant_ID`) ON DELETE CASCADE;

--
-- Constraints for table `ordereddishes`
--
ALTER TABLE `ordereddishes`
  ADD CONSTRAINT `ordereddishes_ibfk_1` FOREIGN KEY (`Dish_ID`) REFERENCES `dish` (`Dish_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `ordereddishes_ibfk_2` FOREIGN KEY (`Order_ID`) REFERENCES `orders` (`Order_ID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customers` (`Customer_ID`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`Restaurant_ID`) REFERENCES `restaurant` (`Restaurant_ID`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`Customer_ID`) REFERENCES `customers` (`Customer_ID`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customers` (`Customer_ID`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`Restaurant_ID`) REFERENCES `restaurant` (`Restaurant_ID`);

--
-- Constraints for table `seatingtables`
--
ALTER TABLE `seatingtables`
  ADD CONSTRAINT `seatingtables_ibfk_1` FOREIGN KEY (`Restaurant_ID`) REFERENCES `restaurant` (`Restaurant_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
