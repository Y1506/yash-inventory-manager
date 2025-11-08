# Inventory Manager and Tracker

This is a web-based inventory management and tracker application built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

*   **Dashboard:** At-a-glance view of key inventory metrics.
*   **Item Management:** Add, edit, and archive items.
*   **Stock Movements:** Check-in, checkout, and adjustments.
*   **Checkout System:** A cart-style checkout process.
*   **Transaction History:** View a log of all inventory transactions.
*   **User Management:** Manage users with different roles (admin, clerk, viewer).
*   **Supplier Management:** Keep track of suppliers.
*   **Reporting:** Generate reports for inventory valuation, stock movements, and low-stock items.
*   **CSV Export:** Export reports to CSV format.
*   **Role-Based Access Control:** Different user roles have different permissions.

## How to Set Up and Run the Project using XAMPP

1.  **Install XAMPP:** Download and install XAMPP from the [official website](https://www.apachefriends.org/index.html).

2.  **Start Apache and MySQL:** Open the XAMPP Control Panel and start the Apache and MySQL services.

3.  **Place Project in `htdocs`:** Place the project folder in the `htdocs` directory of your XAMPP installation (usually `c:\xampp\htdocs`).

4.  **Create the Database:**
    *   Open your web browser and go to `http://localhost/phpmyadmin`.
    *   Click on the "Databases" tab.
    *   Enter `inventory_manager` in the "Create database" field and click "Create".

5.  **Import the Database Schema:**
    *   Select the `inventory_manager` database from the left-hand menu.
    *   Click on the "Import" tab.
    *   Click "Choose File" and select the `database.sql` file from the project directory.
    *   Click "Go" to import the schema.

6.  **Configure the Database Connection:**
    *   In the `php` directory of the project, rename `config-sample.php` to `config.php`.
    *   Open `config.php` and update the database credentials if you have changed the default MySQL username and password.

7.  **Access the Application:**
    *   Open your web browser and go to `http://localhost/inventory-manager-and-tracker` (or the name of your project folder).

## How to Use the Application

*   **Admin Login:**
    *   **Email:** admin@example.com
    *   **Password:** password
*   **Clerk Login:**
    *   **Email:** clerk@example.com
    *   **Password:** password
*   **Viewer Login:**
    *   **Email:** viewer@example.com
    *   **Password:** password

**Note:** You will need to add some sample data to the database to see the application in action. You can start by adding some categories and suppliers, then add some items.

**Note:** A `dummy.txt` file is created to satisfy a tool requirement. It can be safely ignored.