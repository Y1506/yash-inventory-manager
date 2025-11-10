# Inventory Manager and Tracker

This is a web-based inventory management and tracker application built with PHP, MySQL, HTML, CSS, and JavaScript. It provides a comprehensive set of features to manage inventory, track stock movements, and handle sales and reporting.

## Features

*   **Dashboard:** An overview of key inventory metrics.
*   **Item Management:** Add, edit, and manage inventory items.
*   **Stock Control:** Manage stock levels, including check-ins and adjustments.
*   **Checkout System:** A simple point-of-sale interface for selling items.
*   **Transaction History:** A complete log of all inventory transactions.
*   **User Management:** Manage users with different roles (admin, clerk, viewer).
*   **Supplier Management:** Keep track of supplier information.
*   **Reporting:** Generate reports on inventory status and sales.
*   **Role-Based Access Control:** Different user roles have different permissions, restricting access to sensitive features.

## Technologies Used

*   **Frontend:** HTML, CSS, JavaScript
*   **Backend:** PHP
*   **Database:** MySQL

## XAMPP Setup Instructions

These instructions will guide you through setting up and running the project on your local machine using XAMPP.

1.  **Install XAMPP:**
    *   Download and install XAMPP from the [official website](https://www.apachefriends.org/index.html).

2.  **Start Apache and MySQL:**
    *   Open the XAMPP Control Panel and start the **Apache** and **MySQL** services.

3.  **Place Project in `htdocs`:**
    *   Place the project folder inside the `htdocs` directory of your XAMPP installation. The `htdocs` folder is usually located at `c:\xampp\htdocs`.

4.  **Project Folder Name:**
    *   **Important:** The project folder has spaces in its name (`Inventory mangager and tracker`). This can cause "Not Found" errors in the browser. You have two options:
        *   **Option 1 (Recommended):** Rename the folder to `Inventory-manager-and-tracker`. This is the standard practice and will avoid URL issues.
        *   **Option 2:** Keep the folder name as is, but you will have to use `%20` to represent the spaces in the URL.

5.  **Create the Database:**
    *   Open your web browser and navigate to `http://localhost/phpmyadmin`.
    *   Click on the **Databases** tab.
    *   In the "Create database" field, enter `inventory_db` and click **Create**.

6.  **Import the Database Schema:**
    *   In phpMyAdmin, select the `inventory_db` database from the left-hand menu.
    *   Click on the **Import** tab.
    *   Click **Choose File** and select the `database.sql` file located in the project folder.
    *   Click **Go** at the bottom of the page to import the database schema.

7.  **Database Configuration:**
    *   The database configuration file is located at `php/config.php`. It has been pre-configured with the default XAMPP database credentials:
        *   **Server:** `localhost`
        *   **Username:** `root`
        *   **Password:** (empty)
        *   **Database Name:** `inventory_db`
    *   If you have a different MySQL password, you will need to update it in this file.

8.  **Access the Application:**
    *   Open your web browser and navigate to the appropriate URL:
        *   If you renamed the folder (Option 1): `http://localhost/Inventory-manager-and-tracker/`
        *   If you did not rename the folder (Option 2): `http://localhost/Inventory%20mangager%20and%20tracker/`

## Usage

After successfully setting up the project, you can log in with the default admin credentials:

*   **Default Admin Login:**
    *   **Email:** `admin@example.com`
    *   **Password:** `password`

You can add new users with different roles through the "Employees" section in the admin panel.