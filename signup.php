<?php
// Signup Page
// Handles user registration

require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        handle_error("Invalid request");
    } else {
        $username = clean_input($_POST['username']);
        $email = clean_input($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username)) {
        handle_error("Username is required");
    } elseif (strlen($username) < 3) {
        handle_error("Username must be at least 3 characters");
    } elseif (strlen($username) > 33) {
        handle_error("Username must be 33 characters or less");
    } elseif (!preg_match('/^[a-zA-Z]/', $username)) {
        handle_error("Username must start with a letter");
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        handle_error("Username can only contain letters, numbers, underscores, and hyphens");
    }
    
    if (empty($email)) {
        handle_error("Email is required");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        handle_error("Invalid email format");
    }
    
    if (empty($password)) {
        handle_error("Password is required");
    } elseif (strlen($password) < 6) {
        handle_error("Password must be at least 6 characters");
    }
    
    if ($password !== $confirm_password) {
        handle_error("Passwords do not match");
    }
    
    // Check if username or email already exists
    if (empty($_SESSION['error_message'])) {
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            handle_error("Username or email already exists");
        }
        $stmt->close();
    }
    
    // Insert new user
    if (empty($_SESSION['error_message'])) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            handle_success("Account created successfully! You can now login.", 'login.php');
        } else {
            handle_error("Something went wrong. Please try again.");
        }
        $stmt->close();
    }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Expired Products Finder</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling;
            if (field.type === 'password') {
                field.type = 'text';
                toggle.textContent = '🙈';
            } else {
                field.type = 'password';
                toggle.textContent = '👁️';
            }
        }

        function validateField(fieldId, validationFn, errorMsg) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.getElementById(fieldId + '-error');
            
            field.addEventListener('input', function() {
                let isValid;
                let message;
                
                if (typeof errorMsg === 'function') {
                    // Dynamic error message based on validation
                    if (validationFn(this.value)) {
                        isValid = true;
                        message = '';
                    } else {
                        isValid = false;
                        message = errorMsg(this.value);
                    }
                } else {
                    // Static error message
                    isValid = validationFn(this.value);
                    message = errorMsg;
                }
                
                if (!isValid) {
                    this.classList.add('error');
                    errorDiv.textContent = message;
                    errorDiv.classList.add('show');
                } else {
                    this.classList.remove('error');
                    errorDiv.classList.remove('show');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            validateField('username', function(val) {
                if (val.length < 3) return false;
                if (val.length > 33) return false;
                if (!/^[a-zA-Z]/.test(val)) return false; // Must start with letter
                if (!/^[a-zA-Z0-9_-]+$/.test(val)) return false; // Only letters, numbers, underscore, hyphen
                return true;
            }, function(val) {
                if (val.length < 3) return 'Username must be at least 3 characters';
                if (val.length > 33) return 'Username must be 33 characters or less';
                if (!/^[a-zA-Z]/.test(val)) return 'Username must start with a letter';
                if (!/^[a-zA-Z0-9_-]+$/.test(val)) return 'Username can only contain letters, numbers, underscores, and hyphens';
                return '';
            });
            
            validateField('email', function(val) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) && val.length <= 33;
            }, 'Please enter a valid email address (max 33 characters)');
            
            validateField('password', function(val) {
                return val.length >= 6 && val.length <= 11;
            }, 'Password must be between 6 and 11 characters');
            
            validateField('confirm_password', function(val) {
                const password = document.getElementById('password').value;
                return val === password && val.length > 0;
            }, 'Passwords do not match');
        });
    </script>
</head>
<body class="signup-page">
    <div class="container">
        <div class="auth-form">
            <h2>Create Account 📝</h2>
            <p>Join us to track your product expiry dates!</p>
            
            <?php echo display_messages(); ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <p><?php echo $success; ?></p>
                    <p><a href="login.php">Click here to login</a></p>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" maxlength="33" required autocomplete="username" autocapitalize="none" inputmode="text" spellcheck="false">
                        <div class="validation-error" id="username-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" maxlength="33" required autocomplete="email" inputmode="email" spellcheck="false">
                        <div class="validation-error" id="email-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" maxlength="11" required autocomplete="new-password" inputmode="text" spellcheck="false">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                        </div>
                        <div class="validation-error" id="password-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <div class="password-container">
                            <input type="password" id="confirm_password" name="confirm_password" maxlength="11" required autocomplete="new-password" inputmode="text" spellcheck="false">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">👁️</button>
                        </div>
                        <div class="validation-error" id="confirm_password-error"></div>
                    </div>
                    
                    <button type="submit" class="btn">Sign Up</button>
                </form>
                
                <p class="switch-auth">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
