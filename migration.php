<?php

class DatabaseMigration
{
    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Fetching form values
            $server = $_POST['servername'];
            $user = $_POST['username'];
            $passwd = $_POST['password'];
            $dbname = $_POST['databasename'];

            // Setting up connection
            $connection = new mysqli($server, $user, $passwd);

            // Checking for connection errors
            if ($connection->connect_error) {
                die("Connection failed: " . $connection->connect_error);
            }

            // Creating database
            $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
            if ($connection->query($sql) === TRUE) {
                echo "Database created successfully<br>";

                // Selecting the database
                $connection->select_db($dbname);

                // SQL statements for table creation
                $migrationSql = "
                    CREATE TABLE IF NOT EXISTS `User` (
                        UserID INT AUTO_INCREMENT PRIMARY KEY,
                        Username VARCHAR(255),
                        Password VARCHAR(255),
                        Email VARCHAR(255)
                    );

                    CREATE TABLE IF NOT EXISTS `Address` (
                        AddressID INT AUTO_INCREMENT PRIMARY KEY,
                        UserID INT,
                        Street VARCHAR(255),
                        City VARCHAR(255),
                        State VARCHAR(255),
                        ZipCode VARCHAR(10),
                        FOREIGN KEY (UserID) REFERENCES `User`(UserID)
                    );

                    CREATE TABLE IF NOT EXISTS `Category` (
                        CategoryID INT AUTO_INCREMENT PRIMARY KEY,
                        CategoryName VARCHAR(255)
                    );

                    CREATE TABLE IF NOT EXISTS `Product` (
                        ProductID INT AUTO_INCREMENT PRIMARY KEY,
                        ProductName VARCHAR(255),
                        Description TEXT,
                        Price DECIMAL(10, 2),
                        CategoryID INT,
                        FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID)
                    );

                    CREATE TABLE IF NOT EXISTS `Orders` (
                        OrderID INT AUTO_INCREMENT PRIMARY KEY,
                        UserID INT,
                        OrderDate DATE,
                        TotalAmount DECIMAL(10, 2),
                        FOREIGN KEY (UserID) REFERENCES `User`(UserID)
                    );

                    CREATE TABLE IF NOT EXISTS `OrderDetail` (
                        OrderDetailID INT AUTO_INCREMENT PRIMARY KEY,
                        OrderID INT,
                        ProductID INT,
                        Quantity INT,
                        Price DECIMAL(10, 2),
                        FOREIGN KEY (OrderID) REFERENCES Orders(OrderID),
                        FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
                    );

                    CREATE TABLE IF NOT EXISTS `Payment` (
                        PaymentID INT AUTO_INCREMENT PRIMARY KEY,
                        OrderID INT,
                        PaymentDate DATE,
                        Amount DECIMAL(10, 2),
                        FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
                    );
                ";

                // Execute the migration SQL statements
                if ($connection->multi_query($migrationSql) === TRUE) {
                    echo "Migration successful!";
                } else {
                    echo "Migration failed: " . $connection->error;
                }

            } else {
                echo "Error creating database: " . $connection->error;
            }

            $connection->close();
        }
    }

    public function render()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
        </head>

        <body>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <label for="servername">Enter Server Name</label>
                <input type="text" id="servername" name="servername" value="localhost">
                <label for="username">Enter User Name</label>
                <input type="text" id="username" name="username" value="root">
                <label for="password">Enter Password</label>
                <input type="password" id="password" name="password" value="">
                <label for="databasename">Enter DB Name</label>
                <input type="text" id="databasename" name="databasename"
                    value="<?php echo htmlspecialchars($_POST['databasename'] ?? ''); ?>">
                <button type="submit" name="migrate">Perform Migration</button>
            </form>
        </body>

        </html>
        <?php
    }
}

$databaseMigration = new DatabaseMigration();
$databaseMigration->render();
