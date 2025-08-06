// Goba Hospital Management System - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initializeApp();
});

function initializeApp() {
    // Add fade-in animation to elements
    addFadeInAnimation();
    
    // Initialize form validations
    initializeFormValidation();
    
    // Initialize date pickers
    initializeDatePickers();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize file uploads
    initializeFileUploads();
    
    // Initialize payment processing
    initializePayments();
}

// Add fade-in animation to elements
function addFadeInAnimation() {
    const elements = document.querySelectorAll('.portal-card, .card, .form-container');
    elements.forEach((element, index) => {
        setTimeout(() => {
            element.classList.add('fade-in');
        }, index * 100);
    });
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

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

function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Remove existing error
    removeFieldError(field);
    
    // Check if required field is empty
    if (field.hasAttribute('required') && !value) {
        errorMessage = 'This field is required';
        isValid = false;
    }
    
    // Validate email
    if (fieldType === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            errorMessage = 'Please enter a valid email address';
            isValid = false;
        }
    }
    
    // Validate phone
    if (field.name === 'phone' && value) {
        const phoneRegex = /^[\+]?[0-9\-\(\)\s]+$/;
        if (!phoneRegex.test(value)) {
            errorMessage = 'Please enter a valid phone number';
            isValid = false;
        }
    }
    
    // Validate SSN (National ID, Passport, Birth Certificate)
    if (field.name === 'ssn' && value) {
        if (value.length < 5) {
            errorMessage = 'SSN must be at least 5 characters';
            isValid = false;
        }
    }
    
    // Validate password
    if (fieldType === 'password' && value) {
        if (value.length < 6) {
            errorMessage = 'Password must be at least 6 characters';
            isValid = false;
        }
    }
    
    // Show error if validation failed
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--danger-color)';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
}

function removeFieldError(field) {
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Date picker initialization
function initializeDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"], input[type="datetime-local"]');
    
    dateInputs.forEach(input => {
        // Set max date to today for birth dates
        if (input.name === 'date_of_birth') {
            input.max = new Date().toISOString().split('T')[0];
        }
        
        // Set min date to today for future appointments
        if (input.name === 'appointment_date' || input.name === 'operation_date') {
            input.min = new Date().toISOString().split('T')[0];
        }
    });
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const searchType = this.dataset.searchType;
            
            if (searchType === 'patient') {
                searchPatients(searchTerm);
            } else if (searchType === 'doctor') {
                searchDoctors(searchTerm);
            } else if (searchType === 'records') {
                searchRecords(searchTerm);
            }
        });
    });
}

function searchPatients(term) {
    const tableRows = document.querySelectorAll('.patients-table tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function searchDoctors(term) {
    const tableRows = document.querySelectorAll('.doctors-table tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function searchRecords(term) {
    const recordCards = document.querySelectorAll('.record-card');
    
    recordCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(term)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// File upload functionality
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFileUpload(this);
        });
    });
}

function handleFileUpload(input) {
    const files = input.files;
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        
        // Validate file type
        if (!allowedTypes.includes(file.type)) {
            showAlert('Please select only JPG, PNG, or PDF files', 'danger');
            input.value = '';
            return;
        }
        
        // Validate file size
        if (file.size > maxSize) {
            showAlert('File size must be less than 5MB', 'danger');
            input.value = '';
            return;
        }
    }
    
    // Show file preview
    showFilePreview(input, files);
}

function showFilePreview(input, files) {
    const previewContainer = input.parentNode.querySelector('.file-preview') || 
                           createFilePreviewContainer(input);
    
    previewContainer.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';
        previewItem.innerHTML = `
            <span class="file-name">${file.name}</span>
            <span class="file-size">(${formatFileSize(file.size)})</span>
        `;
        previewContainer.appendChild(previewItem);
    }
}

function createFilePreviewContainer(input) {
    const container = document.createElement('div');
    container.className = 'file-preview';
    input.parentNode.appendChild(container);
    return container;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Payment processing
function initializePayments() {
    const paymentForms = document.querySelectorAll('.payment-form');
    
    paymentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            processPayment(this);
        });
    });
}

function processPayment(form) {
    const formData = new FormData(form);
    const bankName = formData.get('bank_name');
    const amount = formData.get('amount');
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.innerHTML = '<span class="loading"></span> Processing...';
    submitBtn.disabled = true;
    
    // Simulate payment processing
    setTimeout(() => {
        // In a real application, this would make an API call to the payment gateway
        const success = Math.random() > 0.1; // 90% success rate for demo
        
        if (success) {
            showAlert(`Payment of ${amount} ETB via ${bankName} was successful!`, 'success');
            form.reset();
        } else {
            showAlert('Payment failed. Please try again.', 'danger');
        }
        
        // Restore button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 2000);
}

// Utility functions
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Add to page
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
    
    // Make it dismissible
    alertDiv.addEventListener('click', () => {
        alertDiv.remove();
    });
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatDateTime(datetime) {
    return new Date(datetime).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// AJAX utility function
function makeAjaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error('Request failed'));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        if (data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    });
}

// Reference number generator
function generateReferenceNumber(prefix = 'REF') {
    const timestamp = Date.now();
    const random = Math.floor(Math.random() * 10000);
    return `${prefix}-${timestamp}-${random}`;
}

// Auto-logout functionality
let logoutTimer;
const LOGOUT_TIME = 30 * 60 * 1000; // 30 minutes

function resetLogoutTimer() {
    clearTimeout(logoutTimer);
    logoutTimer = setTimeout(() => {
        if (confirm('Your session will expire soon. Do you want to continue?')) {
            resetLogoutTimer();
        } else {
            window.location.href = 'logout.php';
        }
    }, LOGOUT_TIME);
}

// Reset timer on user activity
document.addEventListener('click', resetLogoutTimer);
document.addEventListener('keypress', resetLogoutTimer);
document.addEventListener('mousemove', resetLogoutTimer);

// Initialize logout timer if user is logged in
if (document.body.classList.contains('logged-in')) {
    resetLogoutTimer();
}