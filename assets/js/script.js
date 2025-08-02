// Custom JavaScript for Goba Hospital Patient Record Management System

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize Flatpickr for date inputs
    const dateInputs = document.querySelectorAll('input[type="date"], input[data-date]');
    dateInputs.forEach(input => {
        flatpickr(input, {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    });

    // Initialize Flatpickr for datetime inputs
    const datetimeInputs = document.querySelectorAll('input[data-datetime]');
    datetimeInputs.forEach(input => {
        flatpickr(input, {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            allowInput: true
        });
    });

    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove selected class from all methods
            paymentMethods.forEach(m => m.classList.remove('selected'));
            // Add selected class to clicked method
            this.classList.add('selected');
            
            // Update hidden input
            const paymentInput = document.getElementById('payment_method');
            if (paymentInput) {
                paymentInput.value = this.dataset.method;
            }
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Audio recording functionality
    const recordButton = document.getElementById('recordButton');
    const audioPlayer = document.getElementById('audioPlayer');
    let mediaRecorder;
    let audioChunks = [];

    if (recordButton) {
        recordButton.addEventListener('click', function() {
            if (this.textContent === 'Start Recording') {
                startRecording();
                this.textContent = 'Stop Recording';
                this.classList.remove('btn-primary');
                this.classList.add('btn-danger');
            } else {
                stopRecording();
                this.textContent = 'Start Recording';
                this.classList.remove('btn-danger');
                this.classList.add('btn-primary');
            }
        });
    }

    function startRecording() {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];

                mediaRecorder.addEventListener('dataavailable', event => {
                    audioChunks.push(event.data);
                });

                mediaRecorder.addEventListener('stop', () => {
                    const audioBlob = new Blob(audioChunks);
                    const audioUrl = URL.createObjectURL(audioBlob);
                    
                    if (audioPlayer) {
                        audioPlayer.src = audioUrl;
                        audioPlayer.style.display = 'block';
                    }

                    // Create hidden input for form submission
                    const audioInput = document.getElementById('audio_file');
                    if (audioInput) {
                        const file = new File([audioBlob], 'consultation_audio.wav', { type: 'audio/wav' });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        audioInput.files = dataTransfer.files;
                    }
                });

                mediaRecorder.start();
            })
            .catch(error => {
                console.error('Error accessing microphone:', error);
                alert('Unable to access microphone. Please check permissions.');
            });
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
        }
    }

    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Dynamic form fields
    const addFieldButtons = document.querySelectorAll('.add-field');
    addFieldButtons.forEach(button => {
        button.addEventListener('click', function() {
            const container = this.previousElementSibling;
            const fieldTemplate = container.querySelector('.field-template');
            const newField = fieldTemplate.cloneNode(true);
            
            // Clear the template values
            newField.querySelectorAll('input, textarea, select').forEach(input => {
                input.value = '';
            });
            
            // Remove template class and show
            newField.classList.remove('field-template');
            newField.style.display = 'block';
            
            container.appendChild(newField);
        });
    });

    // Remove field functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-field')) {
            e.target.closest('.field-group').remove();
        }
    });

    // Print functionality
    const printButtons = document.querySelectorAll('.print-btn');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });

    // Export functionality
    const exportButtons = document.querySelectorAll('.export-btn');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const table = this.closest('.card').querySelector('table');
            if (table) {
                exportTableToCSV(table, 'hospital_data.csv');
            }
        });
    });

    function exportTableToCSV(table, filename) {
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            cols.forEach(col => {
                rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
            });
            csv.push(rowData.join(','));
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    }

    // Auto-save functionality for forms
    const autoSaveForms = document.querySelectorAll('form[data-autosave]');
    autoSaveForms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                saveFormData(form);
            });
        });
    });

    function saveFormData(form) {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        localStorage.setItem('form_autosave_' + form.id, JSON.stringify(data));
    }

    // Load auto-saved data
    autoSaveForms.forEach(form => {
        const savedData = localStorage.getItem('form_autosave_' + form.id);
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            });
        }
    });

    // Loading indicators
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.form && this.form.checkValidity()) {
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                this.disabled = true;
            }
        });
    });

    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Modal confirmation
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Real-time notifications (simulated)
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Example notification (remove in production)
    // setTimeout(() => {
    //     showNotification('New patient record added successfully!', 'success');
    // }, 3000);
});