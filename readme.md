# QueryBite Project

## Project Description

**QueryBite** is an implementation of SQL queries and procedures integrated with a webpage to test our understanding and skills with databases. The project was originally submitted on 18/11/2024, but further updates have been made to enhance the website's functionality and improve navigation.

### High-Level Overview

At its core, **QueryBite** provides an intuitive interface for customers to explore restaurants, make reservations, view menus, and leave reviews. By registering and logging into the system, customers can easily search for nearby restaurants, view available tables, and make real-time reservations. They can also rate their dining experience and share their feedback, helping others make informed decisions.

For restaurant managers, **QueryBite** offers powerful tools for managing restaurant operations. The **manager dashboard** enables restaurant managers to view and manage reservations, track orders made on specific dates, and see all customer orders with detailed information such as dish names, prices, and quantities. This helps managers optimize resource allocation, track inventory, and provide a better customer experience.

### Advantages of QueryBite

- **Enhanced User Experience**: Customers can effortlessly find and reserve tables at their favorite restaurants, explore available dishes, and check real-time availability.
- **Customer Insights**: The system allows customers to leave reviews, helping restaurants improve their offerings while also providing new customers with valuable feedback on dining experiences.
- **Streamlined Operations for Managers**: The platform offers an easy-to-navigate dashboard for restaurant managers to manage reservations, track orders, and generate reports on restaurant performance, making operations more efficient.
- **Dynamic Recommendations**: The system can recommend dishes and restaurants based on a customer’s preferences and location, ensuring a personalized experience for each user.
- **Improved Accessibility**: With an online interface, customers can make reservations anytime, from anywhere, making it easier for them to enjoy dining experiences without having to call restaurants directly.

---

## Installation

To get started with **QueryBite**, you need to install the database and set up the environment. Follow the instructions in the [Database Installation Guide](Database_Installation_Guide.md) to configure the database and get the system up and running.

### Prerequisites

- A web server (e.g., Apache, Nginx)
- PHP 7.0 or higher
- MySQL or MariaDB
- XAMPP or similar server stack (for local development)
- Access to a MySQL database

Once you have completed the database installation, you can continue with the application setup.

---

## Execution and Usage

Once the **QueryBite** project is set up and running, users can interact with the system by navigating through different pages based on their roles—either as a **customer** or a **manager**. Here's a detailed guide on how to use the website:

### Customer Navigation

1. **Login Page** (`login.php`):
   - When a user first accesses the site, they are directed to the **login page**.
   - On this page, customers can log in using their credentials. Upon successful authentication, they are redirected to the **customer homepage**.
   - If customers are not registered, they can access the registration page to create a new account.
   - Additionally, managers can click a button to access the **manager dashboard**.

2. **Customer Homepage** (`customer_home.php`):
   - After logging in, customers land on their homepage. This page lists all available restaurants, displaying their names and other relevant details.
   - Customers can view restaurants based on their proximity or personal preferences.
   - They can click on a restaurant to view more details about the restaurant's offerings, including menu items and upcoming reservation times.

3. **Restaurant Page** (`restaurant.php`):
   - Clicking on a restaurant from the homepage takes the user to the **restaurant details page**.
   - Here, customers can view the restaurant’s menu, search for dishes by ingredient, and make reservations.
   - The page will also display recommended dishes based on the customer’s favorite ingredients and offer options to leave reviews or book a table for a future date.

4. **Reservation Page** (`makereservation.php`):
   - From the **restaurant page**, customers can choose a time and date to make a reservation.
   - The page allows them to select a reservation slot, and once confirmed, the system will create a reservation in the database for the customer at the chosen restaurant.

5. **Review Page** (`review.php`):
   - After dining, customers can leave reviews for the restaurant they visited, rating their experience. The review system allows users to provide feedback on food and service, which will be stored in the database.

6. **Profile Page** (`profile.php`):
   - Customers can view and edit their profile information, including username and password.
   - The profile page also displays the customer’s current and past reservations, allowing them to keep track of upcoming visits.

### Manager Navigation

1. **Manager Dashboard** (`manager.php`):
   - Managers access the **manager dashboard** by clicking on the “Manager” button on the **login page**.
   - Once logged in, the manager can select a restaurant from a dropdown menu populated with all available restaurants.
   - The dashboard includes options to:
     - **View Reservations**: Displays the available tables for a specific day and time at the selected restaurant.
     - **View Orders on a Specific Date**: Shows a list of orders made on a particular date, with details about the dishes ordered, quantity, and prices.
     - **View All Orders**: Displays all customer orders from the selected restaurant, including order ID, customer details, dish names, and prices.
   
