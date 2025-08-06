/**
 * Goba Hospital Management System - Main JavaScript File
 * Author: System Administrator
 * Date: 2024
 */

// Global variables
let currentUser = null;
let searchResults = [];
let activeTab = 'patient';

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Initialize login tabs
    initializeLoginTabs();
    
    // Initialize date pickers
    initializeDatePickers();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize file upload
    initializeFileUpload();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize auto-logout
    initializeAutoLogout();
    
    // Initialize notifications
    initializeNotifications();
    
    console.log('Goba Hospital Management System initialized successfully');
}

/**
 * Login tab functionality
 */
function initializeLoginTabs() {
    const loginTabs = document.querySelectorAll('.login-tab');
    const loginForms = document.querySelectorAll('.login-form');
    
    loginTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetForm = this.dataset.target;
            
            // Remove active class from all tabs and forms
            loginTabs.forEach(t => t.classList.remove('active'));
            loginForms.forEach(f => f.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding form
            this.classList.add('active');
            document.getElementById(targetForm).classList.add('active');
            
            activeTab = targetForm.replace('-form', '');
        });
    });
}

/**
 * Initialize date pickers using HTML5 input types
 */
function initializeDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"], input[type="datetime-local"]');
    
    dateInputs.forEach(input => {
        // Set max date to today for birth dates
        if (input.name.includes('birth') || input.name.includes('dob')) {
            input.max = new Date().toISOString().split('T')[0];
        }
        
        // Set min date to today for future dates
        if (input.name.includes('follow_up') || input.name.includes('appointment')) {
            input.min = new Date().toISOString().split('T')[0];
        }
    });
}

/**
 * Form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

/**
 * Validate entire form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldName = field.name;
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (fieldType === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation
    if (fieldName.includes('phone') && value) {
        const phoneRegex = /^[\+]?[0-9\-\(\)\s]+$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    // Password validation
    if (fieldType === 'password' && value) {
        if (value.length < 8) {
            showFieldError(field, 'Password must be at least 8 characters long');
            return false;
        }
    }
    
    // Confirm password validation
    if (fieldName === 'confirm_password' && value) {
        const passwordField = field.form.querySelector('input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            showFieldError(field, 'Passwords do not match');
            return false;
        }
    }
    
    // SSN validation (basic format check)
    if (fieldName === 'ssn' && value) {
        if (value.length < 5) {
            showFieldError(field, 'Please enter a valid SSN/ID number');
            return false;
        }
    }
    
    return true;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    errorElement.style.color = 'var(--danger-color)';
    errorElement.style.fontSize = '0.875rem';
    errorElement.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorElement);
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('error');
    
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

/**
 * Search functionality
 */
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    const searchButtons = document.querySelectorAll('.search-btn');
    
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(this.value, this.dataset.searchType);
            }
        });
    });
    
    searchButtons.forEach(button => {
        button.addEventListener('click', function() {
            const searchInput = this.parentNode.querySelector('.search-input');
            if (searchInput) {
                performSearch(searchInput.value, searchInput.dataset.searchType);
            }
        });
    });
}

/**
 * Perform search
 */
function performSearch(query, searchType = 'patient') {
    if (!query.trim()) {
        showAlert('Please enter a search term', 'warning');
        return;
    }
    
    showLoadingSpinner();
    
    // Simulate API call with setTimeout
    setTimeout(() => {
        // This would normally be an AJAX call to the server
        console.log(`Searching for: ${query} in ${searchType}`);
        hideLoadingSpinner();
        
        // Mock search results
        const mockResults = generateMockSearchResults(query, searchType);
        displaySearchResults(mockResults);
    }, 1000);
}

/**
 * Generate mock search results
 */
