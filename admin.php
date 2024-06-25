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

// Handle adding a user
if (isset($_POST['add_user'])) {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $new_ip = $_POST['new_ip'];

    // Check if the username already exists
    $check_sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $new_username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();

    if ($row['count'] > 0) {
        $error_message = "User '{$new_username}' already exists. Please choose a different username.";
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $insert_sql = "INSERT INTO users (username, password, ip_restrictions) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $new_username, $hashed_password, $new_ip);
        
        if ($insert_stmt->execute()) {
            $message = "New user '{$new_username}' added successfully.";
        } else {
            $message = "Error adding user: " . $conn->error;
        }
        $insert_stmt->close();
    }

    $check_stmt->close();
}

// Handle removing users
if (isset($_POST['remove_users'])) {
    $users_to_remove = $_POST['users_to_remove'];

    // Delete selected users from the database
    $deleted_users = [];
    foreach ($users_to_remove as $username) {
        $sql = "DELETE FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        
        if ($stmt->execute()) {
            $deleted_users[] = $username;
        } else {
            $message = "Error removing user '{$username}': " . $conn->error;
        }
        $stmt->close();
    }

    if (!empty($deleted_users)) {
        $message = "Users " . implode(', ', $deleted_users) . " removed successfully.";
    }
}

// Fetch all users from the database
$sql = "SELECT username FROM users";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row['username'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Page</title>
    <style>
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid;
            border-radius: 5px;
        }
        .success {
            color: green;
            border-color: green;
        }
        .error {
            color: red;
            border-color: red;
        }
    </style>
    <script>
        // Function to fade out messages after 5 seconds
        function fadeOutMessages() {
            let messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.style.display = 'none';
                    }, 1000);
                }, 5000);
            });
        }

        // Call the function when the page is loaded
        document.addEventListener('DOMContentLoaded', function() {
            fadeOutMessages();
        });
    </script>
</head>
<body>
    <h2>Admin Page</h2>
    <?php
    if (isset($message)) {
        echo "<p class='message success'>{$message}</p>";
    }
    if (isset($error_message)) {
        echo "<p class='message error'>{$error_message}</p>";
    }
    ?>
    <h3>Add New User</h3>
    <form method="post" action="">
        <label for="new_username">Username:</label>
        <input type="text" id="new_username" name="new_username" required>
        <br><br>
        <label for="new_password">Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <br><br>
        <label for="new_ip">IP Restrictions (comma-separated if multiple):</label>
        <input type="text" id="new_ip" name="new_ip" required>
        <br><br>
        <button type="submit" name="add_user">Add User</button>
    </form>

    <h3>Remove Users</h3>
    <form method="post" action="">
        <fieldset>
            <legend>Select users to remove:</legend>
            <?php foreach ($users as $user): ?>
                <label>
                    <input type="checkbox" name="users_to_remove[]" value="<?php echo $user; ?>">
                    <?php echo $user; ?>
                </label>
                <br>
            <?php endforeach; ?>
        </fieldset>
        <br>
        <button type="submit" name="remove_users">Remove Selected Users</button>
    </form>
</body>
</html>