---

## Technologies Used

The **QueryBite** project leverages a range of technologies to ensure smooth functionality, efficient data handling, and a responsive user experience. Below is an overview of the key technologies used:

### 1. PHP
   - **Purpose**: PHP is the core server-side language used in the **QueryBite** project. It powers the backend logic, handles database connections, processes form submissions, and dynamically generates the HTML content for the user interface.
   - **Usage**: PHP is used in every page of the project, from handling user logins and profiles (`login.php`, `profile.php`), restaurant data and reservations (`restaurant.php`, `makereservation.php`), to managing the manager dashboard (`manager.php`).
   - **Features**: The PHP scripts are responsible for tasks such as:
     - Authenticating and managing user sessions.
     - Fetching and displaying data from the MySQL database.
     - Inserting new records (e.g., customer reviews and reservations).
     - Processing form data for restaurant selection, order management, and more.

### 2. MySQL
   - **Purpose**: MySQL serves as the database management system that stores all the critical data for the **QueryBite** project, including user details, restaurant information, reservations, orders, and reviews.
   - **Usage**: The project uses MySQL for:
     - Storing customer details and credentials in the `customers` table.
     - Storing restaurant data, including names and menu items, in the `restaurant` and `menu` tables.
     - Recording customer reservations in the `reservations` table.
     - Storing customer orders and reviews in the `orders` and `reviews` tables.
   - **Features**: MySQL allows the system to efficiently store and retrieve large amounts of data, supporting complex queries for tasks like viewing reservations for a specific date, displaying restaurant information, and managing customer orders.

### 3. HTML
   - **Purpose**: HTML is used to structure the web pages and content that customers and managers interact with. It defines the layout, text, images, forms, and tables presented on the website.
   - **Usage**: HTML is used to build the frontend of every page in the **QueryBite** project, including:
     - **Forms**: Login, registration, reservation, and review submission forms.
     - **Tables**: Displaying restaurant data, reservation details, and customer orders.
     - **Content Layout**: Organizing content such as restaurant lists, available reservations, and menu items.
   - **Features**: HTML elements like `div`, `table`, `form`, `select`, and `input` are used to structure and present content in an organized and user-friendly manner.

### 4. XAMPP
   - **Purpose**: XAMPP is a software package that provides an easy-to-use development environment to run PHP and MySQL locally on a computer. It includes Apache (the web server), MySQL (the database server), and PHP.
   - **Usage**: XAMPP is used to run the **QueryBite** project locally during development. It serves as the server that processes PHP code and manages database connections, allowing the project to function as it would in a live server environment.
   - **Features**: 
     - **Apache Web Server**: Serves PHP files and provides the web interface.
     - **MySQL Database**: Hosts and manages the project's database.
     - **PHP**: Executes the backend logic and connects to the MySQL database.
---

## Current Features

The **QueryBite** project includes a variety of features designed to provide a seamless user experience for customers and managers. Below are the key features:

### 1. Customer Login
   - Customers can log in to the system using their credentials (username and password). Upon successful authentication, customers are redirected to their personalized homepage (`customer_home.php`), where they can access restaurant details and make reservations.

### 2. Restaurant Listings
   - On the customer homepage, users can view a list of available restaurants. The system recommends restaurants based on customer preferences, including their favorite meal ingredients and location.

### 3. Restaurant Details and Menu
   - When customers select a restaurant, they can view detailed information about the restaurant, including its menu, available reservation times, and dish recommendations based on the customer's preferences.

### 4. Making Reservations
   - Customers can make a reservation at a selected restaurant, choosing from available times and dates. The system ensures that the reservation is successfully added to the database.

### 5. Leaving Reviews
   - Customers can leave a rating (1 to 5) and review a restaurant based on their experience. Reviews are stored in the database, and each restaurant’s review history can be accessed by other users.

### 6. Profile Management
   - Customers can view and edit their profiles, including updating their usernames and passwords. The system also displays their upcoming reservations in their profile page.

### 7. Manager Dashboard
   - The manager dashboard allows restaurant managers to:
     - View available reservations for a specific day and time.
     - See orders made at the restaurant on a given day.
     - Access all orders from customers, including details like order ID, customer ID, dish names, and prices.

---

## Contributions

The **QueryBite** project was created by the following individuals:

Tafari Dudley, Huda Jaffara, Karon Neblett, Jacquan Curtis, Essence Murray, Avin Keith, Kai

Special thanks to all contributors for their dedication to building and enhancing the **QueryBite** project!