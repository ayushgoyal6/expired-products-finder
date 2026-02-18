<?php
// Database Configuration File
// Handles MySQL connection and database setup

// Database connection settings
$servername = $_ENV['DB_HOST'] ?? 'localhost';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'expired_products_db';

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Create connection to MySQL first (without specifying database)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    // Log error instead of displaying it in production
    error_log("MySQL connection failed: " . $conn->connect_error);
    die("MySQL connection error. Please check XAMPP is running and try again later.");
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS expired_products_db";
if ($conn->query($sql) === FALSE) {
    error_log("Database creation failed: " . $conn->error);
    die("Database creation error. Please try again later.");
}

// Select the database
$conn->select_db($dbname);

// Check if we can access the database
if ($conn->error) {
    error_log("Database selection failed: " . $conn->error);
    die("Database selection error. Please try again later.");
}

// Create tables if not exists
create_tables_if_not_exists($conn);

// Set charset to utf8mb4 for better emoji support 😊
$conn->set_charset("utf8mb4");

// Session security settings - must be set before session_start()
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', filter_var($_ENV['SESSION_HTTPONLY'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', $_ENV['SESSION_SAMESITE'] ?? 'Strict');
    
    // Only set secure cookie if HTTPS is actually enabled
    if (filter_var($_ENV['SESSION_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
        ini_set('session.cookie_secure', 1);
    }
    
    // Start session for user authentication with security settings
    session_start();
}

// Global functions
function create_tables_if_not_exists($conn) {
    // Create users table
    $users_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($users_sql) === FALSE) {
        error_log("Users table creation failed: " . $conn->error);
        die("Users table creation error. Please try again later.");
    }
    
    // Create products table
    $products_sql = "CREATE TABLE IF NOT EXISTS products (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        product_name VARCHAR(100) NOT NULL,
        product_type VARCHAR(50) NOT NULL,
        location VARCHAR(200) NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        category VARCHAR(50) NOT NULL,
        manufacturing_date DATE NOT NULL,
        expiry_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($products_sql) === FALSE) {
        error_log("Products table creation failed: " . $conn->error);
        die("Products table creation error. Please try again later.");
    }
}

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// Standardized error handling
function handle_error($message, $redirect_url = null) {
    $_SESSION['error_message'] = $message;
    if ($redirect_url) {
        redirect($redirect_url);
    }
}

function handle_success($message, $redirect_url = null) {
    $_SESSION['success_message'] = $message;
    if ($redirect_url) {
        redirect($redirect_url);
    }
}

function display_messages() {
    $output = '';
    
    if (isset($_SESSION['error_message'])) {
        $output .= '<div class="error-messages"><p>' . htmlspecialchars($_SESSION['error_message']) . '</p></div>';
        unset($_SESSION['error_message']);
    }
    
    if (isset($_SESSION['success_message'])) {
        $output .= '<div class="success-message"><p>' . htmlspecialchars($_SESSION['success_message']) . '</p></div>';
        unset($_SESSION['success_message']);
    }
    
    return $output;
}

// Calculate days until expiry
function days_until_expiry($expiry_date) {
    $today = new DateTime();
    $expiry = new DateTime($expiry_date);
    $diff = $today->diff($expiry);
    return $diff->days * ($diff->invert ? -1 : 1);
}

// Get expiry status with colors
function get_expiry_status($expiry_date) {
    $days = days_until_expiry($expiry_date);
    
    if ($days < 0) {
        return ['status' => 'Expired', 'color' => '#dc3545', 'days' => abs($days)];
    } elseif ($days <= 3) {
        return ['status' => 'Expiring Soon', 'color' => '#ffc107', 'days' => $days];
    } elseif ($days <= 7) {
        return ['status' => 'Expiring This Week', 'color' => '#fd7e14', 'days' => $days];
    } else {
        return ['status' => 'Fresh', 'color' => '#28a745', 'days' => $days];
    }
}

?>
