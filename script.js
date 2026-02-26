// JavaScript for Expired Products Finder
// Handles mobile navigation, accordions, form validation, and UI enhancements

// Mobile detection and touch enhancements
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

// Global toggleMenu function for mobile menu
function toggleMenu() {
    const hamburger = document.querySelector('.hamburger-menu');
    const mobileMenu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (hamburger && mobileMenu) {
        hamburger.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        
        if (overlay) {
            overlay.classList.toggle('active');
        }
        
        // Prevent body scroll when menu is open
        if (mobileMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
}

// Mobile-specific enhancements
if (isMobile || isTouchDevice) {
    // Add touch feedback classes
    document.documentElement.classList.add('touch-device');
    
    // Prevent zoom on double tap for form inputs
    document.addEventListener('touchstart', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
            e.target.style.fontSize = '16px';
        }
    });
}

// Wait for DOM to be loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Category Accordion functionality
    const categoryAccordionHeaders = document.querySelectorAll('.category-accordion-header');
    
    categoryAccordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const categoryItem = this.parentElement;
            const categoryContent = categoryItem.querySelector('.category-accordion-content');
            const categoryToggle = this.querySelector('.category-accordion-toggle');
            
            // Toggle current category accordion
            if (categoryItem.classList.contains('active')) {
                categoryItem.classList.remove('active');
                categoryContent.style.maxHeight = '0';
                categoryToggle.textContent = '▼';
            } else {
                categoryItem.classList.add('active');
                // Set max-height to scrollHeight for smooth animation, then remove limit
                categoryContent.style.maxHeight = categoryContent.scrollHeight + 'px';
                categoryToggle.textContent = '▲';
                
                // Remove max-height constraint after animation completes
                setTimeout(() => {
                    if (categoryItem.classList.contains('active')) {
                        categoryContent.style.maxHeight = 'none';
                    }
                }, 300);
            }
        });
    });
    
    // Product Accordion functionality (nested within categories)
    const productAccordionHeaders = document.querySelectorAll('.product-accordion-header');
    
    productAccordionHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent category accordion from toggling
            
            const productItem = this.parentElement;
            const productContent = productItem.querySelector('.product-accordion-content');
            const productToggle = this.querySelector('.product-accordion-toggle');
            
            // Toggle current product accordion
            if (productItem.classList.contains('active')) {
                productItem.classList.remove('active');
                productContent.style.maxHeight = '0';
                productToggle.textContent = '▼';
            } else {
                productItem.classList.add('active');
                // Set max-height to scrollHeight for smooth animation, then remove limit
                productContent.style.maxHeight = productContent.scrollHeight + 'px';
                productToggle.textContent = '▲';
                
                // Remove max-height constraint after animation completes
                setTimeout(() => {
                    if (productItem.classList.contains('active')) {
                        productContent.style.maxHeight = 'none';
                    }
                }, 300);
            }
        });
    });
    
    // Legacy accordion functionality (for other pages)
    const accordionHeaders = document.querySelectorAll('.accordion-header:not(.category-accordion-header):not(.product-accordion-header)');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const accordionItem = this.parentElement;
            const accordionContent = accordionItem.querySelector('.accordion-content');
            const toggle = this.querySelector('.accordion-toggle');
            
            // Close all other accordions
            accordionHeaders.forEach(otherHeader => {
                if (otherHeader !== header) {
                    const otherItem = otherHeader.parentElement;
                    const otherContent = otherItem.querySelector('.accordion-content');
                    const otherToggle = otherHeader.querySelector('.accordion-toggle');
                    
                    otherItem.classList.remove('active');
                    otherContent.style.maxHeight = '0';
                    otherToggle.textContent = '▼';
                }
            });
            
            // Toggle current accordion
            if (accordionItem.classList.contains('active')) {
                accordionItem.classList.remove('active');
                accordionContent.style.maxHeight = '0';
                toggle.textContent = '▼';
            } else {
                accordionItem.classList.add('active');
                accordionContent.style.maxHeight = accordionContent.scrollHeight + 'px';
                toggle.textContent = '▲';
            }
        });
    });
    
    // Form validation enhancements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Basic client-side validation
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                        
                        // Remove error styling on input
                        field.addEventListener('input', function() {
                            if (this.value.trim()) {
                                this.style.borderColor = '#28a745';
                            }
                        });
                    }
                });
                
                // Date validation for manufacturing and expiry dates
                const mfgDate = form.querySelector('#manufacturing_date');
                const expDate = form.querySelector('#expiry_date');
                
                if (mfgDate && expDate && mfgDate.value && expDate.value) {
                    if (new Date(expDate.value) <= new Date(mfgDate.value)) {
                    isValid = false;
                    expDate.style.borderColor = '#dc3545';
                    
                    // Show error message
                    if (!expDate.nextElementSibling || !expDate.nextElementSibling.classList.contains('error-text')) {
                        const errorText = document.createElement('small');
                        errorText.className = 'error-text';
                        errorText.style.color = '#dc3545';
                        errorText.textContent = 'Expiry date must be after manufacturing date';
                        expDate.parentNode.insertBefore(errorText, expDate.nextSibling);
                    }
                    
                    // Remove error message on input
                    expDate.addEventListener('input', function() {
                        const errorText = this.nextElementSibling;
                        if (errorText && errorText.classList.contains('error-text')) {
                            errorText.remove();
                        }
                        if (new Date(this.value) > new Date(mfgDate.value)) {
                            this.style.borderColor = '#28a745';
                        }
                    });
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('[style*="border-color: rgb(220, 53, 69)"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Form will submit normally
            }
        });
    });
    
    // Search functionality enhancements
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        // Add search suggestions (optional enhancement)
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            // Clear any existing error styling when user starts typing
            this.classList.remove('error');
            const existingError = this.parentNode.querySelector('.search-error');
            if (existingError) {
                existingError.remove();
            }
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    // AJAX search suggestions could be implemented here
                }, 300);
            }
        });
        
        // Clear search on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.blur();
            }
        });
        
        // Add real-time character count feedback
        searchInput.addEventListener('input', function() {
            const currentLength = this.value.trim().length;
            const maxLength = this.getAttribute('maxlength') || 50;
            
            // Remove existing character count if any
            const existingCount = this.parentNode.querySelector('.char-count');
            if (existingCount) {
                existingCount.remove();
            }
            
            // Show character count when approaching limit
            if (currentLength > maxLength * 0.8) {
                const charCount = document.createElement('span');
                charCount.className = 'char-count';
                charCount.textContent = `${currentLength}/${maxLength}`;
                charCount.style.fontSize = '0.75rem';
                charCount.style.color = currentLength >= maxLength ? '#dc3545' : '#6c757d';
                charCount.style.marginLeft = '0.5rem';
                
                this.parentNode.appendChild(charCount);
            }
        });
    }
    
    // Auto-hide success messages after 5 seconds
    const successMessages = document.querySelectorAll('.success-message');
    successMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 5000);
    });
    
    // Auto-hide error messages after 10 seconds
    const errorMessages = document.querySelectorAll('.error-messages');
    errorMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 10000);
    });
    
    // Add smooth scrolling for internal links
    const internalLinks = document.querySelectorAll('a[href^="#"]');
    internalLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add keyboard navigation for forms
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.form) {
                activeElement.form.submit();
            }
        }
        
        // Tab navigation enhancement
        if (e.key === 'Tab') {
            // Add visual indicator for focused elements
            setTimeout(() => {
                const focusedElement = document.activeElement;
                if (focusedElement && focusedElement.tagName !== 'BODY') {
                    focusedElement.style.outline = '2px solid #667eea';
                    focusedElement.style.outlineOffset = '2px';
                }
            }, 0);
        }
    });
    
    // Remove outline on mouse click (for better UX)
    document.addEventListener('mousedown', function() {
        const focusedElement = document.activeElement;
        if (focusedElement && focusedElement.tagName !== 'BODY') {
            focusedElement.style.outline = 'none';
        }
    });
    
    // Dynamic date validation
    const manufacturingDate = document.querySelector('#manufacturing_date');
    const expiryDate = document.querySelector('#expiry_date');
    
    if (manufacturingDate && expiryDate) {
        // Set max date for manufacturing date to today
        const today = new Date().toISOString().split('T')[0];
        manufacturingDate.setAttribute('max', today);
        
        // Set min date for expiry date to manufacturing date
        manufacturingDate.addEventListener('change', function() {
            expiryDate.setAttribute('min', this.value);
        });
        
        // Set max date for expiry date (reasonable future limit)
        const futureDate = new Date();
        futureDate.setFullYear(futureDate.getFullYear() + 10);
        expiryDate.setAttribute('max', futureDate.toISOString().split('T')[0]);
    }
    
    // Add loading state for buttons (but don't interfere with form submission)
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        if (button.type === 'submit') {
            // Store original text for potential use
            button.setAttribute('data-original-text', button.innerHTML);
        }
    });
    
    // Add spin animation for loading states
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    // Print functionality
    const printButton = document.createElement('button');
    printButton.className = 'btn btn-secondary print-button';
    printButton.innerHTML = '🖨️ Print Products';
    
    // Responsive positioning for print button
    function updatePrintButtonPosition() {
        if (window.innerWidth <= 768) {
            // Mobile positioning
            printButton.style.position = 'fixed';
            printButton.style.bottom = '15px';
            printButton.style.right = '15px';
            printButton.style.left = '15px';
            printButton.style.width = 'auto';
            printButton.style.maxWidth = '200px';
            printButton.style.margin = '0 auto';
            printButton.style.zIndex = '1000';
            printButton.style.fontSize = '0.9em';
            printButton.style.padding = '12px 16px';
        } else if (window.innerWidth <= 1024) {
            // Tablet positioning
            printButton.style.position = 'fixed';
            printButton.style.bottom = '20px';
            printButton.style.right = '20px';
            printButton.style.left = 'auto';
            printButton.style.width = 'auto';
            printButton.style.maxWidth = '180px';
            printButton.style.zIndex = '1000';
            printButton.style.fontSize = '0.95em';
            printButton.style.padding = '12px 20px';
        } else {
            // Desktop positioning
            printButton.style.position = 'fixed';
            printButton.style.bottom = '30px';
            printButton.style.right = '30px';
            printButton.style.left = 'auto';
            printButton.style.width = 'auto';
            printButton.style.maxWidth = '200px';
            printButton.style.zIndex = '1000';
            printButton.style.fontSize = '1em';
            printButton.style.padding = '12px 24px';
        }
    }
    
    // Initial positioning
    updatePrintButtonPosition();
    
    // Update position on window resize
    window.addEventListener('resize', updatePrintButtonPosition);
    
    printButton.addEventListener('click', function() {
        window.print();
    });
    
    // Add print button only if there are products
    const productsSection = document.querySelector('.products-section');
    if (productsSection && (document.querySelector('.accordion-item') || document.querySelector('.category-accordion-item') || document.querySelector('.product-item'))) {
        document.body.appendChild(printButton);
    }
    
    // Hide print button when printing
    const printStyle = document.createElement('style');
    printStyle.textContent = `
        @media print {
            /* Hide all non-essential elements */
            button, .btn, .print-button { display: none !important; }
            .form-section, .search-section, header, .main-nav, .mobile-menu, .hamburger-menu { display: none !important; }
            .container { 
                box-shadow: none; 
                border-radius: 0;
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
            
            /* Reset body for printing */
            body {
                background: white !important;
                color: black !important;
                font-size: 12pt;
                line-height: 1.4;
                overflow: visible;
                width: 100%;
            }
            
            /* Expand all accordion content for printing */
            .category-accordion-content,
            .product-accordion-content,
            .accordion-content {
                max-height: none !important;
                display: block !important;
                height: auto !important;
                overflow: visible !important;
            }
            
            /* Hide toggle buttons */
            .category-accordion-toggle,
            .product-accordion-toggle,
            .accordion-toggle {
                display: none !important;
            }
            
            /* Ensure proper page breaks */
            .category-accordion-item,
            .accordion-item {
                break-inside: avoid;
                page-break-inside: avoid;
                margin-bottom: 20pt;
                border: 1pt solid #ccc;
                border-radius: 5pt;
            }
            
            .product-item {
                break-inside: avoid;
                page-break-inside: avoid;
                margin-bottom: 15pt;
                border: 1pt solid #ddd;
                border-radius: 3pt;
            }
            
            /* Show all products clearly */
            .products-section {
                display: block !important;
                padding: 20pt;
            }
            
            /* Product details for printing */
            .product-details {
                display: block !important;
                margin-bottom: 10pt;
            }
            
            .product-details p {
                margin: 5pt 0;
                font-size: 11pt;
                line-height: 1.3;
            }
            
            /* Product actions for printing */
            .product-actions {
                display: none !important;
            }
            
            /* Category headers for printing */
            .category-accordion-header,
            .accordion-header {
                background: #f5f5f5 !important;
                border-bottom: 1pt solid #ccc;
                padding: 10pt !important;
            }
            
            .category-accordion-header h3,
            .product-summary h3,
            .product-summary h4 {
                color: black !important;
                font-size: 14pt !important;
                margin: 0 !important;
            }
            
            /* Expiry status for printing */
            .expiry-status {
                color: black !important;
                font-weight: bold;
                font-size: 10pt;
            }
            
            /* Summary cards for printing */
            .summary-cards {
                display: block !important;
                margin: 20pt 0;
            }
            
            .summary-card {
                display: inline-block !important;
                width: 30% !important;
                margin: 0 1.5% 15pt 1.5% !important;
                border: 1pt solid #ccc;
                padding: 10pt !important;
                text-align: center;
                vertical-align: top;
            }
            
            .summary-card h3 {
                font-size: 12pt !important;
                margin: 5pt 0;
            }
            
            .summary-card p {
                font-size: 16pt !important;
                font-weight: bold;
                margin: 5pt 0;
            }
            
            /* Product count for printing */
            .product-count {
                text-align: center;
                font-size: 11pt;
                font-style: italic;
                margin: 15pt 0;
                color: #666;
            }
            
            /* Ensure content fits page width */
            * {
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    `;
    document.head.appendChild(printStyle);
    
    
    
    // Mobile-specific enhancements
    if (isMobile || isTouchDevice) {
        // Add swipe gestures for accordion items
        let touchStartX = 0;
        let touchEndX = 0;
        
        // Define accordion headers for mobile handling
        const categoryAccordionHeaders = document.querySelectorAll('.category-accordion-header');
        const productAccordionHeaders = document.querySelectorAll('.product-accordion-header');
        const accordionHeaders = document.querySelectorAll('.accordion-header:not(.category-accordion-header):not(.product-accordion-header)');
        
        // Handle category accordion headers
        categoryAccordionHeaders.forEach(header => {
            header.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            header.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe(this);
            }, { passive: true });
        });
        
        // Handle product accordion headers
        productAccordionHeaders.forEach(header => {
            header.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            header.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe(this);
            }, { passive: true });
        });
        
        // Handle legacy accordion headers
        accordionHeaders.forEach(header => {
            header.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            header.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe(this);
            }, { passive: true });
        });
        
        function handleSwipe(header) {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                // Trigger click on swipe
                header.click();
            }
        }
        
        // Add pull-to-refresh functionality (optional)
        let pullStartY = 0;
        let isPulling = false;
        
        document.addEventListener('touchstart', function(e) {
            if (window.scrollY === 0) {
                pullStartY = e.touches[0].clientY;
                isPulling = true;
            }
        }, { passive: true });
        
        document.addEventListener('touchmove', function(e) {
            if (isPulling && window.scrollY === 0) {
                const currentY = e.touches[0].clientY;
                const pullDistance = currentY - pullStartY;
                
                if (pullDistance > 100) {
                    // Show refresh indicator
                    document.body.style.transform = `translateY(${Math.min(pullDistance * 0.3, 50)}px)`;
                }
            }
        }, { passive: true });
        
        document.addEventListener('touchend', function() {
            if (isPulling) {
                document.body.style.transform = '';
                isPulling = false;
                
                // Trigger refresh if pulled enough
                const pullDistance = parseInt(document.body.style.transform.replace(/[^\d-]/g, '')) || 0;
                if (pullDistance > 30) {
                    window.location.reload();
                }
            }
        }, { passive: true });
        
        // Optimize form inputs for mobile
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            // Add better focus handling
            input.addEventListener('focus', function() {
                // Scroll input into view on mobile
                setTimeout(() => {
                    this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            });
            
            // Add haptic feedback on mobile (if supported)
            if ('vibrate' in navigator) {
                input.addEventListener('touchstart', function() {
                    navigator.vibrate(10);
                });
            }
        });
        
        // Add better mobile menu handling
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenu) {
            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                    if (typeof toggleMenu === 'function') {
                        toggleMenu();
                    }
                }
            });
            
            // Close menu on orientation change
            window.addEventListener('orientationchange', function() {
                if (mobileMenu.classList.contains('active')) {
                    if (typeof toggleMenu === 'function') {
                        toggleMenu();
                    }
                }
            });
        }
        
        // Add viewport height fix for mobile browsers
        function setViewportHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        
        setViewportHeight();
        window.addEventListener('resize', setViewportHeight);
        window.addEventListener('orientationchange', setViewportHeight);
    }
});
