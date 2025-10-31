/**
 * Main JavaScript functionality for Inclusify
 * Handles form validation, AJAX requests, and UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeFormValidation();
    initializeTooltips();
    initializeModals();
    initializeSearchFunctionality();
    initializeFileUploads();
    initializeCharts();
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

/**
 * Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation, form[data-validate="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focus on first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    showNotification('Please correct the highlighted fields', 'warning');
                }
            }
            
            form.classList.add('was-validated');
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            
            input.addEventListener('input', function() {
                if (input.classList.contains('is-invalid')) {
                    validateField(input);
                }
            });
        });
    });
}

function validateField(field) {
    const isValid = field.checkValidity();
    field.classList.toggle('is-valid', isValid);
    field.classList.toggle('is-invalid', !isValid);
    
    // Custom validation messages
    if (!isValid) {
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = getValidationMessage(field);
        }
    }
}

function getValidationMessage(field) {
    if (field.validity.valueMissing) {
        return `${getFieldLabel(field)} is required.`;
    }
    if (field.validity.typeMismatch) {
        if (field.type === 'email') {
            return 'Please enter a valid email address.';
        }
        if (field.type === 'url') {
            return 'Please enter a valid URL.';
        }
    }
    if (field.validity.tooShort) {
        return `${getFieldLabel(field)} must be at least ${field.minLength} characters long.`;
    }
    if (field.validity.tooLong) {
        return `${getFieldLabel(field)} must be no more than ${field.maxLength} characters long.`;
    }
    if (field.validity.patternMismatch) {
        return field.getAttribute('data-error') || 'Please match the requested format.';
    }
    
    return field.validationMessage;
}

function getFieldLabel(field) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    return label ? label.textContent.replace('*', '').trim() : 'Field';
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], [title]:not([title=""])'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Bootstrap modals
 */
function initializeModals() {
    // Auto-focus first input in modals
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.target;
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            firstInput.focus();
        }
    });
    
    // Confirm dialogs
    document.addEventListener('click', function(event) {
        const confirmBtn = event.target.closest('[data-confirm]');
        if (confirmBtn) {
            event.preventDefault();
            const message = confirmBtn.getAttribute('data-confirm') || 'Are you sure?';
            
            if (confirm(message)) {
                // If it's a link, navigate to it
                if (confirmBtn.tagName === 'A') {
                    window.location.href = confirmBtn.href;
                }
                // If it's a form submit button, submit the form
                else if (confirmBtn.type === 'submit') {
                    confirmBtn.closest('form').submit();
                }
            }
        }
    });
}

/**
 * Search functionality with debounce
 */
function initializeSearchFunctionality() {
    const searchInputs = document.querySelectorAll('input[type="search"], .search-input');
    
    searchInputs.forEach(input => {
        let timeout;
        
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                performSearch(input);
            }, 300);
        });
    });
}

function performSearch(input) {
    const query = input.value.trim();
    const targetTable = input.getAttribute('data-target');
    
    if (targetTable) {
        const table = document.querySelector(targetTable);
        if (table) {
            filterTable(table, query);
        }
    }
}

function filterTable(table, query) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = query === '' || text.includes(query.toLowerCase());
        row.style.display = match ? '' : 'none';
    });
    
    // Show "no results" message if needed
    const visibleRows = table.querySelectorAll('tbody tr[style=""], tbody tr:not([style])');
    const noResultsRow = table.querySelector('.no-results-row');
    
    if (visibleRows.length === 0 && !noResultsRow) {
        const tbody = table.querySelector('tbody');
        const colCount = table.querySelectorAll('thead th').length;
        const noResults = document.createElement('tr');
        noResults.className = 'no-results-row';
        noResults.innerHTML = `<td colspan="${colCount}" class="text-center text-muted py-4">No results found</td>`;
        tbody.appendChild(noResults);
    } else if (visibleRows.length > 0 && noResultsRow) {
        noResultsRow.remove();
    }
}

/**
 * File Upload Handling
 */
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFileUpload(input);
        });
        
        // Drag and drop support
        const dropZone = input.closest('.file-drop-zone');
        if (dropZone) {
            setupDropZone(dropZone, input);
        }
    });
}

function handleFileUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    // Validate file size (5MB limit)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        showNotification('File size must be less than 5MB', 'error');
        input.value = '';
        return;
    }
    
    // Validate file type based on input accept attribute
    const acceptedTypes = input.getAttribute('accept');
    if (acceptedTypes) {
        const types = acceptedTypes.split(',').map(type => type.trim());
        const fileType = file.type;
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        const isValidType = types.some(type => {
            if (type.startsWith('.')) {
                return type === fileExtension;
            }
            return fileType.startsWith(type.replace('*', ''));
        });
        
        if (!isValidType) {
            showNotification('Please select a valid file type', 'error');
            input.value = '';
            return;
        }
    }
    
    // Show file preview if it's an image
    if (file.type.startsWith('image/')) {
        showImagePreview(input, file);
    }
    
    // Update file name display
    const fileNameDisplay = input.closest('.file-input-container')?.querySelector('.file-name');
    if (fileNameDisplay) {
        fileNameDisplay.textContent = file.name;
    }
}

function setupDropZone(dropZone, input) {
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            handleFileUpload(input);
        }
    });
}

function showImagePreview(input, file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        let preview = input.closest('.file-input-container')?.querySelector('.image-preview');
        
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'image-preview mt-2';
            input.closest('.file-input-container').appendChild(preview);
        }
        
        preview.innerHTML = `
            <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFilePreview(this)">
                <i class="fas fa-times"></i> Remove
            </button>
        `;
    };
    reader.readAsDataURL(file);
}

function removeFilePreview(button) {
    const preview = button.closest('.image-preview');
    const container = preview.closest('.file-input-container');
    const input = container.querySelector('input[type="file"]');
    
    input.value = '';
    preview.remove();
    
    const fileNameDisplay = container.querySelector('.file-name');
    if (fileNameDisplay) {
        fileNameDisplay.textContent = 'No file selected';
    }
}

/**
 * Chart initialization
 */
function initializeCharts() {
    // Application status chart
    const statusChartCanvas = document.getElementById('applicationStatusChart');
    if (statusChartCanvas) {
        const ctx = statusChartCanvas.getContext('2d');
        const data = JSON.parse(statusChartCanvas.getAttribute('data-chart'));
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: [
                        '#0d6efd',  // Primary
                        '#ffc107',  // Warning
                        '#198754',  // Success
                        '#dc3545'   // Danger
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Opportunities by type chart
    const typeChartCanvas = document.getElementById('opportunityTypeChart');
    if (typeChartCanvas) {
        const ctx = typeChartCanvas.getContext('2d');
        const data = JSON.parse(typeChartCanvas.getAttribute('data-chart'));
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Opportunities',
                    data: data.values,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

/**
 * Notification system (simplified - no UI notifications)
 */
function showNotification(message, type = 'info') {
    // Only log to console, no UI notifications
    console.log(`${type.toUpperCase()}: ${message}`);
}

/**
 * AJAX form submission
 */
function submitFormAjax(form, successCallback, errorCallback) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn?.textContent;
    
    // Show loading state
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
    }
    
    fetch(form.action, {
        method: form.method || 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) {
                successCallback(data);
            } else {
                showNotification(data.message || 'Operation completed successfully', 'success');
            }
        } else {
            if (errorCallback) {
                errorCallback(data);
            } else {
                showNotification(data.message || 'An error occurred', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (errorCallback) {
            errorCallback({ message: 'Network error occurred' });
        } else {
            showNotification('Network error occurred', 'error');
        }
    })
    .finally(() => {
        // Restore button state
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

/**
 * Utility functions
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let lastFunc;
    let lastRan;
    return function() {
        const context = this;
        const args = arguments;
        if (!lastRan) {
            func.apply(context, args);
            lastRan = Date.now();
        } else {
            clearTimeout(lastFunc);
            lastFunc = setTimeout(function() {
                if ((Date.now() - lastRan) >= limit) {
                    func.apply(context, args);
                    lastRan = Date.now();
                }
            }, limit - (Date.now() - lastRan));
        }
    };
}

// Global utility functions
window.showNotification = showNotification;
window.submitFormAjax = submitFormAjax;
window.formatDate = formatDate;
window.formatCurrency = formatCurrency;
