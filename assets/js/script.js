// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(n => {
            n.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    showFieldError(field, 'This field is required');
                } else {
                    field.classList.remove('error');
                    hideFieldError(field);
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // Real-time form validation
    document.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });

        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
});

// Field validation function
function validateField(field) {
    let isValid = true;
    const value = field.value.trim();

    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        showFieldError(field, 'This field is required');
    }
    // Email validation
    else if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            showFieldError(field, 'Please enter a valid email address');
        }
    }
    // Phone validation
    else if (field.type === 'tel' && value) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            showFieldError(field, 'Please enter a valid phone number');
        }
    }
    // Password validation
    else if (field.type === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            showFieldError(field, 'Password must be at least 6 characters long');
        }
    }

    if (isValid) {
        field.classList.remove('error');
        hideFieldError(field);
    }

    return isValid;
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('error');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }

    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#dc2626';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// Hide field error
function hideFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Date picker initialization (for browsers that don't support HTML5 date input)
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Add fallback for older browsers
        if (input.type !== 'date') {
            // You can implement a custom date picker here or use a library like flatpickr
            console.log('Date input not supported, consider adding a polyfill');
        }
    });
}

// Search functionality
function initSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const searchTarget = this.getAttribute('data-search-target');
            const items = document.querySelectorAll(searchTarget);

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}

// Modal functionality
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 1rem;
        max-width: 400px;
    `;
    
    notification.querySelector('button').style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// File upload handling
function handleFileUpload(input, allowedTypes = []) {
    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Check file type
        if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
            showNotification('Invalid file type. Please select a valid file.', 'error');
            this.value = '';
            return;
        }

        // Check file size (10MB limit)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            showNotification('File size too large. Maximum size is 10MB.', 'error');
            this.value = '';
            return;
        }

        // Show file name
        const fileName = file.name;
        const fileInfo = document.createElement('div');
        fileInfo.textContent = `Selected: ${fileName}`;
        fileInfo.style.marginTop = '0.5rem';
        fileInfo.style.fontSize = '0.875rem';
        fileInfo.style.color = '#6b7280';

        // Remove existing file info
        const existingInfo = this.parentNode.querySelector('.file-info');
        if (existingInfo) {
            existingInfo.remove();
        }

        fileInfo.className = 'file-info';
        this.parentNode.appendChild(fileInfo);
    });
}

// Initialize file uploads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    initDatePickers();
    
    // Initialize search functionality
    initSearch();
    
    // Initialize file uploads
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        const allowedTypes = input.getAttribute('accept')?.split(',') || [];
        handleFileUpload(input, allowedTypes);
    });
});

// AJAX form submission
function submitForm(form, successCallback, errorCallback) {
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: form.method || 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) successCallback(data);
            showNotification(data.message || 'Operation completed successfully', 'success');
        } else {
            if (errorCallback) errorCallback(data);
            showNotification(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (errorCallback) errorCallback(error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Table sorting functionality
function initTableSorting() {
    const sortableTables = document.querySelectorAll('.sortable-table');
    
    sortableTables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                const isAscending = header.classList.contains('sort-asc');
                
                // Remove sort classes from all headers
                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                
                // Add appropriate sort class
                header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
                
                // Sort rows
                rows.sort((a, b) => {
                    const aVal = a.querySelector(`td:nth-child(${getColumnIndex(header) + 1})`).textContent.trim();
                    const bVal = b.querySelector(`td:nth-child(${getColumnIndex(header) + 1})`).textContent.trim();
                    
                    if (isAscending) {
                        return bVal.localeCompare(aVal);
                    } else {
                        return aVal.localeCompare(bVal);
                    }
                });
                
                // Reappend sorted rows
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
}

function getColumnIndex(element) {
    return Array.from(element.parentNode.children).indexOf(element);
}

// Initialize table sorting when DOM is loaded
document.addEventListener('DOMContentLoaded', initTableSorting);