# currency_converter
PHP Assignment Currency Converter 
# Currency Converter Application

This project is a web-based currency converter application that allows users to convert currencies based on the latest exchange rates. The application includes features for user authentication, IP restriction management, and admin functionalities for managing users.

## Installation

1. **Clone the repository:**
    ```sh
    git clone https://github.com/nikitskamnas/currency_converter.git
    cd currency-converter
    ```

2. **Set up the database:**
    - Create a MySQL database named `currency_db`.
    - Import the database schema (create necessary tables).
    - Adjust the database credentials in the PHP files as needed.

3. **Download dependencies:**
    - This project uses Bootstrap for styling, which is included via CDN in the HTML files.
    - No additional PHP dependencies are required.

## Usage

2. **Access the application in your web browser:**
    ```sh
    http://localhost/currency_converter
    ```

3. **Login**
    - Navigate to the login page (`login.php`).
    - Enter the username and password.
    - Testing Credentials-
    Username - User_1
    Password - 123

3. **Login**
    - Navigate to the login page (`admin.php`).
    - As an admin, you can add or remove users using the `admin.php` page.
    - Can Add remove IP addresses.

4. **Convert currencies:**
    - After logging in, navigate to `index.php`.
    - Select the input currency and enter the amount.
    - Click `Convert` to view the converted amounts in other currencies.
    - Click `Update Rate` to fetch the latest currency rates from the API.

## File Descriptions

- **`login.php`**: Handles user login and authentication with IP restriction management.
- **`index.php`**: Main application page for currency conversion.
- **`update_currencies.php`**: Fetches and updates the latest currency rates from an external API.
- **`admin.php`**: Admin page for adding and removing users.
- **`log.php`**: Contains the logging function used to log messages to `log.txt`.
- **`log.txt`**: Log file for recording events and errors.


