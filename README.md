# Expired Products Finder 📦

A comprehensive PHP web application to track product expiry dates with full CRUD operations, secure user authentication, and advanced search functionality.

## Features ✨

- **🔐 Secure User Authentication**: Registration, login, and session management with CSRF protection
- **📦 Product Management**: Complete CRUD operations (Create, Read, Update, Delete)
- **⏰ Expiry Tracking**: Visual indicators with color-coded expiry status
- **🔍 Advanced Search**: Search products by name, type, location, or category
- **📊 Multiple Views**: Dashboard, expired products view, and all products view
- **📱 Responsive Design**: Mobile-friendly interface with collapsible navigation
- **🎨 Modern UI**: Clean, intuitive interface with accordion-style product display
- **🔒 Security Features**: Password hashing, SQL injection prevention, XSS protection
- **⚡ Performance Optimized**: Database indexes and efficient queries
- **🌍 Environment Support**: Configurable via .env file

## Requirements 🛠️

- XAMPP (or similar PHP/MySQL environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- MySQLi extension
- Modern web browser with JavaScript enabled

## Setup Instructions 🚀

### 1. Database Setup

1. Start XAMPP and make sure Apache and MySQL are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the `database_setup.sql` file OR run it manually:
   ```sql
   -- You can also just run the database_setup.sql file in phpMyAdmin
   ```

### 2. Environment Configuration

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Update the `.env` file with your database credentials:
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=expired_products_db
   ```

### 3. Project Setup

1. Place all files in your web server directory:
   ```
   /var/www/html/Expired_Products_Finder/  (Linux)
   C:\xampp\htdocs\Expired_Products_Finder\  (Windows XAMPP)
   ```

2. Ensure proper file permissions (Linux):
   ```bash
   chmod 755 Expired_Products_Finder/
   chmod 644 Expired_Products_Finder/*.php
   chmod 644 Expired_Products_Finder/*.css
   chmod 644 Expired_Products_Finder/*.js
   ```

### 4. Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/Expired_Products_Finder/`
3. You'll be redirected to the signup page
4. Create an account and start tracking your products!

## Usage 📖

### Adding Products

1. Login to your account
2. Click the "Add Product" button
3. Fill in the product details:
   - **Product Name**: e.g., "Bread", "Medicine", "Milk"
   - **Product Type**: bottle, pouch, packet, tablet, box, jar, tube, other
   - **Location**: Where you stored it (e.g., "fridge", "kitchen", "pantry")
   - **Quantity**: How many items
   - **Category**: food, medicine, cosmetics, cleaning, other
   - **Manufacturing Date**: When it was made
   - **Expiry Date**: When it expires

### Managing Products

- **View**: All products displayed in accordion format on the dashboard
- **Edit**: Click the "Edit" button on any product to modify its details
- **Delete**: Click the "Delete" button (with confirmation dialog)
- **Search**: Use the search bar to find specific products by any field
- **Filter**: View expired products separately via the "Expired Products" link

### Navigation

- **Dashboard**: Main view with all products and expiry status
- **Expired Products**: Dedicated view for expired and expiring items
- **All Products**: Complete list view with search functionality
- **Logout**: Securely end your session

### Expiry Status Colors

- 🟢 **Green**: Fresh (more than 7 days until expiry)
- 🟠 **Orange**: Expiring this week (3-7 days)
- 🟡 **Yellow**: Expiring soon (1-3 days)
- 🔴 **Red**: Already expired

## File Structure 📁

```
Expired_Products_Finder/
├── config.php              # Database connection and core functions
├── index.php               # Main dashboard with CRUD operations
├── login.php               # User login page with CSRF protection
├── signup.php              # User registration page
├── logout.php              # Secure logout script
├── expired.php             # Dedicated expired products view
├── all_products.php        # Complete products list view
├── style.css               # CSS styling with responsive design
├── script.js               # JavaScript functionality and interactions
├── database_setup.sql      # Database schema and indexes
├── .env.example            # Environment configuration template
├── .gitignore              # Git ignore file
├── LICENSE                 # MIT License
├── IMPROVEMENTS.md         # Development improvements log
└── README.md               # This documentation file
```

## Database Schema 🗄️

### Users Table
- `id` - Primary key (auto-increment)
- `username` - Unique username (varchar, 50 chars)
- `email` - Unique email address (varchar, 100 chars)
- `password` - Hashed password using PHP's password_hash()
- `created_at` - Account creation timestamp

### Products Table
- `id` - Primary key (auto-increment)
- `user_id` - Foreign key to users table
- `product_name` - Name of the product (varchar, 100 chars)
- `product_type` - Type (bottle, pouch, packet, tablet, box, jar, tube, other)
- `location` - Storage location (varchar, 100 chars)
- `quantity` - Number of items (int)
- `category` - Product category (food, medicine, cosmetics, cleaning, other)
- `manufacturing_date` - Date of manufacture (DATE)
- `expiry_date` - Expiry date (DATE)
- `created_at` - Entry creation timestamp
- `updated_at` - Last update timestamp

### Database Indexes
- Index on `users.email` for fast login queries
- Index on `products.user_id` for user-specific product retrieval
- Index on `products.expiry_date` for expired product queries
- Composite index on `products.user_id` and `products.expiry_date` for optimized filtering

## Security Features 🔒

- **Password Security**: Hashing using PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: All database queries use prepared statements
- **XSS Protection**: Output sanitization with `htmlspecialchars()`
- **CSRF Protection**: CSRF tokens on all forms for cross-site request forgery prevention
- **Session Security**: Secure session configuration with HTTP-only cookies
- **Input Validation**: Comprehensive validation for all user inputs
- **Environment Variables**: Sensitive configuration stored in `.env` file
- **Error Handling**: Secure error messages that don't leak sensitive information

## Performance Optimizations ⚡

- **Database Indexes**: Strategic indexes on frequently queried columns
- **Query Optimization**: Efficient queries with minimal database round trips
- **Responsive Design**: Optimized CSS and JavaScript for fast loading
- **Session Management**: Efficient session handling without unnecessary overhead

## Customization 🎨

### Adding New Product Types
Edit the `index.php` file and add new options to the product type select dropdown in the form section.

### Adding New Categories
Edit the `index.php` file and add new options to the category select dropdown in the form section.

### Changing Colors
Modify the `style.css` file to change the color scheme and appearance. Key classes to look for:
- `.status-fresh` - Green color for fresh products
- `.status-warning` - Orange color for products expiring soon
- `.status-expired` - Red color for expired products

### Customizing Expiry Thresholds
Update the expiry calculation logic in `config.php` or relevant PHP files to change the warning periods.

## Troubleshooting 🔧

### Database Connection Issues
- Make sure XAMPP is running (Apache and MySQL services)
- Check that MySQL service is active in XAMPP control panel
- Verify database credentials in `.env` file
- Ensure the database `expired_products_db` exists
- Check MySQL error logs in XAMPP for detailed errors

### Page Not Found (404 Error)
- Ensure files are in the correct web directory (`htdocs/Expired_Products_Finder/`)
- Check that Apache is running on port 80 (or your configured port)
- Verify `.htaccess` settings if using custom URL rewriting
- Check file permissions (should be readable by web server)

### Session Issues
- Make sure cookies are enabled in your browser
- Check PHP session settings in `php.ini` (session.save_path)
- Ensure proper file permissions on session storage directory
- Try clearing browser cookies and cache

### Environment Configuration Issues
- Verify `.env` file exists and is readable
- Check that `.env` file is in the project root directory
- Ensure proper file permissions on `.env` (should not be publicly accessible)
- Validate that all required environment variables are set

### Performance Issues
- Check if database indexes are properly created
- Monitor MySQL slow query log
- Ensure proper caching headers are set
- Consider optimizing large product lists with pagination

## Development & Contributing 🛠️

### Code Style
- Follow PSR-12 coding standards for PHP
- Use meaningful variable names and comments
- Maintain consistent indentation and formatting
- Keep functions small and focused

### Testing
- Test all CRUD operations thoroughly
- Verify security measures (CSRF, XSS, SQL injection)
- Test responsive design on different screen sizes
- Validate form inputs and error handling

### Deployment Notes
- Always use HTTPS in production
- Set `APP_ENV=production` in `.env` for production
- Ensure proper file permissions on sensitive files
- Regularly update dependencies and security patches

## Future Enhancements 🚀

- **📧 Email Notifications**: Automated alerts for expiring products
- **📱 Mobile App**: Native mobile application
- **📷 Barcode Scanning**: Product addition via barcode/QR code
- **📸 Product Images**: Upload and display product photos
- **📊 Analytics Dashboard**: Usage statistics and insights
- **🔄 Data Import/Export**: CSV, Excel, PDF export functionality
- **👥 Multi-User Support**: Shared inventories and collaboration
- **🏷️ Advanced Categories**: Custom categories with icons
- **📍 Location Tracking**: GPS-based storage location tracking
- **🔔 Push Notifications**: Browser and mobile push notifications
- **📈 Inventory Reports**: Detailed reports and analytics
- **🌐 API Integration**: Third-party service integrations

## License 📄

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments �

- Built with pure PHP, MySQL, JavaScript, and CSS
- No external frameworks or dependencies required
- Responsive design works across all modern browsers
- Security-first approach with modern best practices

## Support 💬

For issues, questions, or contributions:
1. Check the [IMPROVEMENTS.md](IMPROVEMENTS.md) for recent updates
2. Review the troubleshooting section above
3. Create an issue in the project repository
4. Check existing issues for solutions

---

**Enjoy using the Expired Products Finder! 🎉**
