# Database Xampp Installation

## **1. Installing XAMPP**

XAMPP is a software package that includes **Apache**, **MariaDB (MySQL)**, **PHP**, and **Perl**, making it easy to set up a local development environment.

### **Download and Install XAMPP**

1. Go to the official [XAMPP download page](https://www.apachefriends.org/download.html).
2. Choose the version for your operating system (**Windows, macOS, or Linux**) and download it.
3. Run the installer and follow the setup instructions. When prompted, ensure that **Apache** and **MySQL** are selected for installation.
4. Once installed, open the **XAMPP Control Panel** (found in `C:/xampp/` on Windows or `Applications/XAMPP/` on macOS).
5. Start both **Apache** and **MySQL** by clicking the "Start" buttons in the Control Panel.

---

## **2. Importing the Database**

After installing XAMPP, you need to import the `.sql` file to set up the database. You can do this in two ways:

### **Method 1: Using phpMyAdmin (Localhost)**

1. Open your web browser and go to http://localhost/phpmyadmin.
2. Click on the **Databases** tab at the top.
3. Under **Create database**, enter a name for your database (e.g., `my_database`) and click **Create**.
4. Click on the newly created database from the left sidebar.
5. Go to the **Import** tab.
6. Click **Choose File**, then select your `.sql` file from your computer.
7. Click **Go** to import the database.
8. Once completed, you should see a success message confirming the tables have been imported.

---

### **Method 2: Using the Terminal (Command Line)**

#### **For Windows (Command Prompt)**

1. Open **Command Prompt (cmd)**.
    
2. Navigate to the MySQL bin directory by running:
    
    `cd C:\xampp\mysql\bin`
    
3. Start MySQL by entering:
    
    `mysql -u root -p`
    
    - If you set a password during installation, enter it when prompted.
    - If no password was set, just press **Enter**.
4. Create a new database (replace `my_database` with your desired name):
    
    `CREATE DATABASE my_database;`
    
5. Exit MySQL:
    
    `EXIT;`
    
6. Import the `.sql` file by running:
    
    `mysql -u root -p querybyte < path_to_file/querybyte.sql`
    
    _(Replace `path_to_file` with the actual location of your SQL file.)_
    

---

#### **For macOS/Linux (Terminal)**

1. Open **Terminal**.
2. Navigate to the MySQL directory:
    
    `cd /Applications/XAMPP/xamppfiles/bin/`
    
3. Start MySQL:
    
    `./mysql -u root -p`
    
4. Follow steps **4-6** from the Windows instructions above.

---

## **3. Verifying the Database**

After importing the database, confirm that the tables were created correctly:

1. Open **phpMyAdmin** at http://localhost/phpmyadmin.
2. Click on your database and check if the tables are listed.
3. Alternatively, in the terminal, run:
    
    `SHOW TABLES;`
    
    This should list all the tables in your database.

---

## **4. Running XAMPP and Connecting to the Database**

- Make sure **Apache** and **MySQL** are running in XAMPP before testing your website or application.
- If your application requires a database connection, ensure it is configured with the correct database name, username (`root`), and password (if set).

Now your XAMPP setup is ready, and your database has been successfully imported!

---

## **5. Running the webpage**

- After the Xampp Apach and MySQL is running type this command to access the webpage
```
http://localhost/querybite/login.php
```

---