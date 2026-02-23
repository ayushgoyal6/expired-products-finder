<?php
// Main Product Management Page
// Handles CRUD operations for products

require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$errors = [];
$success = '';
$search_term = '';


// Handle CRUD operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $product_name = clean_input($_POST['product_name']);
                $product_type = clean_input($_POST['product_type']);
                $location = clean_input($_POST['location']);
                $quantity = clean_input($_POST['quantity']);
                $category = clean_input($_POST['category']);
                $manufacturing_date = clean_input($_POST['manufacturing_date']);
                $expiry_date = clean_input($_POST['expiry_date']);
                
                // Validation
                if (empty($product_name) || empty($product_type) || empty($location) || 
                    empty($quantity) || empty($category) || empty($manufacturing_date) || empty($expiry_date)) {
                    $errors[] = "All fields are required";
                } elseif (strlen($product_name) < 2 || strlen($product_name) > 100) {
                    $errors[] = "Product name must be between 2 and 100 characters";
                } elseif (strlen($location) < 2 || strlen($location) > 200) {
                    $errors[] = "Location must be between 2 and 200 characters";
                } elseif (!is_numeric($quantity) || $quantity < 1 || $quantity > 9999) {
                    $errors[] = "Quantity must be between 1 and 9999";
                } elseif (strtotime($expiry_date) <= strtotime($manufacturing_date)) {
                    $errors[] = "Expiry date must be after manufacturing date";
                } elseif (strtotime($manufacturing_date) > strtotime(date('Y-m-d'))) {
                    $errors[] = "Manufacturing date cannot be in the future";
                }
                
                if (empty($errors)) {
                    $sql = "INSERT INTO products (user_id, product_name, product_type, location, quantity, category, manufacturing_date, expiry_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt === false) {
                        $errors[] = "Database error: Failed to prepare statement";
                    } else {
                        $stmt->bind_param("isssisss", $_SESSION['user_id'], $product_name, $product_type, $location, $quantity, $category, $manufacturing_date, $expiry_date);
                        
                        if ($stmt->execute()) {
                            handle_success("Product added successfully! 🎉");
                            redirect('index.php');
                            } else {
                            $errors[] = "Failed to add product: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
                break;
                
            case 'update':
                $product_id = clean_input($_POST['product_id']);
                $product_name = clean_input($_POST['product_name']);
                $product_type = clean_input($_POST['product_type']);
                $location = clean_input($_POST['location']);
                $quantity = clean_input($_POST['quantity']);
                $category = clean_input($_POST['category']);
                $manufacturing_date = clean_input($_POST['manufacturing_date']);
                $expiry_date = clean_input($_POST['expiry_date']);
                
                // Validation (same as add)
                if (empty($product_name) || empty($product_type) || empty($location) || 
                    empty($quantity) || empty($category) || empty($manufacturing_date) || empty($expiry_date)) {
                    $errors[] = "All fields are required";
                } elseif (strlen($product_name) < 2 || strlen($product_name) > 100) {
                    $errors[] = "Product name must be between 2 and 100 characters";
                } elseif (strlen($location) < 2 || strlen($location) > 200) {
                    $errors[] = "Location must be between 2 and 200 characters";
                } elseif (!is_numeric($quantity) || $quantity < 1 || $quantity > 9999) {
                    $errors[] = "Quantity must be between 1 and 9999";
                } elseif (strtotime($expiry_date) <= strtotime($manufacturing_date)) {
                    $errors[] = "Expiry date must be after manufacturing date";
                } elseif (strtotime($manufacturing_date) > strtotime(date('Y-m-d'))) {
                    $errors[] = "Manufacturing date cannot be in future";
                }
                
                if (empty($errors)) {
                    $sql = "UPDATE products SET product_name = ?, product_type = ?, location = ?, quantity = ?, 
                            category = ?, manufacturing_date = ?, expiry_date = ? WHERE id = ? AND user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssissssi", $product_name, $product_type, $location, $quantity, $category, $manufacturing_date, $expiry_date, $product_id, $_SESSION['user_id']);
                    
                    if ($stmt->execute()) {
                        handle_success("Product updated successfully! ✏️");
                        redirect('index.php');
                    } else {
                        $errors[] = "Failed to update product";
                    }
                    $stmt->close();
                }
                break;
                
            case 'delete':
                $product_id = clean_input($_POST['product_id']);
                $sql = "DELETE FROM products WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    handle_success("Product deleted successfully! 🗑️");
                    redirect('index.php');
                } else {
                    $errors[] = "Failed to delete product";
                }
                $stmt->close();
                break;
        }
    }
}

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = clean_input($_GET['edit']);
    $sql = "SELECT * FROM products WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $edit_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_product = $result->fetch_assoc();
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateField(fieldId, validationFn, errorMsg) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.getElementById(fieldId + '-error');
            
            if (field && errorDiv) {
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
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            validateField('product_name', function(val) {
                return val.length >= 2 && val.length <= 33;
            }, 'Product name must be between 2 and 33 characters');
            
            validateField('location', function(val) {
                return val.length >= 2 && val.length <= 33;
            }, 'Location must be between 2 and 33 characters');
            
            validateField('quantity', function(val) {
                const num = parseInt(val);
                return !isNaN(num) && num >= 1 && num <= 9999;
            }, 'Quantity must be between 1 and 9999');
        });
    </script>
    <script>
        function toggleMenu() {
            const hamburger = document.querySelector('.hamburger-menu');
            const mobileMenu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            hamburger.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Prevent body scroll when menu is open
            if (mobileMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const hamburger = document.querySelector('.hamburger-menu');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (!hamburger.contains(event.target) && !mobileMenu.contains(event.target)) {
                hamburger.classList.remove('active');
                mobileMenu.classList.remove('active');
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>➕ Add Product</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
                <a href="logout.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
            <div class="hamburger-menu" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="mobile-menu" id="mobileMenu">
                <a href="expired.php">⚠️ Expired Products</a>
                <a href="index.php" class="active">➕ Add Product</a>
                <a href="all_products.php">📦 All Products</a>
                <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
            </div>
        </header>
        
        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMenu()"></div>
        
        <!-- Navigation -->
        <nav class="main-nav">
            <a href="expired.php" class="nav-link">⚠️ Expired Products</a>
            <a href="index.php" class="nav-link active">➕ Add Product</a>
            <a href="all_products.php" class="nav-link">📦 All Products</a>
        </nav>
        
        <?php echo display_messages(); ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Add/Edit Product Form -->
            <div class="form-section">
                <h2><?php echo $edit_product ? '✏️ Edit Product' : '➕ Add New Product'; ?></h2>
                    <form method="POST" action="" id="productForm">
                        <input type="hidden" name="action" value="<?php echo $edit_product ? 'update' : 'add'; ?>">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                        <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="product_name">Product Name:</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo $edit_product['product_name'] ?? ''; ?>" maxlength="33" required autocomplete="off" autocapitalize="words" inputmode="text" spellcheck="false" aria-label="Product name" aria-required="true">
                        <div class="validation-error" id="product_name-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_type">Product Type:</label>
                        <select id="product_type" name="product_type" required>
                            <option value="">Select Type</option>
                            <option value="bottle" <?php echo ($edit_product['product_type'] ?? '') == 'bottle' ? 'selected' : ''; ?>>Bottle</option>
                            <option value="pouch" <?php echo ($edit_product['product_type'] ?? '') == 'pouch' ? 'selected' : ''; ?>>Pouch</option>
                            <option value="packet" <?php echo ($edit_product['product_type'] ?? '') == 'packet' ? 'selected' : ''; ?>>Packet</option>
                            <option value="tablet" <?php echo ($edit_product['product_type'] ?? '') == 'tablet' ? 'selected' : ''; ?>>Tablet</option>
                            <option value="box" <?php echo ($edit_product['product_type'] ?? '') == 'box' ? 'selected' : ''; ?>>Box</option>
                            <option value="jar" <?php echo ($edit_product['product_type'] ?? '') == 'jar' ? 'selected' : ''; ?>>Jar</option>
                            <option value="tube" <?php echo ($edit_product['product_type'] ?? '') == 'tube' ? 'selected' : ''; ?>>Tube</option>
                            <option value="other" <?php echo ($edit_product['product_type'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" id="location" name="location" placeholder="e.g., kitchen, fridge, mandir waala kamra" value="<?php echo $edit_product['location'] ?? ''; ?>" maxlength="33" required autocomplete="off" autocapitalize="sentences" inputmode="text" spellcheck="false">
                        <div class="validation-error" id="location-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="9999" value="<?php echo $edit_product['quantity'] ?? '1'; ?>" required inputmode="numeric" pattern="[0-9]*">
                        <div class="validation-error" id="quantity-error"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="food" <?php echo ($edit_product['category'] ?? '') == 'food' ? 'selected' : ''; ?>>Food</option>
                            <option value="medicine" <?php echo ($edit_product['category'] ?? '') == 'medicine' ? 'selected' : ''; ?>>Medicine</option>
                            <option value="cosmetics" <?php echo ($edit_product['category'] ?? '') == 'cosmetics' ? 'selected' : ''; ?>>Cosmetics</option>
                            <option value="cleaning" <?php echo ($edit_product['category'] ?? '') == 'cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                            <option value="other" <?php echo ($edit_product['category'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="manufacturing_date">Manufacturing Date:</label>
                        <input type="date" id="manufacturing_date" name="manufacturing_date" value="<?php echo $edit_product['manufacturing_date'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date:</label>
                        <input type="date" id="expiry_date" name="expiry_date" value="<?php echo $edit_product['expiry_date'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn"><?php echo $edit_product ? 'Update Product' : 'Add Product'; ?></button>
                    <?php if ($edit_product): ?>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
                    </form>
                </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
