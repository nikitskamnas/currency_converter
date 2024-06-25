<?php
session_start();

// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL password
$dbname = "currency_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch machine's IPv4 address using ipconfig (Windows specific)
function getMachineIPAddress() {
    // Execute shell command to get IP address
    $ip_output = shell_exec("ipconfig | findstr IPv4");
    preg_match("/IPv4 Address[.\s]*:\s*([0-9.]*)/", $ip_output, $matches);
    return $matches[1] ?? '';
}

// Handle user login
if (isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Fetch machine's IP address
    $machine_ip = getMachineIPAddress();

    // Query to fetch user data including IP restrictions
    $sql = "SELECT id, username, password, ip_restrictions FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $db_username = $row['username'];
        $db_password = $row['password'];
        $ip_restrictions_json = $row['ip_restrictions'];

        // Verify the password using password_verify()
        if (password_verify($password, $db_password)) {
            // Check IP restriction against machine's IP address
            if (checkIPRestrictions($machine_ip, $ip_restrictions_json)) {
                $_SESSION['username'] = $username;
                
                // Redirect to dashboard or homepage
                header('Location: index.php');
                exit;
            } else {
                $error_message = "Access denied due to IP restrictions.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }

    $stmt->close();
}

// Function to check if machine's IP matches any subnet in the database
function checkIPRestrictions($machine_ip, $ip_restrictions_json) {
    if (empty($ip_restrictions_json)) {
        return true; // No restrictions, allow access
    }

    // Decode the IP restrictions JSON
    $ip_restrictions = json_decode($ip_restrictions_json, true);

    // Check if decoding failed, treat as a single IP string
    if (json_last_error() !== JSON_ERROR_NONE) {
        $ip_restrictions = [$ip_restrictions_json];
    }

    if (is_array($ip_restrictions)) {
        foreach ($ip_restrictions as $ip) {
            if ($ip == $machine_ip) {
                return true; // IP matches, allow access
            }
        }
    }
    return false; // No matching IP found, deny access
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" type="text/css" href="/currency_converter/css/style.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center mt-5">
      <div class="col-md-6">
        <div class="card login-container">
        <h4 class="login-title">Login</h4>
        <?php
    if (isset($error_message)) {
        echo "<p style='color: red;'>{$error_message}</p>";
    }
    ?>
          <div class="card-body">
            <form action="" method="POST">
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
              </div>
              <button type="submit" name="login" class="btn btn-login btn-block">submit</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS and dependencies (jQuery, Popper.js) -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
