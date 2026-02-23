<?php
// Login Page
// Handles user authentication

require_once 'config.php';

// Initialize CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        handle_error("Invalid request");
    } elseif (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    } elseif ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt']) < 300) {
        handle_error("Too many login attempts. Please try again in 5 minutes.");
    } else {
        $username = clean_input($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            handle_error("Both username and password are required");
        } elseif (strlen($username) < 3) {
            handle_error("Username must be at least 3 characters long");
        } elseif (strlen($password) < 6 || strlen($password) > 11) {
            handle_error("Password must be between 6 and 11 characters long");
        } elseif (!preg_match('/^[a-zA-Z0-9_@.-]+$/', $username)) {
            handle_error("Username contains invalid characters");
        } else {
            $sql = "SELECT id, username, password FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['login_attempts'] = 0;
                    handle_success("Login successful", 'expired.php');
                } else {
                    handle_error("Invalid password");
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt'] = time();
                }
            } else {
                handle_error("User not found");
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Expired Products Finder</title>
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
                if (!validationFn(this.value)) {
                    this.classList.add('error');
                    errorDiv.textContent = errorMsg;
                    errorDiv.classList.add('show');
                } else {
                    this.classList.remove('error');
                    errorDiv.classList.remove('show');
                }
            });
            
            field.addEventListener('blur', function() {
                if (!validationFn(this.value)) {
                    this.classList.add('error');
                    errorDiv.textContent = errorMsg;
                    errorDiv.classList.add('show');
                } else {
                    this.classList.remove('error');
                    errorDiv.classList.remove('show');
                }
            });
        }

        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            let isValid = true;
            
            if (username.length < 3) {
                document.getElementById('username').classList.add('error');
                document.getElementById('username-error').textContent = 'Username must be at least 3 characters';
                document.getElementById('username-error').classList.add('show');
                isValid = false;
            }
            
            if (password.length < 6 || password.length > 11) {
                document.getElementById('password').classList.add('error');
                document.getElementById('password-error').textContent = 'Password must be between 6 and 11 characters';
                document.getElementById('password-error').classList.add('show');
                isValid = false;
            }
            
            return isValid;
        }

        document.addEventListener('DOMContentLoaded', function() {
            validateField('username', function(val) {
                return val.trim().length >= 3;
            }, 'Username must be at least 3 characters');
            
            validateField('password', function(val) {
                return val.length >= 6 && val.length <= 11;
            }, 'Password must be between 6 and 11 characters');
            
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
        });
    </script>
</head>
<body class="login-page">
    <div class="container">
        <div class="auth-form">
            <h2>Welcome Back! 👋</h2>
            <p>Login to track your product expiry dates</p>
            
            <?php echo display_messages(); ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label for="username">Username or Email:</label>
                    <input type="text" id="username" name="username" maxlength="33" required autocomplete="username" autocapitalize="none" inputmode="text" spellcheck="false" aria-label="Username or email" aria-required="true">
                    <div class="validation-error" id="username-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" maxlength="11" required autocomplete="current-password" inputmode="text" spellcheck="false" aria-label="Password" aria-required="true">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                    </div>
                    <div class="validation-error" id="password-error"></div>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <p class="switch-auth">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </p>
        </div>
    </div>
</body>
</html>
