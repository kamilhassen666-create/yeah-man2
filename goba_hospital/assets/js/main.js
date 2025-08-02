/**
 * Goba Hospital Patient Record Management System
 * Main JavaScript File
 */

$(document).ready(function() {
    // Initialize date pickers
    initDatePickers();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize tooltips and popovers
    initBootstrapComponents();
    
    // Initialize file upload handlers
    initFileUpload();
    
    // Initialize search functionality
    initSearch();
    
    // Initialize auto-save functionality
    initAutoSave();
});

/**
 * Initialize date pickers using Flatpickr
 */
function initDatePickers() {
    // Date picker for date inputs
    flatpickr('.date-picker', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        clickOpens: true
    });
    
    // DateTime picker for datetime inputs
    flatpickr('.datetime-picker', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        allowInput: true,
        clickOpens: true
    });
    
    // Time picker for time inputs
    flatpickr('.time-picker', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        allowInput: true,
        clickOpens: true
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Custom validation for specific fields
    $('.validate-email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Please enter a valid email address.');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('.validate-phone').on('blur', function() {
        const phone = $(this).val();
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        
        if (phone && !phoneRegex.test(phone)) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Please enter a valid phone number.');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
}

/**
 * Initialize Bootstrap components
 */
function initBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Initialize file upload functionality
 */
function initFileUpload() {
    $('.file-upload').on('change', function() {
        const input = this;
        const file = input.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'audio/mp3', 'audio/wav'];
        
        if (file) {
            // Check file size
            if (file.size > maxSize) {
                showAlert('File size must be less than 10MB', 'danger');
                input.value = '';
                return;
            }
            
            // Check file type
            if (!allowedTypes.includes(file.type)) {
                showAlert('Invalid file type. Please upload JPG, PNG, PDF, MP3, or WAV files only.', 'danger');
                input.value = '';
                return;
            }
            
            // Update label with file name
            const label = $(input).siblings('.file-upload-label');
            label.text(file.name);
            
            // Show file preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = $(input).siblings('.file-preview');
                    if (preview.length) {
                        preview.html(`<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">`);
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    });
    
    // Drag and drop file upload
    $('.file-drop-zone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $('.file-drop-zone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $('.file-drop-zone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        const input = $(this).find('input[type="file"]')[0];
        input.files = files;
        $(input).trigger('change');
    });
}

/**
 * Initialize search functionality
 */
function initSearch() {
    // Real-time search for tables
    $('.table-search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        const table = $($(this).data('table'));
        
        table.find('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Patient ID search with suggestions
    $('.patient-search').on('input', function() {
        const query = $(this).val();
        const input = $(this);
        
        if (query.length >= 2) {
            $.ajax({
                url: '../includes/ajax/search_patients.php',
                method: 'GET',
                data: { q: query },
                dataType: 'json',
                success: function(data) {
                    showSearchSuggestions(input, data);
                }
            });
        } else {
            hideSearchSuggestions(input);
        }
    });
    
    // Doctor search with suggestions
    $('.doctor-search').on('input', function() {
        const query = $(this).val();
        const input = $(this);
        
        if (query.length >= 2) {
            $.ajax({
                url: '../includes/ajax/search_doctors.php',
                method: 'GET',
                data: { q: query },
                dataType: 'json',
                success: function(data) {
                    showSearchSuggestions(input, data);
                }
            });
        } else {
            hideSearchSuggestions(input);
        }
    });
}

/**
 * Show search suggestions
 */
function showSearchSuggestions(input, suggestions) {
    hideSearchSuggestions(input);
    
    if (suggestions.length > 0) {
        const suggestionsList = $('<div class="search-suggestions"></div>');
        
        suggestions.forEach(function(item) {
            const suggestionItem = $(`<div class="search-suggestion-item" data-value="${item.id}">${item.name}</div>`);
            suggestionItem.on('click', function() {
                input.val($(this).data('value'));
                hideSearchSuggestions(input);
            });
            suggestionsList.append(suggestionItem);
        });
        
        input.after(suggestionsList);
    }
}

/**
 * Hide search suggestions
 */
function hideSearchSuggestions(input) {
    input.siblings('.search-suggestions').remove();
}

/**
 * Initialize auto-save functionality
 */
function initAutoSave() {
    let autoSaveInterval;
    
    $('.auto-save-form').on('input', function() {
        clearTimeout(autoSaveInterval);
        
        autoSaveInterval = setTimeout(function() {
            saveFormDraft();
        }, 5000); // Auto-save after 5 seconds of inactivity
    });
    
    function saveFormDraft() {
        const form = $('.auto-save-form');
        const formData = form.serialize();
        const formId = form.data('form-id');
        
        if (formId && formData) {
            localStorage.setItem(`form_draft_${formId}`, formData);
            showToast('Draft saved automatically', 'info');
        }
    }
    
    // Load saved draft on page load
    $('.auto-save-form').each(function() {
        const formId = $(this).data('form-id');
        const savedDraft = localStorage.getItem(`form_draft_${formId}`);
        
        if (savedDraft) {
            const urlParams = new URLSearchParams(savedDraft);
            const form = $(this);
            
            urlParams.forEach(function(value, key) {
                const field = form.find(`[name="${key}"]`);
                if (field.length) {
                    if (field.is(':checkbox') || field.is(':radio')) {
                        field.filter(`[value="${value}"]`).prop('checked', true);
                    } else {
                        field.val(value);
                    }
                }
            });
            
            showAlert('A saved draft has been restored.', 'info');
        }
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.alert-container').html(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastElement = $(toastHtml);
    $('.toast-container').append(toastElement);
    
    const toast = new bootstrap.Toast(toastElement[0]);
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

/**
 * Confirm deletion
 */
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

/**
 * Print page or element
 */
function printElement(elementId) {
    const element = document.getElementById(elementId);
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: Arial, sans-serif; }
                @media print {
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${element.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    const rows = Array.from(table.querySelectorAll('tr'));
    
    const csvContent = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
    }).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    
    window.URL.revokeObjectURL(url);
}

/**
 * Format currency
 */
function formatCurrency(amount, currency = 'ETB') {
    return new Intl.NumberFormat('en-ET', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(date, format = 'en-US') {
    return new Date(date).toLocaleDateString(format);
}

/**
 * Format time
 */
function formatTime(time, format = 'en-US') {
    return new Date(`2000-01-01 ${time}`).toLocaleTimeString(format, {
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Generate random ID
 */
function generateId(prefix = '', length = 8) {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let result = prefix;
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

/**
 * Debounce function
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

/**
 * Loading overlay
 */
function showLoading() {
    $('body').append('<div class="loading-overlay"><div class="loading-spinner"></div></div>');
}

function hideLoading() {
    $('.loading-overlay').remove();
}

// Global event handlers
$(document).on('click', '.btn-delete', function(e) {
    if (!confirmDelete()) {
        e.preventDefault();
    }
});

$(document).on('click', '.btn-print', function() {
    const target = $(this).data('target');
    if (target) {
        printElement(target);
    } else {
        window.print();
    }
});

$(document).on('click', '.btn-export-csv', function() {
    const table = $(this).data('table');
    const filename = $(this).data('filename') || 'export.csv';
    exportTableToCSV(table, filename);
});

// Audio recording functionality
let mediaRecorder;
let audioChunks = [];

function startAudioRecording() {
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            
            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };
            
            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                const audioUrl = URL.createObjectURL(audioBlob);
                
                // Create audio element for playback
                const audio = document.createElement('audio');
                audio.src = audioUrl;
                audio.controls = true;
                
                $('.audio-preview').html(audio);
                
                // Convert to base64 for form submission
                const reader = new FileReader();
                reader.onload = function() {
                    $('input[name="audio_recording"]').val(reader.result);
                };
                reader.readAsDataURL(audioBlob);
            };
            
            mediaRecorder.start();
            $('.btn-start-recording').hide();
            $('.btn-stop-recording').show();
        })
        .catch(error => {
            console.error('Error accessing microphone:', error);
            showAlert('Error accessing microphone. Please check permissions.', 'danger');
        });
}

function stopAudioRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        $('.btn-start-recording').show();
        $('.btn-stop-recording').hide();
    }
}

$(document).on('click', '.btn-start-recording', startAudioRecording);
$(document).on('click', '.btn-stop-recording', stopAudioRecording);