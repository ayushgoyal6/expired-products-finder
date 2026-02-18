<?php
// All Products Page
// Displays all products with search and filter functionality

require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$errors = [];
$search_term = '';

// Handle search
if (isset($_GET['search'])) {
    $search_term = clean_input($_GET['search']);
    
    // Server-side validation
    if (strlen($search_term) < 2) {
        $errors[] = "Search term must be at least 2 characters long";
        $search_term = ''; // Clear invalid search
    } elseif (strlen($search_term) > 50) {
        $errors[] = "Search term is too long (maximum 50 characters)";
        $search_term = ''; // Clear invalid search
    }
}

// Fetch all products
$sql = "SELECT * FROM products WHERE user_id = ?";
$params = [$_SESSION['user_id']];
$types = "i";

if (!empty($search_term)) {
    $sql .= " AND (product_name LIKE ? OR product_type LIKE ? OR location LIKE ? OR category LIKE ?)";
    $search_param = "%$search_term%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

$sql .= " ORDER BY category, expiry_date ASC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Database error: Failed to prepare products query");
}
$stmt->bind_param($types, ...$params);
if ($stmt->execute() === false) {
    die("Database error: Failed to execute products query");
}
$products = $stmt->get_result();
$stmt->close();

// Get total products count (for display purposes only)
$total_products_display = $products->num_rows;

// Group products by category
$grouped_products = [];
$category_colors = [
    'food' => '#28a745',
    'medicine' => '#dc3545', 
    'cosmetics' => '#6f42c1',
    'cleaning' => '#17a2b8',
    'other' => '#6c757d'
];

while ($product = $products->fetch_assoc()) {
    $category = $product['category'];
    if (!isset($grouped_products[$category])) {
        $grouped_products[$category] = [];
    }
    $grouped_products[$category][] = $product;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Product Manager</title>
    <link rel="stylesheet" href="style.css">
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
        
        // Search validation function
        function validateSearchForm(event) {
            const searchInput = event.target.querySelector('input[name="search"]');
            const searchTerm = searchInput.value.trim();
            
            // Remove existing error messages
            const existingError = searchInput.parentNode.querySelector('.search-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Remove error styling
            searchInput.classList.remove('error');
            
            // Validation: minimum 2 characters, maximum 50 characters
            if (searchTerm.length < 2) {
                event.preventDefault();
                searchInput.classList.add('error');
                
                // Create and show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'search-error';
                errorDiv.textContent = 'Please enter at least 2 characters to search';
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.marginTop = '0.25rem';
                
                searchInput.parentNode.appendChild(errorDiv);
                searchInput.focus();
                return false;
            }
            
            if (searchTerm.length > 50) {
                event.preventDefault();
                searchInput.classList.add('error');
                
                // Create and show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'search-error';
                errorDiv.textContent = 'Search term is too long (maximum 50 characters)';
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.marginTop = '0.25rem';
                
                searchInput.parentNode.appendChild(errorDiv);
                searchInput.focus();
                return false;
            }
            
            return true;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Add validation to search forms
            const searchForms = document.querySelectorAll('.search-form');
            searchForms.forEach(form => {
                form.addEventListener('submit', validateSearchForm);
                
                // Clear error on input
                const searchInput = form.querySelector('input[name="search"]');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const existingError = this.parentNode.querySelector('.search-error');
                        if (existingError) {
                            existingError.remove();
                        }
                        this.classList.remove('error');
                    });
                }
            });
        });
        
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
            <h1>📦 All Products - Product Manager</h1>
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
                <a href="index.php">➕ Add Product</a>
                <a href="all_products.php" class="active">📦 All Products</a>
                <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
            </div>
        </header>
        
        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMenu()"></div>
        
        <!-- Navigation -->
        <nav class="main-nav">
            <a href="expired.php" class="nav-link">⚠️ Expired Products</a>
            <a href="index.php" class="nav-link">➕ Add Product</a>
            <a href="all_products.php" class="nav-link active">📦 All Products</a>
        </nav>
        
        <?php echo display_messages(); ?>
        
        <!-- Search Bar -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="Search products by name, type, location, or category..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input" maxlength="50" required>
                <button type="submit" class="btn btn-search">🔍 Search</button>
                <?php if (!empty($search_term)): ?>
                    <a href="all_products.php" class="btn btn-secondary">✖ Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products Display -->
        <div class="products-section">
            <h2>📦 Your Products</h2>
            <p class="product-count">
                Showing <?php echo $total_products_display; ?> total products
                <?php if (!empty($search_term)): ?>
                    for "<?php echo htmlspecialchars($search_term); ?>"
                <?php endif; ?>
            </p>
            <?php if (!empty($grouped_products)): ?>
                <div class="category-accordion">
                    <?php foreach ($grouped_products as $category => $category_products): ?>
                        <div class="category-accordion-item">
                            <div class="category-accordion-header" style="border-left: 5px solid <?php echo $category_colors[$category] ?? '#6c757d'; ?>;">
                                <div class="category-info">
                                    <h3><?php echo ucfirst($category); ?> (<?php echo count($category_products); ?> items)</h3>
                                </div>
                                <div class="category-accordion-toggle">▼</div>
                            </div>
                            <div class="category-accordion-content">
                                <?php foreach ($category_products as $product): ?>
                                    <?php 
                                    $expiry_info = get_expiry_status($product['expiry_date']);
                                    $is_expired = strtotime($product['expiry_date']) < strtotime(date('Y-m-d'));
                                    ?>
                                    <div class="product-item <?php echo $is_expired ? 'expired-item' : ''; ?>">
                                        <div class="product-accordion-header" style="border-left: 3px solid <?php echo $expiry_info['color']; ?>;">
                                            <div class="product-summary">
                                                <h4><?php echo $product['product_name']; ?></h4>
                                                <span class="expiry-status" style="color: <?php echo $expiry_info['color']; ?>;">
                                                    <?php echo $expiry_info['status']; ?> 
                                                    <?php if ($expiry_info['days'] >= 0): ?>
                                                        (<?php echo $expiry_info['days']; ?> days left)
                                                    <?php else: ?>
                                                        (<?php echo abs($expiry_info['days']); ?> days ago)
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <div class="product-accordion-toggle">▼</div>
                                        </div>
                                        <div class="product-accordion-content">
                                            <div class="product-details">
                                                <p><strong>Type:</strong> <?php echo $product['product_type']; ?></p>
                                                <p><strong>Location:</strong> <?php echo $product['location']; ?></p>
                                                <p><strong>Quantity:</strong> <?php echo $product['quantity']; ?></p>
                                                <p><strong>Manufacturing Date:</strong> <?php echo date('d M Y', strtotime($product['manufacturing_date'])); ?></p>
                                                <p><strong>Expiry Date:</strong> <?php echo date('d M Y', strtotime($product['expiry_date'])); ?></p>
                                            </div>
                                            <div class="product-actions">
                                                <a href="index.php?edit=<?php echo $product['id']; ?>" class="btn btn-secondary">✏️ Edit</a>
                                                <form method="POST" action="index.php" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">🗑️ Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <p>No products found. <?php if (!empty($search_term)): ?>Try a different search term or <?php endif; ?><a href="index.php">add your first product</a>! 🎉</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