function generateMockSearchResults(query, searchType) {
    const results = [];
    
    for (let i = 1; i <= 5; i++) {
        results.push({
            id: i,
            type: searchType,
            name: `Sample ${searchType} ${i}`,
            ssn: `SSN${1000 + i}`,
            date: new Date().toLocaleDateString(),
            status: Math.random() > 0.5 ? 'Active' : 'Inactive'
        });
    }
    
    return results;
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="text-center">No results found.</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table">';
    html += '<thead><tr><th>ID</th><th>Name</th><th>SSN</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>';
    html += '<tbody>';
    
    results.forEach(result => {
        html += `
            <tr>
                <td>${result.id}</td>
                <td>${result.name}</td>
                <td>${result.ssn}</td>
                <td>${result.date}</td>
                <td><span class="badge ${result.status === 'Active' ? 'badge-success' : 'badge-danger'}">${result.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewDetails(${result.id}, '${result.type}')">View</button>
                    <button class="btn btn-sm btn-secondary" onclick="editRecord(${result.id}, '${result.type}')">Edit</button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    resultsContainer.innerHTML = html;
}

/**
 * File upload functionality
 */
function initializeFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFileUpload(this);
        });
    });
}

/**
 * Handle file upload
 */
function handleFileUpload(input) {
    const files = input.files;
    
    if (files.length === 0) return;
    
    Array.from(files).forEach(file => {
        // Validate file type
        if (!validateFileType(file)) {
            showAlert(`Invalid file type: ${file.name}. Allowed types: PDF, JPG, PNG, DOC, DOCX`, 'danger');
            input.value = '';
            return;
        }
        
        // Validate file size
        if (!validateFileSize(file)) {
            showAlert(`File too large: ${file.name}. Maximum size: 5MB`, 'danger');
            input.value = '';
            return;
        }
        
        // Show upload progress
        showUploadProgress(file);
    });
}

/**
 * Validate file type
 */
function validateFileType(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 
                         'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    return allowedTypes.includes(file.type);
}

/**
 * Validate file size
 */
function validateFileSize(file) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    return file.size <= maxSize;
}

/**
 * Show upload progress
 */
function showUploadProgress(file) {
    const progressContainer = document.createElement('div');
    progressContainer.className = 'upload-progress';
    progressContainer.innerHTML = `
        <div class="upload-item">
            <span class="filename">${file.name}</span>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%"></div>
            </div>
            <span class="progress-text">0%</span>
        </div>
    `;
    
    document.body.appendChild(progressContainer);
    
    // Simulate upload progress
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            setTimeout(() => {
                progressContainer.remove();
                showAlert('File uploaded successfully', 'success');
            }, 500);
        }
        
        const progressFill = progressContainer.querySelector('.progress-fill');
        const progressText = progressContainer.querySelector('.progress-text');
        
        progressFill.style.width = progress + '%';
        progressText.textContent = Math.round(progress) + '%';
    }, 200);
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(e) {
    const text = e.target.dataset.tooltip;
    if (!text) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: var(--dark-color);
        color: var(--white);
        padding: 0.5rem;
        border-radius: var(--border-radius-sm);
        font-size: 0.875rem;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    
    e.target.tooltipElement = tooltip;
}

/**
 * Hide tooltip
 */
function hideTooltip(e) {
    if (e.target.tooltipElement) {
        e.target.tooltipElement.remove();
        delete e.target.tooltipElement;
    }
}

/**
 * Auto-logout functionality
 */
function initializeAutoLogout() {
    let lastActivity = Date.now();
    const timeout = 30 * 60 * 1000; // 30 minutes
    
    // Track user activity
    document.addEventListener('click', updateActivity);
    document.addEventListener('keypress', updateActivity);
    document.addEventListener('scroll', updateActivity);
    document.addEventListener('mousemove', updateActivity);
    
    function updateActivity() {
        lastActivity = Date.now();
    }
    
    // Check for inactivity
    setInterval(() => {
        if (Date.now() - lastActivity > timeout && currentUser) {
            showAlert('Session expired due to inactivity. Please log in again.', 'warning');
            logout();
        }
    }, 60000); // Check every minute
}

/**
 * Initialize notifications
 */
function initializeNotifications() {
    // Check for browser notification support
    if ('Notification' in window) {
        // Request permission if not already granted
        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
}

/**
 * Show notification
 */
function showNotification(title, message, type = 'info') {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            body: message,
            icon: '/assets/images/logo.png',
            tag: 'goba-hospital'
        });
    }
    
    // Also show in-app notification
    showAlert(message, type);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = getOrCreateAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <span>${message}</span>
        <button class="close-btn" onclick="this.parentElement.remove()" style="margin-left: auto; background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

/**
 * Get or create alert container
 */
function getOrCreateAlertContainer() {
    let container = document.getElementById('alert-container');
    
    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
    
    return container;
}

/**
 * Show loading spinner
 */
function showLoadingSpinner() {
    const spinner = document.createElement('div');
    spinner.id = 'loading-spinner';
    spinner.innerHTML = `
        <div class="spinner-overlay">
            <div class="spinner">
                <div class="spinner-circle"></div>
                <p>Loading...</p>
            </div>
        </div>
    `;
    spinner.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    `;
    
    const style = document.createElement('style');
    style.textContent = `
        .spinner-overlay {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .spinner {
            text-align: center;
            color: var(--white);
        }
        .spinner-circle {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(spinner);
}

/**
 * Hide loading spinner
 */
function hideLoadingSpinner() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}

/**
 * Login function
 */
function login(userType, userId, password) {
    showLoadingSpinner();
    
    // Simulate API call
    setTimeout(() => {
        hideLoadingSpinner();
        
        // Mock successful login
        currentUser = {
            id: userId,
            type: userType,
            name: 'John Doe'
        };
        
        showAlert('Login successful! Redirecting...', 'success');
        
        // Redirect to appropriate dashboard
        setTimeout(() => {
            window.location.href = `pages/${userType}/dashboard.php`;
        }, 1500);
    }, 2000);
}

/**
 * Logout function
 */
function logout() {
    currentUser = null;
    showAlert('Logged out successfully', 'info');
    window.location.href = 'index.php';
}

/**
 * View record details
 */
function viewDetails(id, type) {
    showAlert(`Viewing ${type} details for ID: ${id}`, 'info');
    // Implementation would open a modal or navigate to details page
}

/**
 * Edit record
 */
function editRecord(id, type) {
    showAlert(`Editing ${type} record for ID: ${id}`, 'info');
    // Implementation would open edit form
}

/**
 * Delete record
 */
function deleteRecord(id, type) {
    if (confirm(`Are you sure you want to delete this ${type} record?`)) {
        showAlert(`${type} record deleted successfully`, 'success');
        // Implementation would make API call to delete record
    }
}

/**
 * Format phone number
 */
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 10) {
        value = value.substring(0, 10);
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    }
    input.value = value;
}

/**
 * Generate reference number
 */
function generateReferenceNumber(prefix = 'REF') {
    const date = new Date();
    const timestamp = date.getTime();
    const random = Math.random().toString(36).substring(2, 8).toUpperCase();
    return `${prefix}-${date.getFullYear()}${(date.getMonth() + 1).toString().padStart(2, '0')}${date.getDate().toString().padStart(2, '0')}-${random}`;
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Copied to clipboard!', 'success');
    }).catch(() => {
        showAlert('Failed to copy to clipboard', 'danger');
    });
}

/**
 * Print page
 */
function printPage() {
    window.print();
}

/**
 * Export to CSV
 */
function exportToCSV(data, filename = 'export.csv') {
    const csv = convertArrayToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    
    window.URL.revokeObjectURL(url);
}

/**
 * Convert array to CSV
 */
function convertArrayToCSV(data) {
    if (!data.length) return '';
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => 
            JSON.stringify(row[header] || '')).join(',')
        )
    ].join('\n');
    
    return csvContent;
}

// Utility functions for form handling
const FormUtils = {
    /**
     * Serialize form data to object
     */
    serializeForm: function(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    },
    
    /**
     * Reset form and clear errors
     */
    resetForm: function(form) {
        form.reset();
        const errorMessages = form.querySelectorAll('.error-message');
        errorMessages.forEach(msg => msg.remove());
        
        const errorFields = form.querySelectorAll('.error');
        errorFields.forEach(field => field.classList.remove('error'));
    },
    
    /**
     * Populate form with data
     */
    populateForm: function(form, data) {
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = data[key];
            }
        });
    }
};

// Export functions for use in other files
window.GobaHospital = {
    showAlert,
    showNotification,
    showLoadingSpinner,
    hideLoadingSpinner,
    login,
    logout,
    performSearch,
    validateForm,
    formatPhoneNumber,
    generateReferenceNumber,
    copyToClipboard,
    printPage,
    exportToCSV,
    FormUtils
};