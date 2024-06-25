<?php
require 'log.php';

// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // your MySQL password
$dbname = "currency_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    logMessage("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the URL
$url = "https://www.floatrates.com/daily/usd.json";
$json_data = file_get_contents($url);

if ($json_data === FALSE) {
    logMessage("Error fetching data from URL.");
    die("Error fetching data from URL.");
}

$rates = json_decode($json_data, true);

// Add USD to the rates list manually if it's not included
if (!isset($rates['usd'])) {
    $rates['usd'] = [
        'code' => 'USD',
        'rate' => 1.0,
        'date' => date('Y-m-d H:i:s')
    ];
}

if ($rates) {
    // Clear the existing data
    $conn->query("TRUNCATE TABLE currencies");

    // Prepare an SQL statement for inserting data
    $stmt = $conn->prepare("INSERT INTO currencies (code, rate, last_updated) VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE rate = VALUES(rate), last_updated = VALUES(last_updated)");

    foreach ($rates as $key => $rate_info) {
        $code = strtoupper($rate_info['code']); // Convert to uppercase
        $rate = $rate_info['rate'];
        $last_updated = date('Y-m-d H:i:s', strtotime($rate_info['date']));

        // Bind parameters
        $stmt->bind_param("sds", $code, $rate, $last_updated);
        $stmt->execute();
    }

    $stmt->close();
    logMessage("Currency data updated successfully.");
    echo "Currency data updated successfully.";
} else {
    logMessage("Error decoding JSON data.");
    echo "Error decoding JSON data.";
}

$conn->close();

// Redirect back to index.php
header("Location: index.php");
?>
