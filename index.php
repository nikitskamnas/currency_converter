<?php
session_start();
require 'log.php';

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

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

// Fetch all currencies
$currencies = [];
$result = $conn->query("SELECT code, rate FROM currencies");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $currencies[$row['code']] = $row['rate'];
    }
}

// Ensure USD is included
if (!isset($currencies['USD'])) {
    $currencies['USD'] = 1.0;
}

$conn->close();

// Set default values for form inputs
$selected_currency = isset($_POST['input_currency']) ? $_POST['input_currency'] : '';
$entered_amount = isset($_POST['amount']) ? $_POST['amount'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Currency Converter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Currency Converter</h1>
        <form method="post" action="" class="mt-4">
            <div class="form-group">
                <label for="input_currency">Select Input Currency:</label>
                <select name="input_currency" id="input_currency" class="form-control">
                    <?php
                    foreach ($currencies as $code => $rate) {
                        $selected = ($code == $selected_currency) ? 'selected' : '';
                        echo "<option value=\"$code\" $selected>$code</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Enter Amount:</label>
                <input type="text" name="amount" id="amount" class="form-control" pattern="[0-9]+(\.[0-9]{1,4})?" title="Please enter a valid float number (up to 4 decimal places)" value="<?php echo htmlspecialchars($entered_amount); ?>" required>
            </div>
            <button type="submit" name="convert" class="btn btn-primary">Convert</button>
            <button type="submit" name="update_rate" class="btn btn-secondary" formaction="update_currencies.php">Update Rate</button>
        </form>

        <?php
        if (isset($_POST['convert'])) {
            $input_currency = $_POST['input_currency'];
            $amount = $_POST['amount'];

            // Validate amount as float
            if (!is_numeric($amount)) {
                echo "<p class='text-danger mt-3'>Please enter a valid float number.</p>";
            } else {
                $amount = floatval($amount); // Convert to float if it's a valid numeric string

                // Continue with currency conversion logic
                echo "<h2 class='mt-4'>Converted Amounts</h2>";
                echo "<table class='table table-bordered mt-3'><thead class='thead-dark'><tr>";

                // Display table headers
                foreach ($currencies as $code => $rate) {
                    echo "<th>$code</th>";
                }

                echo "</tr></thead><tbody><tr>";

                // Display converted amounts with approximation to 3 decimal places
                foreach ($currencies as $code => $rate) {
                    if ($input_currency == 'USD') {
                        $converted_amount = number_format($amount * $rate, 3);
                    } elseif ($code == 'USD') {
                        $converted_amount = number_format($amount / $currencies[$input_currency], 3);
                    } else {
                        $converted_amount = number_format(($amount * $rate) / $currencies[$input_currency], 3);
                    }
                    echo "<td>$converted_amount</td>";
                }

                echo "</tr></tbody></table>";
            }
        }
        ?>

        <!-- Logout Button -->
        <form method="post" action="login.php" class="mt-3">
            <button type="submit" name="logout" class="btn btn-danger">Logout</button>
        </form>
    </div>
</body>
</html>
