// Form Validation Functions

// Validate email
function validateEmail(email) {
    const re = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
    return re.test(email);
}

// Validate phone (Nepali format)
function validatePhone(phone) {
    const re = /^(?:\+9779[678]\d{7}|9[678]\d{8})$/;
    return re.test(phone);
}

// Validate password strength
function validatePassword(password) {
    if (password.length < 6) {
        return { valid: false, message: 'Password must be at least 6 characters' };
    }
    if (!/[A-Z]/.test(password)) {
        return { valid: false, message: 'Password must contain at least one uppercase letter' };
    }
    if (!/[a-z]/.test(password)) {
        return { valid: false, message: 'Password must contain at least one lowercase letter' };
    }
    if (!/[0-9]/.test(password)) {
        return { valid: false, message: 'Password must contain at least one number' };
    }
    return { valid: true, message: '' };
}

// Validate PIN code
function validatePincode(pincode) {
    const re = /^[1-9][0-9]{5}$/;
    return re.test(pincode);
}

// Real-time form validation
document.addEventListener('DOMContentLoaded', function() {
    // Registration form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password && confirmPassword) {
            function validatePasswordMatch() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('change', validatePasswordMatch);
            confirmPassword.addEventListener('keyup', validatePasswordMatch);
        }
    }
    
    // Add Business Form Validation
    const addBusinessForm = document.getElementById('addBusinessForm');
    if (addBusinessForm) {
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
            });
        }
    }
});

// File validation for uploads
function validateImage(file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: 'Only JPG, PNG, GIF, and WEBP images are allowed' };
    }
    if (file.size > maxSize) {
        return { valid: false, message: 'Image size must be less than 5MB' };
    }
    return { valid: true, message: '' };
}

// Display validation error
function showValidationError(input, message) {
    const formGroup = input.closest('.mb-3, .mb-2');
    let errorDiv = formGroup.querySelector('.invalid-feedback');
    
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        formGroup.appendChild(errorDiv);
    }
    
    input.classList.add('is-invalid');
    errorDiv.textContent = message;
}

function clearValidationError(input) {
    input.classList.remove('is-invalid');
    const formGroup = input.closest('.mb-3, .mb-2');
    const errorDiv = formGroup.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}