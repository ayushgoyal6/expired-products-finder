<?php
// Expired Products Page
// Displays expired products with search and filter functionality

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

// Fetch expired and near-expiry products with single query
$sql = "SELECT *, 
               CASE 
                   WHEN expiry_date < CURDATE() THEN 'expired'
                   WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'near_expiry'
               END as status_type
        FROM products 
        WHERE user_id = ? AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$params = [$_SESSION['user_id']];
$types = "i";

if (!empty($search_term)) {
    $sql .= " AND (product_name LIKE ? OR product_type LIKE ? OR location LIKE ? OR category LIKE ?)";
    $search_param = "%$search_term%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

$sql .= " ORDER BY expiry_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// Count expired and near-expiry products from results
$expired_count = 0;
$near_expiry_count = 0;
$all_products = [];

while ($product = $products->fetch_assoc()) {
    $all_products[] = $product;
    if ($product['status_type'] === 'expired') {
        $expired_count++;
    } else {
        $near_expiry_count++;
    }
}

$stmt->close();

// Reset result pointer for display
$products = $all_products;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expired Products</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleMobileMenu() {
            const hamburger = document.querySelector('.hamburger-menu');
            const mobileMenu = document.getElementById('mobileMenu');
            
            hamburger.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        }

        function showUserInfo() {
            toggleMobileMenu();
            alert('Welcome, <?php echo $_SESSION['username']; ?>!');
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
        
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>⚠️ Expired Products</h1>
            <div class="hamburger-menu" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="user-info">
                <a href="logout.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
            
            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobileMenu">
                <a href="expired.php">⚠️ Expired Products</a>
                <a href="index.php">➕ Add Product</a>
                <a href="all_products.php">📦 All Products</a>
                <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
            </div>
        </header>
        
        
        <!-- Navigation -->
        <nav class="main-nav">
            <a href="expired.php" class="nav-link active">⚠️ Expired Products</a>
            <a href="index.php" class="nav-link">➕ Add Product</a>
            <a href="all_products.php" class="nav-link">📦 All Products</a>
        </nav>
        
        <?php echo display_messages(); ?>
        
        <!-- Search Bar -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="Search expired products by name, type, location, or category..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input" maxlength="50" required>
                <button type="submit" class="btn btn-search">🔍 Search</button>
                <?php if (!empty($search_term)): ?>
                    <a href="expired.php" class="btn btn-secondary">✖ Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card expired">
                <h3>🚨 Expired</h3>
                <p><?php echo $expired_count; ?> products</p>
            </div>
            <div class="summary-card near-expiry">
                <h3>⏰ Expiring Soon</h3>
                <p><?php echo $near_expiry_count; ?> products</p>
            </div>
            <div class="summary-card total">
                <h3>📊 Total Alert Items</h3>
                <p><?php echo $expired_count + $near_expiry_count; ?> products</p>
            </div>
        </div>
        
        <!-- Products Display -->
        <div class="products-section">
            <h2>📦 Products Requiring Attention</h2>
            <?php if (!empty($search_term)): ?>
                <p class="product-count">
                    Showing <?php echo count($products); ?> products 
                    for "<?php echo htmlspecialchars($search_term); ?>"
                </p>
            <?php endif; ?>
            <?php if (!empty($products)): ?>
                <div class="accordion">
                    <?php foreach ($products as $product): ?>
                        <?php 
                        $expiry_info = get_expiry_status($product['expiry_date']);
                        $is_expired = strtotime($product['expiry_date']) < strtotime(date('Y-m-d'));
                        ?>
                        <div class="accordion-item <?php echo $is_expired ? 'expired-item' : 'near-expiry-item'; ?>">
                            <div class="accordion-header" style="border-left: 5px solid <?php echo $expiry_info['color']; ?>;">
                                <div class="product-summary">
                                    <h3><?php echo $product['product_name']; ?></h3>
                                    <span class="expiry-status" style="color: <?php echo $expiry_info['color']; ?>;">
                                        <?php echo $expiry_info['status']; ?> 
                                        <?php if ($expiry_info['days'] >= 0): ?>
                                            (<?php echo $expiry_info['days']; ?> days left)
                                        <?php else: ?>
                                            (<?php echo abs($expiry_info['days']); ?> days ago)
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="accordion-toggle">▼</div>
                            </div>
                            <div class="accordion-content">
                                <div class="product-details">
                                    <p><strong>Type:</strong> <?php echo $product['product_type']; ?></p>
                                    <p><strong>Location:</strong> <?php echo $product['location']; ?></p>
                                    <p><strong>Quantity:</strong> <?php echo $product['quantity']; ?></p>
                                    <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
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
            <?php else: ?>
                <div class="no-products">
                    <?php if (!empty($search_term)): ?>
                        <p>No expired or near-expiry products found for "<?php echo htmlspecialchars($search_term); ?>". Try a different search term!</p>
                        <a href="expired.php" class="btn">✖ Clear Search</a>
                    <?php else: ?>
                        <p>🎉 Great! No expired or near-expiry products found.</p>
                        <a href="index.php" class="btn">➕ Add New Product</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
