<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // your MySQL password
$dbname = "currency_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the URL
$url = "https://www.floatrates.com/daily/usd.json";
$json_data = file_get_contents($url);
$currencies = json_decode($json_data, true);

if ($currencies) {
    // Clear the existing data
    $conn->query("TRUNCATE TABLE currencies");

    // Prepare an SQL statement for inserting data
    $stmt = $conn->prepare("INSERT INTO currencies (name, rate) VALUES (?, ?)");

    foreach ($currencies as $currency) {
        $name = $currency['name'];
        $rate = $currency['rate'];
        $stmt->bind_param("sd", $name, $rate);
        $stmt->execute();
    }
    $stmt->close();
    echo "Currency data updated successfully.";
} else {
    echo "Error fetching currency data.";
}

$conn->close();
?>
